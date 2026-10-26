@extends('layouts.app')

@section('title', 'Days In Yard')

@section('content')
    <div class="row">
        <div class="col-12 col-lg-6 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h3 class="mb-2"> Most Days at Auction</h3>
                </div>
                <table class="vehicles-table table table-sm table-striped table-hover my-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Year Make Model</th>
                            <th>VIN</th>
                            <th>Days in Yard</th>
                        </tr>
                    </thead>
                    <tbody class="text-end">
                        @if (count($vehicles_with_days_in_yard))
                            @foreach ($vehicles_with_days_in_yard as $vehicle)
                                <tr id="{{ $vehicle->id }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td><a href="{{ route('vehicles.show', $vehicle->id) }}">{{ $vehicle->description }}</a></td>
                                    <td>
                                        @if ($vehicle->auction_lot)
                                            <a href="https://seller.copart.com/lotdisplay/{{ $vehicle->auction_lot }}"
                                                target="_blank">{{ $vehicle->vin }}</a>
                                        @else
                                            {{ $vehicle->vin }}
                                        @endif
                                    </td>
                                    <td>{{ $vehicle->meta_value }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3">No vehicles found</td>
                            </tr>

                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
