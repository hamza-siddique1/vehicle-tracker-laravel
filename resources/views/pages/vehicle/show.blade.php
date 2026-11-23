@extends('layouts.app')

@section('content')

<style>
    /* make every Select2 dropdown full width */
    #vehicle-form .select2-container {
        width: 100% !important;
        max-width: 100%;
    }
    #vehicle-form .select2-selection--single {
        height: calc(1.5em + .75rem + 2px);
        display: flex;
        align-items: center;
    }
    #vehicle-form .select2-selection__rendered {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    #vehicle-form [class*="col-"] { min-width: 0; }
</style>

@php
    $today      = now()->format('Y-m-d');
    $dateFields = ['left_location', 'date_paid'];
    $lockedFields = ['id', 'created_at', 'updated_at', 'deleted_at'];

    $labels = [
        'id'                 => 'Vehicle ID',
        'vin'                => 'VIN Number',
        'purchase_lot'       => 'Purchase Lot Number',
        'auction_lot'        => 'Auction Lot Number',
        'source'             => 'Source',
        'location'           => 'Current Location',
        'description'        => 'Year-Make-Model',
        'left_location'     => 'Left Location',
        'date_paid'          => 'Purchase Date',
        'invoice_amount'     => 'Purchase Amount($)',
        'created_at'         => 'Date Entered',
        'deleted_at'         => 'Deleted At',
        'claim_number'       => 'Claim Number',
        'status'             => 'Current Status',
        'primary_damage'     => 'Primary Damage',
        'keys'               => 'Has Keys',
        'drivability_rating' => 'Engine Status',
        'odometer'           => 'Mileage',
        'odometer_brand'     => 'Odometer',
        'days_in_yard'       => 'Days In Yard',
        'secondary_damage'   => 'Secondary Damage',
        'sale_title_state'   => 'Sale Title State',
        'sale_title_type'    => 'Sale Title Type',
        'sale_price'         => 'Sale Price',

        'arrival_date'     => 'Arrival Date',
        'left_yard_date'   => 'Left Yard / Sent to Auction Date',
        'prev_owner_name'  => 'Name',
        'prev_owner_phone' => 'Phone',
        'prev_owner_street'=> 'Street Address',
        'prev_owner_city'  => 'City',
        'prev_owner_state' => 'State',
        'prev_owner_zip'   => 'ZIP',
        'prev_owner_country'=> 'Country',
        'buyer_name'       => 'Buyer Name',
        'buyer_address'    => 'Address',
        'buyer_phone'      => 'Phone',
        'buyer_email'      => 'Email',

        'title_received_date'        => 'Title Received Date',
        'reassignment_number'        => 'Reassignment Number',
        'title_sent_to_auction_date' => 'Title Sent to Auction Date',
    ];
    $label = fn ($k) => $labels[$k] ?? ucwords(str_replace('_', ' ', $k));
@endphp

<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ url()->previous() }}" class="text-decoration-none small">&larr; Back</a>
            <h4 class="mb-0">Vehicle #{{ $vehicle->id }}</h4>
        </div>
        <div class="d-flex">
            @if (in_array(auth()->user()->role, ['admin', 'ndtc_manager']))
                @if ($ndtcOrder)
                    <a href="{{ route('ndtc.orders.show', $ndtcOrder) }}" class="btn btn-info">View NDTC Order</a>
                @endif
                {{-- a new order is only possible when there is none, or the last one is finished --}}
                @if (! $ndtcOrder || in_array($ndtcOrder->status, \App\Models\NdtcOrder::TERMINAL_STATUSES))
                    <a href="{{ route('ndtc.orders.create', $vehicle->id) }}" class="btn btn-success ml-2">Create NDTC Order</a>
                @endif
            @endif
            <button type="submit" form="vehicle-form" class="btn btn-primary ml-2">Save changes</button>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-3 px-4 mb-3">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="vehicle-form" method="POST" action="{{ route('vehicles.update', $vehicle) }}">
        @csrf
        @method('PUT')

        {{-- ================= VEHICLE ================= --}}
        <div class="card mb-3">
            <div class="card-header">Vehicle</div>
            <div class="card-body">
                <div class="row gx-3 gy-4">
                    @foreach ($vehicleData as $key => $value)
                        <div class="col-md-6 col-lg-4">
                            <label for="f_{{ $key }}" class="form-label small text-muted mb-1">{{ $label($key) }}</label>

                            @if ($key === 'location')
                                <select id="f_{{ $key }}" name="{{ $key }}" class="form-select select2">
                                    <option value="">Select Location</option>
                                    @foreach ($options['location'] as $choice)
                                        <option value="{{ $choice }}" @selected($choice == $value)>{{ $choice }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input id="f_{{ $key }}"
                                       type="text"
                                       name="{{ $key }}"
                                       value="{{ in_array($key, $dateFields) && is_null($value) ? $today : $value }}"
                                       class="form-control {{ in_array($key, $dateFields) && ! in_array($key, $lockedFields) ? 'daterange' : '' }}"
                                       @if ($key === 'source') placeholder="copart | iaai" @endif
                                       @disabled(in_array($key, $lockedFields))>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ================= AUCTION / TITLE ================= --}}
        @foreach ($metaGroups as $title => $metas)
            @continue($title === 'Title Information') {{-- replaced by the Title History card below --}}
            <div class="card mb-3">
                <div class="card-header">{{ $title }}</div>
                <div class="card-body">
                    <div class="row gx-3 gy-4">
                        @foreach ($metas as $key => $value)
                            <div class="col-md-6 col-lg-4">
                                <label for="f_{{ $key }}" class="form-label small text-muted mb-1">{{ $label($key) }}</label>

                                @if (isset($options[$key]))
                                    <select id="f_{{ $key }}" name="{{ $key }}" class="form-select select2">
                                        <option value="">Select {{ $label($key) }}</option>
                                        @foreach ($options[$key] as $choice)
                                            <option value="{{ $choice }}" @selected($choice == $value)>{{ $choice }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input id="f_{{ $key }}"
                                        type="{{ $key === 'odometer' ? 'number' : 'text' }}"
                                        @if ($key === 'odometer') step="any" @endif
                                        name="{{ $key }}"
                                        value="{{ old($key, $value) }}"
                                        class="form-control {{ in_array($key, ['arrival_date', 'left_yard_date', 'title_received_date', 'title_sent_to_auction_date']) ? 'daterange-optional' : '' }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach


        {{-- ================= NOTES ================= --}}
        <div class="card mb-3">
            <div class="card-header">Notes</div>
            <div class="card-body">
                @foreach ($notes as $note)
                    @php $canEdit = $note->user_id === auth()->id(); @endphp
                    <div class="border-bottom pb-3 mb-3">
                        <div class="small text-muted mb-1">
                            {{ $note->user->name ?? 'Unknown' }} &middot; {{ $note->created_at?->format('d.m.Y H:i') }}
                        </div>
                        <textarea name="notes[{{ $note->id }}]"
                                  class="form-control"
                                  rows="2"
                                  @disabled(! $canEdit)>{{ $note->note }}</textarea>
                    </div>
                @endforeach

                <label class="form-label">Add New Note</label>
                <textarea name="new_note" class="form-control" rows="3"></textarea>
            </div>
        </div>
    </form>

    {{-- ================= TITLE HISTORY ================= --}}
    {{-- its own form (outside #vehicle-form) so adding a title never touches the vehicle's other fields --}}
    <div class="card mb-3" id="title-history">
        <div class="card-header">Title History</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-3">
                    <thead>
                    <tr>
                        <th>Title Number</th>
                        <th>Received</th>
                        <th>Reassignment #</th>
                        <th>Sent to Auction</th>
                        <th>Notes</th>
                        <th>Added By</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($titles as $t)
                        <tr>
                            <td>
                                {{ $t->title_number ?: '—' }}
                                @if ($loop->first)
                                    <span class="badge badge-success">Current</span>
                                @endif
                            </td>
                            <td>{{ $t->received_date?->format('d.m.Y') ?? '—' }}</td>
                            <td>{{ $t->reassignment_number ?: '—' }}</td>
                            <td>{{ $t->sent_to_auction_date?->format('d.m.Y') ?? '—' }}</td>
                            <td>{{ $t->notes }}</td>
                            <td>{{ $t->creator->name ?? '—' }}</td>
                            <td class="text-right">
                                @if (auth()->user()->role === 'admin')
                                    <form method="POST" action="{{ route('vehicles.titles.destroy', [$vehicle, $t]) }}"
                                          onsubmit="return confirmSubmission(this, 'Remove this title from the history?')"
                                          style="display: inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm text-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">No titles recorded yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if (in_array(auth()->user()->role, ['admin', 'ndtc_manager', 'yard_manager', 'vehicle_manager']))
                <form method="POST" action="{{ route('vehicles.titles.store', $vehicle) }}">
                    @csrf
                    <div class="small text-muted mb-2">Add a new title. Earlier titles are kept.</div>
                    <div class="row gx-3 gy-2">
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Title Number *</label>
                            <input type="text" name="title_number" value="{{ old('title_number') }}" class="form-control" maxlength="50" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Received Date *</label>
                            <input type="date" name="received_date" value="{{ old('received_date') }}" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Reassignment Number</label>
                            <input type="text" name="reassignment_number" value="{{ old('reassignment_number') }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Sent to Auction Date</label>
                            <input type="date" name="sent_to_auction_date" value="{{ old('sent_to_auction_date') }}" class="form-control">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label small text-muted mb-1">Notes</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" class="form-control" maxlength="1000">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">Add title</button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

<script>
    // runs after the layout has loaded jQuery, Select2 and the date picker
    window.addEventListener('load', function () {
        $('#vehicle-form .select2').each(function () {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
            $(this).select2({ width: '100%' });
        });

        $('#vehicle-form .daterange').daterangepicker({
            singleDatePicker: true,
            autoUpdateInput: true,
            locale: { format: 'YYYY-MM-DD' }
        });

        $('#vehicle-form .daterange-optional').each(function () {
    var $el = $(this);
    var current = moment($el.val(), ['YYYY-MM-DD', 'DD-MMM-YYYY', 'DD.MM.YYYY'], true);

    var opts = {
        singleDatePicker: true,
        autoUpdateInput: false,
        locale: { format: 'YYYY-MM-DD', cancelLabel: 'Clear' }
    };

    if (current.isValid()) {
        opts.startDate = current;
        $el.val(current.format('YYYY-MM-DD'));   // normalise so it saves in one format
    }

    $el.daterangepicker(opts);

    $el.on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });

    $el.on('cancel.daterangepicker', function () {
        $(this).val('');
    });
});
    });
</script>

@endsection
