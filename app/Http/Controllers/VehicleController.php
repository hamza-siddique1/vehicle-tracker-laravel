<?php

namespace App\Http\Controllers;

use App\Models\CSVHeader;
use App\Models\Vehicle;
use App\Models\VehicleMetas;
use App\Models\VehicleNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Campo\UserAgent;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('vehicle_manager')->only('destroy'); // Vehicle manager and higher roles can delete
        $this->middleware('yard_manager')->only('update'); // Yard manager and higher roles can edit
    }

    public function index()
    {
        // Get distinct statuses excluding 'Sold'
        $statuses = $this->getDistinctStatuses();

        // Get distinct locations
        $locations = $this->getDistinctLocations();

        return view('pages.vehicle.index', compact('statuses', 'locations'));
    }

    public function getDistinctStatuses()
    {
        return VehicleMetas::select('meta_value')
            ->where('meta_key', 'status')
            ->where('meta_value', '!=', 'Sold')
            ->groupBy('meta_value')
            ->orderBy('meta_value')
            ->pluck('meta_value');
    }

    public function getDistinctLocations()
    {
        return Vehicle::select('location')
            ->distinct()
            ->orderBy('location', 'asc')
            ->pluck('location');
    }

    public function sold_vehicles()
    {
        $statuses = VehicleMetas::select('meta_value')
            ->where('meta_key', 'status')
            ->where('meta_value', 'Sold')
            ->groupBy('meta_value')
            ->orderBy('meta_value')
            ->get()
            ->pluck('meta_value');

        return view('pages.vehicle.sold', compact('statuses'));
    }

    public function create_upload_buy()
    {
        return view('pages.vehicle.buy.upload'); //1
    }

    public function create_upload_inventory()
    {
        return view('pages.vehicle.inventory.upload'); //2
    }

    public function create_upload_sold()
    {
        return view('pages.vehicle.sold.upload'); //3
    }


    public function create()
    {
        return view('pages.vehicle.add');
    }

    public function store(Request $request)
    {
        $vehicle = $this->insert_in_db($request);

        return redirect()->route('vehicles.edit', $vehicle->id);
    }

    /*
     * Step 1:
     */

    public function import_buy_copart_csv(Request $request)
    {
        $path = $request->file('csv_file')->getRealPath();
        $csvFile = array_map('str_getcsv', file($path));

        $headers = $csvFile[0];
        $headers[0] = preg_replace('/^\x{FEFF}/u', '', $headers[0]);
        unset($csvFile[0]);

        $requiredColumns = [
            'VIN',
            'Lot/Inv #',
            'Location',
            'Description',
            'Left Location',
            'Date Paid',
            'Invoice Amount',
        ];

        $requiredColumns = $this->get_csv_headers('copart_buy');
        $positions = [];

        // Find positions of required columns in the first row
        foreach ($requiredColumns as $columnName) {
            $position = array_search($columnName, $headers);
            if ($position === false) {
                Session::flash('error', "CSV file header [$columnName] not found");

                return view('pages.vehicle.buy.upload')->with(['csv_header' => $requiredColumns, 'column' => $columnName]);
            }
            $positions[$columnName] = $position;
        }

        $unprocessed_rows = [];
        $vehicles_vins = Vehicle::pluck('vin')->toArray();
        $total_vehicles = 0;
        foreach ($csvFile as $row) {

            //Skip empty lines or lines with fewer columns than required
            if (count($row) < count($requiredColumns)) {
                continue;
            }


            $vin = $row[$positions[$requiredColumns['vin']]];
            $vin = preg_replace('/\s+/', '', trim($vin));

            if (empty($vin)) {
                $unprocessed_rows[] = $row;
                continue;
            }

            if (!in_array($vin, $vehicles_vins)) {
                $vehicle = new Vehicle();
                $vehicle->vin = $vin;
                $vehicle->purchase_lot = $row[$positions[$requiredColumns['purchase_lot']]];
                $vehicle->location = $row[$positions[$requiredColumns['location']]];
                $vehicle->source = 'copart';
                $vehicle->description = $row[$positions[$requiredColumns['description']]]; //year_make_model
                $vehicle->left_location = Carbon::parse($row[$positions[$requiredColumns['left_location']]])->format('Y-m-d');
                $vehicle->date_paid = Carbon::parse($row[$positions[$requiredColumns['date_paid']]])->format('Y-m-d');
                $vehicle->invoice_amount = $this->format_amount($row[$positions[$requiredColumns['invoice_amount']]]);
                $vehicle->save();
                $total_vehicles++;
            }
        }

        $msg = sprintf("%d vehicles inserted successfully", $total_vehicles);
        Session::flash('success', $msg);
        $file_name = sprintf("unprocessed_vehicles-%s.xlsx", time());

        return redirect()->route('upload.create.buy');

    }

    public function import_buy_iaai_csv(Request $request)
    {
        $path = $request->file('csv_file')->getRealPath();
        $csvFile = array_map('str_getcsv', file($path));

        $headers = $csvFile[0];
        $headers[0] = preg_replace('/^\x{FEFF}/u', '', $headers[0]);

        unset($csvFile[0]);

        $requiredColumns = $this->get_csv_headers('iaai_buy');

        $positions = [];

        // Find positions of required columns in the first row
        foreach ($requiredColumns as $columnName) {
            $position = array_search($columnName, $headers);
            if ($position === false) {
                Session::flash('error', "CSV file header [$columnName] not found");
                return view('pages.vehicle.buy.upload')->with(['csv_header' => $requiredColumns, 'column' => $columnName]);
            }
            $positions[$columnName] = $position;
        }

        $vehicles_vins = Vehicle::pluck('vin')->toArray();
        $total_vehicles = 0;
        foreach ($csvFile as $row) {

            //Skip empty lines or lines with fewer columns than required
            if (count($row) < count($requiredColumns)) {
                continue;
            }

            $vin = $row[$positions[$requiredColumns['vin']]];
            $vin = preg_replace('/\s+/', '', trim($vin));
            if (empty($vin)) {
                continue;
            }

            if (!in_array($vin, $vehicles_vins)) {
                $vehicle = new Vehicle();
                $vehicle->vin = $vin;
                $vehicle->purchase_lot = $row[$positions[$requiredColumns['purchase_lot']]];
                $vehicle->source = 'iaai';
                $vehicle->location = $row[$positions[$requiredColumns['location']]];

                if ($row[$positions[$requiredColumns['year']]] == $row[$positions[$requiredColumns['make']]] && $row[$positions[$requiredColumns['make']]] == $row[$positions[$requiredColumns['model']]]) {
                    $vehicle->description = sprintf("%s", $row[$positions[$requiredColumns['year']]]);
                    //dd($row[$positions[$requiredColumns['year']]], $row[$positions[$requiredColumns['make']]], $row[$positions[$requiredColumns['model']]]);
                } else {
                    $vehicle->description = sprintf("%s %s %s", $row[$positions[$requiredColumns['year']]], $row[$positions[$requiredColumns['make']]], $row[$positions[$requiredColumns['model']]]);
                }

                $vehicle->left_location = isset($row[$positions[$requiredColumns['left_location']]]) ? Carbon::parse($row[$positions[$requiredColumns['left_location']]])->format('Y-m-d') : null;
                $vehicle->date_paid = Carbon::parse($row[$positions[$requiredColumns['date_paid']]])->format('Y-m-d');
                $vehicle->invoice_amount = $this->format_amount($row[$positions[$requiredColumns['invoice_amount']]]);
                $vehicle->save();
                $total_vehicles++;
            }
        }

        $msg = sprintf("%d vehicles inserted successfully", $total_vehicles);
        Session::flash('success', $msg);

        return redirect()->route('upload.create.buy');
    }

    /*
     * Step 2:
     */

    public function import_inventory_copart_csv(Request $request) //step 2
    {
        $path = $request->file('csv_file')->getRealPath();
        $csvFile = array_map('str_getcsv', file($path));

        $headers = $csvFile[0];
        $headers[0] = preg_replace('/^\x{FEFF}/u', '', $headers[0]);
        unset($csvFile[0]);

        $requiredColumns = [
            'Lot #',
            'Claim #',
            'Status',
            'Description',
            'VIN',
            'Primary Damage',
            'Secondary Damage',
            'Keys',
            'Drivability Rating',
            'Odometer',
            'Odometer Brand',
            'Sale Title State',
            'Sale Title Type',
            'Location',
            'Days in Yard',
        ];

        $requiredColumns = $this->get_csv_headers('copart_inventory');

        $positions = [];

        // Find positions of required columns in the first row
        foreach ($requiredColumns as $columnName) {
            $position = array_search($columnName, $headers);
            if ($position === false) {
                Session::flash('error', "CSV file header [$columnName] not found");
                return view('pages.vehicle.inventory.upload')->with(['csv_header' => $requiredColumns, 'column' => $columnName]);
            }
            $positions[$columnName] = $position;
        }
        $vehicles_vins = Vehicle::pluck('vin')->toArray();

        //        $today = now();
        //        $past_auction_date = [];

        $count = 0;
        $new_vehicles = 0;
        $updated_vehicles = 0;
        $start_row = $request->start;
        $end_row = $request->end;
        foreach ($csvFile as $index => $row) {

            //Skip empty lines or lines with fewer columns than required
            if (count($row) < count($requiredColumns)) {
                continue;
            }


            if ($start_row == 0 && $end_row == 0) {

            } else {
                if ($index < $start_row) {
                    continue; // Skip rows before the start row
                }
                if ($index >= $end_row + 1) {
                    break; // Stop processing after the end row
                }
            }

            $vin = $row[$positions[$requiredColumns['vin']]];
            $vin = preg_replace('/\s+/', '', trim($vin));
            if (empty($vin)) {
                continue;
            }

            /*
             * Check if auction date is past date
             */
            //            $auction_date = Carbon::parse($row[23]);
            //            if ($auction_date < $today) {
            //                $past_auction_date[] = ['vin' => $vin, 'auction_date' => $auction_date->format('Y-m-d')];
            //
            //                $count++;
            //                if ($count > 10) {
            //                    return view('pages.vehicle.inventory.upload')->with('past_auction_date', $past_auction_date);
            //                }
            //                continue;
            //            }
            //            return view('pages.vehicle.inventory.upload')->with('past_auction_date', $past_auction_date);
            if (!in_array($vin, $vehicles_vins)) {
                //insert new vehicle
                $vehicle = new Vehicle();
                $vehicle->vin = $vin;
                $vehicle->auction_lot = $row[$positions[$requiredColumns['auction_lot']]];
                $vehicle->location = $row[$positions[$requiredColumns['location']]];
                $vehicle->description = $row[$positions[$requiredColumns['description']]];
                $vehicle->source = 'copart';
                $vehicle->save();
                $new_vehicles++;
                $this->insert_vehicle_metas($row, $vehicle->id, $positions, $requiredColumns);
            } else {
                //update vehicle
                $vehicle = Vehicle::where('vin', $vin)->first();
                // dd($vehicle);

                if (!$vehicle) {
                    continue;
                }
                //delete old vehicle metas
                VehicleMetas::where('vehicle_id', $vehicle->id)->forceDelete();
                $vehicle->auction_lot = $row[$positions[$requiredColumns['auction_lot']]];
                $vehicle->location = $row[$positions[$requiredColumns['location']]];
                $vehicle->save();
                $updated_vehicles++;
                $this->insert_vehicle_metas($row, $vehicle->id, $positions, $requiredColumns);
            }
        }
        $msg = sprintf("%d new vehicles inserted, %d updated", $new_vehicles, $updated_vehicles);
        Session::flash('success', $msg);

        return redirect()->route('upload.create.inventory');
    }

    public function insert_vehicle_metas($row, $vehicle_id, $positions, $requiredColumns)
    {
        $necessary_meta_fields = [
            'claim_number' => $row[$positions[$requiredColumns['claim_number']]],
            'status' => $row[$positions[$requiredColumns['status']]],
            'primary_damage' => $row[$positions[$requiredColumns['primary_damage']]],
            'keys' => $row[$positions[$requiredColumns['keys']]],
            'drivability_rating' => $row[$positions[$requiredColumns['drivability_rating']]],
            'odometer' => $row[$positions[$requiredColumns['odometer']]],
            'odometer_brand' => $row[$positions[$requiredColumns['odometer_brand']]],
            'days_in_yard' => $row[$positions[$requiredColumns['days_in_yard']]],
        ];
        if (!empty($row[$positions[$requiredColumns['secondary_damage']]])) {
            $necessary_meta_fields['secondary_damage'] = $row[$positions[$requiredColumns['secondary_damage']]];
        }
        if (!empty($row[$positions[$requiredColumns['sale_title_type']]])) {
            $necessary_meta_fields['sale_title_type'] = $row[$positions[$requiredColumns['sale_title_type']]];
        }
        if (!empty($row[$positions[$requiredColumns['sale_title_state']]])) {
            $necessary_meta_fields['sale_title_state'] = $row[$positions[$requiredColumns['sale_title_state']]];
        }

        $metas = [];
        $now = now();
        foreach ($necessary_meta_fields as $key => $value) {
            $metas[] = [
                'vehicle_id' => $vehicle_id,
                'meta_key' => $key,
                'meta_value' => trim($value),
                'created_at' => $now,
                'updated_at' => $now,
            ];


        }
        DB::table('vehicle_metas')->insert($metas);

        // Create days_in_yard inside main vehicle table (for solving sorting issue)
        if (isset($necessary_meta_fields['days_in_yard']) && !empty($necessary_meta_fields['days_in_yard'])) {
            $vehicle = Vehicle::find($vehicle_id);
            $vehicle->days_in_yard = $necessary_meta_fields['days_in_yard'];
            $vehicle->save();
        }
    }

    /*
     * Step 3:
     */

    public function import_sold_copart_csv(Request $request)
    {
        $path = $request->file('csv_file')->getRealPath();
        $data = array_map('str_getcsv', file($path));

        $headers = $this->cleanHeaders($data[0]);
        $headers[0] = preg_replace('/^\x{FEFF}/u', '', $headers[0]);
        unset($data[0]); // Remove header

        $requiredColumns = [
            'Lot #',
            'Claim #',
            'Status',
            'Location',
            'Sale Date',
            'Description',
            'Title State',
            'Title Type',
            'Odometer',
            'Odometer Brand',
            'Primary Damage',
            'Loss Type',
            'Keys',
            'Drivability Rating',
            'ACV',
            'Repair Cost',
            'Sale Price',
            'Return %',
        ];

        $requiredColumns = $this->get_csv_headers('copart_sale');

        //         foreach ($headers as $value) {
        //             $value = trim($value);
        //             if (! in_array($value, $requiredColumns)) {
        //                 Session::flash('error', "CSV file header [$value] not found");
        //                 return view('pages.vehicle.sold.upload')->with(['csv_header' => $requiredColumns, 'column' => $value]);
        //             }
        //         }

        $positions = [];
        // Find positions of required columns in the first row
        foreach ($requiredColumns as $columnName) {
            $position = array_search($columnName, $headers);
            if ($position === false) {
                Session::flash('error', "CSV file header [$columnName] not found");
                return view('pages.vehicle.sold.upload')->with(['csv_header' => $requiredColumns, 'column' => $columnName]);
            }
            $positions[$columnName] = $position;
        }

        $auction_lot = Vehicle::whereNotNull('auction_lot')->pluck('auction_lot')->toArray();
        $purchase_lot = Vehicle::whereNotNull('purchase_lot')->pluck('purchase_lot')->toArray();
        $vehicles_vins = Vehicle::pluck('vin')->toArray();

        $vehicles_not_found = [];

        $updated_vehicles = 0;
        $start_row = $request->start;
        $end_row = $request->end;
        foreach ($data as $index => $row) {
            //Skip empty lines or lines with fewer columns than required
            if (count($row) < count($requiredColumns)) {
                continue;
            }

            if ($start_row == 0 && $end_row == 0) {

            } else {
                if ($index < $start_row) {
                    continue; // Skip rows before the start row
                }
                if ($index >= $end_row + 1) {
                    break; // Stop processing after the end row
                }
            }
            $lot = $row[$positions[$requiredColumns['lot']]];
            $lot = preg_replace('/\s+/', '', trim($lot));

            $vin = trim($row[18]);
            $vin = preg_replace('/\s+/', '', $vin);
        
            $vehicle = null;

            if ($vin !== "" && in_array($vin, $vehicles_vins)) {
                $vehicle = Vehicle::where('vin', $vin)->first();
            }

            if (!$vehicle && ($lot !== "")) {
                if (in_array($lot, $auction_lot) || in_array($lot, $purchase_lot)) {
                    $vehicle = Vehicle::where('auction_lot', $lot)
                        ->orWhere('purchase_lot', $lot)
                        ->first();
                }
            }

            if (!$vehicle) {
                $vehicles_not_found[] = ['lot' => $lot, 'vin' => $vin];
                continue;
            }

            $updated_vehicles++;
                
            VehicleMetas::updateOrCreate(
                ['vehicle_id' => $vehicle->id, 'meta_key' => 'sale_date'],
                [
                    'meta_value' => Carbon::parse($row[$positions[$requiredColumns['sale_date']]])->format('Y-m-d'), //sale_date
                ]
            );

            VehicleMetas::updateOrCreate(
                ['vehicle_id' => $vehicle->id, 'meta_key' => 'sale_price'],
                [
                    'meta_value' => $row[$positions[$requiredColumns['sale_price']]] == "" ? 0 : $row[$positions[$requiredColumns['sale_price']]], //sale_price
                ]
            );

            VehicleMetas::updateOrCreate(
                ['vehicle_id' => $vehicle->id, 'meta_key' => 'status'],
                [
                    'meta_value' => 'SOLD', //sale_price
                ]
            );

            $vehicle->auction_lot = $lot;
            $vehicle->save();
        }

        $msg = sprintf("0 new vehicles inserted, %d updated", $updated_vehicles);
        Session::flash('success', $msg);

        return view('pages.vehicle.sold.upload')->with('vehicles_not_found', $vehicles_not_found);

    }


    public function edit(Vehicle $vehicle)
    {
        $vehicle_metas = VehicleMetas::where('vehicle_id', $vehicle->id)->get()->mapWithKeys(function ($item) {
            return [$item['meta_key'] => $item['meta_value']];
        });

        $meta_keys = [
            'claim_number',
            'status',
            'primary_damage',
            'keys',
            'drivability_rating',
            'odometer',
            'odometer_brand',
            'days_in_yard',
            'secondary_damage',
            'sale_title_state',
            'sale_title_type',
        ];

        $locations = Vehicle::select('location')->distinct()->orderBy('location', 'asc')->get()->pluck('location');

        return view('pages.vehicle.detail', get_defined_vars());
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'vin' => 'required',
            'location' => 'required',
            'description' => 'required',
        ]);


        // Check if the VIN already exists in the database
        $existingVehicle = Vehicle::where('vin', $request->vin)->first();

        // If the VIN exists and it doesn't belong to the current vehicle, show an error
        if ($existingVehicle && $existingVehicle->id !== $vehicle->id) {
            return response()->json(['message' => 'Vehicle with this VIN already exists', 'status' => 'error']);
        }

        $meta_keys = [
            'claim_number',
            'status',
            'primary_damage',
            'keys',
            'drivability_rating',
            'odometer',
            'odometer_brand',
            'days_in_yard',
            'secondary_damage',
            'sale_title_state',
            'sale_title_type',
            'sale_price',
        ];

        $amount = $request->invoice_amount;
        try {
            $vehicle->vin = $request->vin;
            $vehicle->purchase_lot = $request->purchase_lot;
            $vehicle->auction_lot = $request->auction_lot;
            $vehicle->source = $request->source;
            $vehicle->location = $request->location;
            $vehicle->description = $request->description;
            $vehicle->left_location = $request->left_location;
            $vehicle->date_paid = $request->date_paid;
            $vehicle->days_in_yard = $request->days_in_yard;

            $amount = str_replace('$', '', $amount);
            $vehicle->invoice_amount = (int) str_replace(' ', '', $amount);
            $vehicle->save();

            foreach ($meta_keys as $key) {

                if (empty($request->$key)) {
                    continue;
                }

                if ($request->$key == '-100') {
                    $request->$key = '';
                }

                VehicleMetas::updateOrCreate(
                    ['vehicle_id' => $vehicle->id, 'meta_key' => $key],
                    [
                        'meta_value' => $request->$key,
                    ]
                );
            }

            foreach ($request->notes as $note) {

                if ($note == null) {
                    continue;
                }

                VehicleNote::updateOrCreate(
                    ['vehicle_id' => $vehicle->id, 'user_id' => Auth::id()],
                    [
                        'note' => $note,
                    ]
                );

            }

            return response()->json(['message' => 'Vehicle updated successfully', 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error']);
        }

    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->metas()->delete();
        $vehicle->forceDelete();
        Session::flash('success', __('Successfully Deleted'));

        return redirect()->back();
    }

    public function delete_multiple_vehicles(Request $request)
    {
        $ids = $request->input('ids');
        $vehicles = Vehicle::whereIn('id', $ids)->get();

        foreach ($vehicles as $vehicle) {
            $vehicle->forceDelete();
        }

        return response()->json(['message' => count($vehicles) . ' vehicles have been deleted.']);
    }

    public function get_makes()
    {
        return Vehicle::select('make')
            ->where('make', '!=', '')
            ->groupBy('make')->get();
    }

    public function get_models()
    {
        return Vehicle::select('model')
            ->where('model', '!=', '')
            ->groupBy('model')->get();
    }

    public function get_locations()
    {
        return VehicleMetas::select('meta_value')
            ->where('meta_key', 'location')
            ->where('meta_value', '!=', '')
            ->groupBy('meta_value')->get();
    }

    public function format_amount($amount)
    {
        $invoice_amount = str_replace('$', '', $amount);
        $invoice_amount = str_replace('USD', '', $invoice_amount);
        $invoice_amount = str_replace(',', '', $invoice_amount);

        return $invoice_amount;
    }

    public function insert_in_db($request, $vehicle = null)
    {
        if (!$vehicle) {
            $vehicle = new Vehicle();
        }


        $vin = preg_replace('/\s+/', '', trim($request->vin));

        $vehicle->date_paid = $request->invoice_date;
        $vehicle->invoice_amount = $request->invoice_amount;
        $vehicle->purchase_lot = $request->purchase_lot;
        $vehicle->auction_lot = $request->auction_lot;
        $vehicle->vin = $vin;
        $vehicle->description = sprintf('%s %s %s', $request->year, $request->make, $request->model);
        $vehicle->location = $request->location;
        $vehicle->left_location = $request->left_location;
        $vehicle->save();

        $vehicle->metas()->updateOrCreate(['meta_key' => 'location'], ['meta_value' => $request->location]);
        $vehicle->metas()->updateOrCreate(['meta_key' => 'invoice_amount'], ['meta_value' => $request->invoice_amount]);

        return $vehicle;
        //        $vehicle->metas()->updateOrCreate(['meta_key' => 'pickup_date'], ['meta_value' => $request->pickup_date]);
    }

    public function delete_unsaved_vehicles()
    {
        Vehicle::where('vin', '')->forceDelete();
    }

    public function get_csv_headers($filename)
    {
        return CSVHeader::select('database_field', 'csv_header')->where('filename', $filename)->pluck('csv_header', 'database_field')->toArray();
    }

    public function cleanHeaders($headers)
    {
        return array_map('trim', $headers);
    }

    public function import_buy_copart_csv_old(Request $request)
    {
        $path = $request->file('csv_file')->getRealPath();
        $csvFile = array_map('str_getcsv', file($path));

        $headers = $csvFile[0];
        unset($csvFile[0]);

        //        $requiredColumns = $this->get_csv_headers('copart_buy');
        $requiredColumns = [
            'VIN',
            'Lot/Inv #',
            'Location',
            'Description',
            'Left Location',
            'Date Paid',
            'Invoice Amount',
        ];
        $positions = [];

        // Find positions of required columns in the first row
        foreach ($requiredColumns as $columnName) {
            $position = array_search($columnName, $headers);
            if ($position === false) {
                Session::flash('error', "CSV file header [$columnName] not found");

                return view('pages.vehicle.buy.upload')->with(['csv_header' => $requiredColumns, 'column' => $columnName]);
            }
            $positions[$columnName] = $position;
        }
        $vehicles_vins = Vehicle::pluck('vin')->toArray();

        foreach ($csvFile as $row) {
            $vin = $row[$positions['VIN']];

            if (empty($vin)) {
                continue;
            }

            if (!in_array($vin, $vehicles_vins)) {
                $vehicle = new Vehicle();
                $vehicle->vin = $vin;
                $vehicle->purchase_lot = $row[$positions['Lot/Inv #']];
                $vehicle->location = $row[$positions['Location']];
                $vehicle->source = 'copart';
                $vehicle->description = $row[$positions['Description']]; //year_make_model
                $vehicle->left_location = Carbon::parse($row[$positions['Left Location']])->format('Y-m-d');
                $vehicle->date_paid = Carbon::parse($row[$positions['Date Paid']])->format('Y-m-d');
                $vehicle->invoice_amount = $this->format_amount($row[$positions['Invoice Amount']]);
                $vehicle->save();
            }
        }

        Session::flash('success', 'Successfully inserted');

        return redirect()->route('upload.create.buy');

    }

    public function import_buy_iaai_csv_old(Request $request)
    {
        $path = $request->file('csv_file')->getRealPath();
        $csvFile = array_map('str_getcsv', file($path));

        $headers = $csvFile[0];
        unset($csvFile[0]);

        $requiredColumns = [
            'VIN',
            'Stock',
            'Stock#',
            'Branch',
            'Description',
            'Year',
            'Make',
            'Model',
            'Date Picked Up',
            'Date Paid',
            'Total Paid',
            'Total Amount',
            'Item#',
        ];
        $requiredColumns = $this->get_csv_headers('iaai_buy');

        //        $requiredColumns2 = $requiredColumns;
        if (!in_array('Item#', $headers)) {
            unset($requiredColumns[12]);
        }

        if (in_array('Description', $headers)) {
            unset($requiredColumns[5]);
            unset($requiredColumns[6]);
            unset($requiredColumns[7]);
        } else {
            unset($requiredColumns[4]);
        }

        if (in_array('Stock', $headers)) {
            unset($requiredColumns[2]);
        } else {
            unset($requiredColumns[1]);
        }

        if (in_array('Total Paid', $headers)) {
            unset($requiredColumns[11]);
        } else {
            unset($requiredColumns[10]);
        }
        $positions = [];

        // Find positions of required columns in the first row
        foreach ($requiredColumns as $columnName) {
            $position = array_search($columnName, $headers);
            if ($position === false) {
                Session::flash('error', "CSV file header [$columnName] not found");
                return view('pages.vehicle.buy.upload')->with(['csv_header' => $requiredColumns, 'column' => $columnName]);
            }
            $positions[$columnName] = $position;
        }

        $vehicles_vins = Vehicle::pluck('vin')->toArray();

        foreach ($csvFile as $row) {
            //            if (! isset($positions['Item#']) || empty($row[$positions['Item#']])) { //if item# is not present in csv file
            //                continue;
            //            }

            $vin = $row[$positions['VIN']];
            if (empty($vin)) {
                continue;
            }

            if (!in_array($vin, $vehicles_vins)) {
                $vehicle = new Vehicle();
                $vehicle->vin = $vin;
                $vehicle->purchase_lot = isset($positions['Stock']) ? $row[$positions['Stock']] : $row[$positions['Stock#']];
                $vehicle->source = 'iaai';
                $vehicle->location = $row[$positions['Branch']];
                $vehicle->description = isset($positions['Description']) ? $row[$positions['Description']] : sprintf('%s %s %s', $row[$positions['Year']], $row[$positions['Make']], $row[$positions['Model']]); //year_make_model
                $vehicle->left_location = Carbon::parse($row[$positions['Date Picked Up']])->format('Y-m-d');
                $vehicle->date_paid = Carbon::parse($row[$positions['Date Paid']])->format('Y-m-d');
                $vehicle->invoice_amount = isset($positions['Total Amount']) ? $this->format_amount($row[$positions['Total Amount']]) : $this->format_amount($row[$positions['Total Paid']]);
                $vehicle->save();
            }
        }

        Session::flash('success', 'Successfully inserted');

        return redirect()->route('upload.create.buy');
    }

    public function duplicate_vehicles()
    {
        $duplicateVins = \DB::select("SELECT vin, COUNT(*) AS count FROM vehicles GROUP BY vin HAVING COUNT > 1");

        foreach ($duplicateVins as $duplicateVin) {
            echo $duplicateVin->vin . "</br>";
        }

        if (count($duplicateVins) == 0) {
            echo "No duplicate vehicles found";
        }

    }

    public function check_status($lotNumber)
    {
        $url = 'https://www.copart.com/public/data/lotdetails/solr/' . $lotNumber;

        try {
            $response = Http::withHeaders([
                'User-Agent' => UserAgent::random(),
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate, br, zstd',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Cache-Control' => 'no-cache',
            ])->get($url);

            if ($response->successful()) {
                $jsonData = $response->json();

                if (isset($jsonData['returnCode']) && $jsonData['returnCode'] === 1) {
                    Log::info("Successfully fetched lot details for lot number: " . $lotNumber);
                    return response()->json($jsonData); // Return the JSON response
                } else {
                    Log::warning("Invalid returnCode received for lot number: " . $lotNumber . ". returnCode: " . ($jsonData['returnCode'] ?? 'null'));
                    Log::debug("Response body: " . $response->body());
                    return response()->json(['error' => 'Invalid data from Copart API.'], 500);  //Return Error to UI
                }
            } else {
                Log::error("HTTP error ({$response->status()}) while fetching lot details for lot number: " . $lotNumber);
                Log::debug("Response body: " . $response->body());
                return response()->json(['error' => 'Could not retrieve lot details from Copart API. HTTP Status: ' . $response->status()], 500); //Return Error to UI
            }

        } catch (\Exception $e) {
            Log::error("Exception while fetching lot details for lot number: " . $lotNumber . ": " . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500); //Return Error to UI
        }
    }
}
