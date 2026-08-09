<?php

namespace App\Http\Controllers\NDTC;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class NdtcOrderController extends Controller
{
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(int $vehicleId)
    {
        $vehicle = Vehicle::with('metas')
                          ->findOrFail($vehicleId);

        $metas = $vehicle->metas->pluck('meta_value', 'meta_key');

        return view('pages.ndtc.create', [
            'vehicle'    => $vehicle,
            'metas'      => $metas,
            'odometer'   => $metas->get('odometer'),
            'saleDate'   => $metas->get('sale_date') ?? '',
            'titleState' => $metas->get('sale_title_state'),
            'titleType'  => $metas->get('sale_title_type', 'PAPER'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
