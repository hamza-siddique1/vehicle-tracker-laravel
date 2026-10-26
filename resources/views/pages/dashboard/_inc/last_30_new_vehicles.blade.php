<div class="row">
    <div class="col-12 col-lg-6 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <h5 class="card-title mb-0">Last 30 New Vehicles</h5>
            </div>
            <table class="vehicles-table table table-sm table-striped my-0">
                <thead>
                    <tr>
                        <th>Year Make Model</th>
                        <th>VIN</th>
                        <th>Days in Yard</th>
                    </tr>
                </thead>
                <tbody class="text-end">
                    @if (count($last_30_inserted))
                        @foreach ($last_30_inserted as $vehicle)
                            <tr id="{{ $vehicle->id }}">
                                <td><a href="{{ route('vehicles.show', $vehicle->id) }}">{{ $vehicle->description }}</a></td>
                                <td>
                                    @if ($vehicle->auction_lot)
                                        <a href="https://seller.copart.com/lotdisplay/{{ $vehicle->auction_lot }}"
                                            target="_blank">{{ $vehicle->vin }}</a>
                                    @else
                                        {{ $vehicle->vin }}
                                    @endif
                                </td>
                                <td data-toggle="tooltip" data-placement="top"
                                    title="{{ $vehicle->created_at->format('Y-m-d H:i:s') }}">
                                    {{ $vehicle->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3">No vehicles found</td>
                        </tr>
                    @endif

                    <tr>
                        <td colspan="3">
                            <a href="{{ route('dashboard.index', ['type' => 'inserted']) }}" class="btn btn-primary">View All</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @include('pages.dashboard._inc.last_30_updated_vehicles')
</div>
