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
        <div class="card mb-3">
            <div class="card-header">Auction / Title details</div>
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
                                       value="{{ $value }}"
                                       class="form-control">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

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
    });
</script>

@endsection
