<?php

namespace App\Http\Controllers;

use App\Http\Resources\RunList\RunListCollection;
use App\Models\CSVHeader;
use App\Models\RunList;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Spatie\QueryBuilder\QueryBuilder;

class RunListController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.runlist.index');
    }

    public function get_run_lists(Request $request)
    {
        $query = $this->buildRunListQuery($request);

        // Check if per_page is set to -1 for fetching all data
        $perPage = $request->per_page ?? 10;

        if ($perPage == -1) {
            $perPage = 100000;
        }

        $run_lists = $query->paginate($perPage);

        return new RunListCollection($run_lists);
    }


    public function create_upload_run_list()
    {
        return view('pages.runlist.upload');
    }

    /**
     * Run List
     */

    public function import_run_list_csv(Request $request)
    {
        $path = $request->file('csv_file')->getRealPath();
        $data = array_map('str_getcsv', file($path));

        $headers = $this->cleanHeaders($data[0]);
        unset($data[0]); // Remove header

        $requiredColumns = $this->get_csv_headers('run_list');

        $positions = [];
        // Find positions of required columns in the first row
        foreach ($requiredColumns as $columnName) {
            $position = array_search($columnName, $headers);
            if ($position === false) {
                Session::flash('error', "CSV file header [$columnName] not found");
                return view('pages.runlist.upload')->with(['csv_header' => $requiredColumns, 'column' => $columnName]);
            }
            $positions[$columnName] = $position;
        }

        $user_id = Auth::id();
        RunList::where('user_id', $user_id)->delete();

        $inserted_vehicles = 0;


        foreach ($data as $row) {

            $item = $row[$positions[$requiredColumns['item_number']]];
            $lot = $row[$positions[$requiredColumns['lot_number']]];
            $claim = $row[$positions[$requiredColumns['claim_number']]];
            $description = $row[$positions[$requiredColumns['description']]];
            $number_of_runs = $row[$positions[$requiredColumns['number_of_runs']]];

            RunList::create([
                'user_id' => $user_id,
                'item_number' => $item,
                'lot_number' => $lot,
                'claim_number' => $claim,
                'description' => $description,
                'number_of_runs' => $number_of_runs,
            ]);

            $inserted_vehicles++;
        }

        $msg = sprintf("%d new vehicles inserted", $inserted_vehicles);
        Session::flash('success', $msg);

        // return view('pages.runlist.upload');
        return back();

    }


    public function get_csv_headers($filename)
    {
        return CSVHeader::select('database_field', 'csv_header')->where('filename', $filename)->pluck('csv_header', 'database_field')->toArray();
    }


    public function cleanHeaders($headers)
    {
        return array_map('trim', $headers);
    }

    public function export_run_list(Request $request)
    {
        $query = $this->buildRunListQuery($request);

        // Check if per_page is set to -1 for fetching all data
        $perPage = $request->per_page ?? 10;

        if ($perPage == -1) {
            $perPage = 100000;
        }


        $columnsToFetch = ['description', 'item_number', 'lot_number', 'claim_number', 'number_of_runs'];

        $run_lists = $query->select($columnsToFetch)->toBase()->get();

        // return view('pages.pdf.run_list', compact('run_lists'));
        $pdf = PDF::loadView('pages.pdf.run_list', ['run_lists' => $run_lists]);
        //  return $pdf->stream();
        return $pdf->download('run_lists.pdf');
    }

    private function buildRunListQuery(Request $request)
    {
        $query = QueryBuilder::for(RunList::class)
            ->allowedFilters(
                'item_number',
                'lot_number',
                'claim_number',
                'description',
                'number_of_runs',
            )
            ->where('user_id', auth()->id());

        // Determine the sort direction and field
        $sortField = $request->input('sort', 'item_number');
        $sortDirection = 'asc';

        if (\Str::startsWith($sortField, '-')) {
            $sortDirection = 'desc';
            $sortField = ltrim($sortField, '-');
        }

        // Check for sort parameter and apply raw sorting if necessary
        $query->orderByRaw('CAST(' . $sortField . ' AS UNSIGNED) ' . $sortDirection);

        return $query;
    }
}
