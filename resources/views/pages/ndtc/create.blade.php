{{-- resources/views/ndtc/orders/create.blade.php --}}

@extends('layouts.app')

@section('title', 'Create NDTC Order')

@section('styles')
    <style>
        .field-badge {
            font-size: 0.65rem;
            font-weight: 600;
            padding: 1px 5px;
            border-radius: 3px;
        }
        .badge-auto    { background: #d1fae5; color: #065f46; }
        .badge-manual  { background: #fef3c7; color: #92400e; }
        .badge-empty   { background: #fee2e2; color: #991b1b; }

        .brand-check {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: border-color .15s;
        }
        .brand-check:hover       { border-color: #3b7ddd; }
        .brand-check.is-checked  { border-color: #dc3545; background: #fff5f5; }

        .section-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #adb5bd;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 0.4rem;
            margin-bottom: 1rem;
        }

        .vin-banner {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%);
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .vin-banner .vin-number {
            font-family: monospace;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: .08em;
        }
        .vin-banner .vin-desc   { font-size: 0.85rem; color: rgba(255,255,255,0.6); margin-top: 2px; }
        .vin-badge {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 6px;
            padding: 5px 14px;
            text-align: center;
        }
        .vin-badge .txn-type    { font-size: 1rem; font-weight: 700; color: #fff; }
        .vin-badge .txn-label   { font-size: 0.65rem; color: rgba(255,255,255,.55); }

        .required-note { color: #dc3545; font-weight: 600; }

        .form-control[readonly] { background: #f8f9fa; cursor: not-allowed; }

        .sticky-footer-actions {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid #dee2e6;
            padding: 0.875rem 0;
            margin-top: 1.5rem;
            z-index: 100;
        }
    </style>
@endsection

@php
    $role = Auth()->user()->role;

    // ── Pull vehicle metas ────────────────────────────────────
    // Adjust this based on how you access vehicle_metas in your app
    $metas = $vehicle->metas->pluck('meta_value', 'meta_key');

    $odometer      = $metas->get('odometer', '');
    $saleDate      = $metas->get('sale_date', '');
    $titleState    = $metas->get('sale_title_state', '');
    $titleType     = $metas->get('sale_title_type', 'PAPER');

    // Parse description → year / make / model (also done in JS)
    $descParts  = explode(' ', trim($vehicle->description), 3);
    $parsedYear = $descParts[0] ?? '';
@endphp

@section('content')
    <h1 class="h3 mb-3">Create NDTC Order</h1>

    {{-- Alerts --}}
    @if(session('error'))
        <x-alert type="danger">{{ session('error') }}</x-alert>
    @endif

    @if ($errors->any())
        <x-alert type="danger">
            Please fix the following errors before submitting:
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    {{-- VIN Banner --}}
    <div class="vin-banner">
        <div>
            <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.5);font-weight:600">
                Vehicle
            </div>
            <div class="vin-number">{{ $vehicle->vin }}</div>
            <div class="vin-desc" id="banner-desc">{{ $vehicle->description }}</div>
        </div>
        <div class="vin-badge">
            <div class="txn-type">TNL</div>
            <div class="txn-label">Transaction</div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="d-flex align-items-center gap-2 mb-3" style="gap:12px">
        <small><span class="field-badge badge-auto">AUTO</span> Auto-filled from DB</small>
        <small><span class="field-badge badge-manual">VERIFY</span> Please verify</small>
        <small><span class="field-badge badge-empty">REQUIRED</span> Must be entered</small>
    </div>

    <form action="{{ route('ndtc.orders.store', $vehicle) }}"
          method="POST"
          id="ndtcOrderForm">
        @csrf

        {{-- Hidden fields --}}
        <input type="hidden" name="vehicle_id"          value="{{ $vehicle->id }}">
        <input type="hidden" name="transaction_type"    value="TNL">
        <input type="hidden" name="vertical"            value="NATIONAL_RETAILER">
        <input type="hidden" name="correlation_id"      value="{{ $vehicle->id }}">

        {{-- ══ SECTION 1: VEHICLE INFORMATION ══════════════════════ --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="align-middle mr-2" data-feather="truck"></i>
                    Vehicle Information
                </h5>
            </div>
            <div class="card-body">

                {{-- VIN --}}
                <div class="form-group row">
                    <label class="col-md-3 col-form-label">
                        VIN <span class="required-note">*</span>
                    </label>
                    <div class="col-sm-6">
                        <input type="text"
                               class="form-control font-monospace"
                               name="vin"
                               value="{{ $vehicle->vin }}"
                               readonly>
                        <small class="form-text text-muted">Auto-filled from vehicle record. Cannot be changed here.</small>
                    </div>
                    <div class="col-sm-3 d-flex align-items-center">
                        <span class="field-badge badge-auto">AUTO</span>
                    </div>
                </div>

                <div class="form-row">
                    {{-- Year --}}
                    <div class="form-group col-md-2">
                        <label>Year <span class="required-note">*</span></label>
                        <input type="number"
                               class="form-control @error('year') is-invalid @enderror"
                               name="year"
                               id="field-year"
                               value="{{ old('year', $parsedYear) }}"
                               min="1980" max="2030">
                        @error('year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text"><span class="field-badge badge-auto">AUTO</span></small>
                    </div>

                    {{-- Make --}}
                    <div class="form-group col-md-3">
                        <label>Make (NCIC Code) <span class="required-note">*</span></label>
                        <select class="form-control @error('make') is-invalid @enderror"
                                name="make"
                                id="field-make">
                            <option value="">-- Select Make --</option>
                            <option value="ACUR">ACUR — Acura</option>
                            <option value="AUDI">AUDI — Audi</option>
                            <option value="BMW">BMW — BMW</option>
                            <option value="BUIC">BUIC — Buick</option>
                            <option value="CADI">CADI — Cadillac</option>
                            <option value="CHEV">CHEV — Chevrolet</option>
                            <option value="CHRY">CHRY — Chrysler</option>
                            <option value="DODG">DODG — Dodge</option>
                            <option value="FORD">FORD — Ford</option>
                            <option value="GMC">GMC — GMC</option>
                            <option value="HOND">HOND — Honda</option>
                            <option value="HYUN">HYUN — Hyundai</option>
                            <option value="INFI">INFI — Infiniti</option>
                            <option value="JEEP">JEEP — Jeep</option>
                            <option value="KIA">KIA — Kia</option>
                            <option value="LEXS">LEXS — Lexus</option>
                            <option value="LINC">LINC — Lincoln</option>
                            <option value="LNDR">LNDR — Land Rover</option>
                            <option value="MAZD">MAZD — Mazda</option>
                            <option value="MERZ">MERZ — Mercedes-Benz</option>
                            <option value="MINI">MINI — Mini</option>
                            <option value="MITS">MITS — Mitsubishi</option>
                            <option value="NISS">NISS — Nissan</option>
                            <option value="PONT">PONT — Pontiac</option>
                            <option value="PORS">PORS — Porsche</option>
                            <option value="RRAM">RRAM — Ram</option>
                            <option value="SUBA">SUBA — Subaru</option>
                            <option value="TOYT">TOYT — Toyota</option>
                            <option value="VOLK">VOLK — Volkswagen</option>
                            <option value="VOLV">VOLV — Volvo</option>
                        </select>
                        @error('make')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text"><span class="field-badge badge-auto">AUTO</span></small>
                    </div>

                    {{-- Model --}}
                    <div class="form-group col-md-4">
                        <label>Model <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('model') is-invalid @enderror"
                               name="model"
                               id="field-model"
                               value="{{ old('model') }}"
                               placeholder="e.g. GLB 250">
                        @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text"><span class="field-badge badge-auto">AUTO</span></small>
                    </div>

                    {{-- Vehicle Class --}}
                    <div class="form-group col-md-3">
                        <label>Vehicle Class <span class="required-note">*</span></label>
                        <select class="form-control @error('vehicle_class') is-invalid @enderror"
                                name="vehicle_class">
                            <option value="CARS_AND_TRUCKS" selected>Cars &amp; Trucks</option>
                            <option value="TRUCKS">Trucks</option>
                            <option value="MOTORCYCLES">Motorcycles</option>
                            <option value="BUSES">Buses</option>
                            <option value="TRAILERS_AND_SEMI_TRAILERS">Trailers &amp; Semi-Trailers</option>
                            <option value="TRAVEL_TRAILERS">Travel Trailers</option>
                            <option value="TRAILERS">Trailers</option>
                            <option value="ATV">ATV</option>
                            <option value="ANTIQUE_MOTOR">Antique Motor</option>
                            <option value="MOBILE_HOME">Mobile Home</option>
                            <option value="FARM_TRUCKS">Farm Trucks</option>
                        </select>
                        @error('vehicle_class')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    {{-- Body Style --}}
                    <div class="form-group col-md-3">
                        <label>Body Style <span class="required-note">*</span></label>
                        <select class="form-control @error('body_style') is-invalid @enderror"
                                name="body_style">
                            <option value="">-- Select --</option>
                            <optgroup label="Common">
                                <option value="SD">SD — Sedan</option>
                                <option value="4W">4W — SUV / 4DR Wagon</option>
                                <option value="UT">UT — 2DR Sport Utility</option>
                                <option value="CP">CP — Coupe</option>
                                <option value="CV">CV — Convertible</option>
                                <option value="HB">HB — Hatchback</option>
                                <option value="SW">SW — Station Wagon</option>
                                <option value="PK">PK — Pickup</option>
                                <option value="PV">PV — Passenger Van</option>
                                <option value="CG">CG — Cargo Van</option>
                                <option value="TK">TK — Truck</option>
                            </optgroup>
                            <optgroup label="Other">
                                <option value="2D">2D — 2 Door Sedan</option>
                                <option value="2H">2H — Hatchback 2DR</option>
                                <option value="2T">2T — Hardtop 2DR</option>
                                <option value="2W">2W — Wagon 2DR</option>
                                <option value="3C">3C — Extended Cab</option>
                                <option value="4C">4C — 4DR Ext Cab</option>
                                <option value="4T">4T — Hardtop 4DR</option>
                                <option value="MC">MC — Motorcycle</option>
                                <option value="MH">MH — Motor Home</option>
                                <option value="TL">TL — Trailer</option>
                            </optgroup>
                        </select>
                        @error('body_style')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fuel Type --}}
                    <div class="form-group col-md-3">
                        <label>Fuel Type <span class="required-note">*</span></label>
                        <select class="form-control @error('fuel_type') is-invalid @enderror"
                                name="fuel_type">
                            <option value="GAS" selected>GAS — Gasoline</option>
                            <option value="DIESEL">DIESEL — Diesel</option>
                            <option value="ELECTRIC">ELECTRIC — Electric</option>
                            <option value="ELECTRIC_AND_GAS_HYBRID">HYBRID — Electric &amp; Gas</option>
                            <option value="ELECTRIC_AND_DIESEL_HYBRID">HYBRID — Electric &amp; Diesel</option>
                            <option value="FLEXIBLE">FLEXIBLE — Flex Fuel</option>
                            <option value="ETHANOL">ETHANOL</option>
                            <option value="PROPANE">PROPANE</option>
                            <option value="COMPRESSED_NATURAL_GAS">CNG</option>
                            <option value="LIQUID_NATURAL_GAS">LNG</option>
                            <option value="HYDROGEN_FUEL_CELL">HYDROGEN</option>
                            <option value="UNKNOWN">UNKNOWN</option>
                        </select>
                        @error('fuel_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Weight --}}
                    <div class="form-group col-md-3">
                        <label>Vehicle Weight (LBS) <span class="required-note">*</span></label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control @error('weight') is-invalid @enderror"
                                   name="weight"
                                   value="{{ old('weight') }}"
                                   placeholder="e.g. 4200"
                                   min="100" max="99999">
                            <div class="input-group-append">
                                <span class="input-group-text">LBS</span>
                            </div>
                            @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ══ SECTION 2: EXISTING TITLE ════════════════════════════ --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="align-middle mr-2" data-feather="file-text"></i>
                    Existing Title Information
                </h5>
            </div>
            <div class="card-body">

                <div class="alert alert-info">
                    <i class="align-middle mr-1" data-feather="info"></i>
                    The title number must exactly match what is in NMVTIS. Check the physical paper title in hand.
                </div>

                <div class="form-row">
                    {{-- Title Number --}}
                    <div class="form-group col-md-4">
                        <label>Title Number <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('title_number') is-invalid @enderror"
                               name="title_number"
                               value="{{ old('title_number') }}"
                               placeholder="Enter exactly as printed on title">
                        @error('title_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-danger font-weight-bold">
                            <span class="field-badge badge-empty">REQUIRED</span>
                            Enter from physical title — not pre-filled
                        </small>
                    </div>

                    {{-- Issuing State --}}
                    <div class="form-group col-md-3">
                        <label>Issuing State <span class="required-note">*</span></label>
                        <select class="form-control @error('issuing_state') is-invalid @enderror"
                                name="issuing_state">
                            <option value="">-- Select State --</option>
                            @foreach(['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'] as $state)
                                <option value="{{ $state }}"
                                    {{ old('issuing_state', $titleState) == $state ? 'selected' : '' }}>
                                    {{ $state }}
                                </option>
                            @endforeach
                        </select>
                        @error('issuing_state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-auto">AUTO</span>
                            From <code>sale_title_state</code>
                        </small>
                    </div>

                    {{-- Title Type --}}
                    <div class="form-group col-md-2">
                        <label>Title Type <span class="required-note">*</span></label>
                        <select class="form-control @error('title_type') is-invalid @enderror"
                                name="title_type">
                            <option value="PAPER"
                                {{ old('title_type', $titleType) == 'PAPER' ? 'selected' : '' }}>
                                Paper
                            </option>
                            <option value="ELECTRONIC"
                                {{ old('title_type', $titleType) == 'ELECTRONIC' ? 'selected' : '' }}>
                                Electronic
                            </option>
                        </select>
                        @error('title_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-auto">AUTO</span>
                            From <code>sale_title_type</code>
                        </small>
                    </div>
                </div>

                {{-- Title Brands --}}
                <div class="section-label">Title Brands</div>
                <p class="text-muted small mb-2">
                    Vehicles from Copart / IAAI typically carry a Salvage brand.
                    Verify against the physical title and check all that apply.
                </p>
                <div class="form-row">
                    @foreach([
                        'SALVAGE'  => ['SALVAGE',  true],
                        'JUNK'     => ['JUNK',     false],
                        'REBUILT'  => ['REBUILT',  false],
                        'FLOOD'    => ['FLOOD',    false],
                        'LEMON'    => ['LEMON LAW',false],
                        'NONE'     => ['NONE / CLEAN', false],
                    ] as $value => [$label, $defaultChecked])
                        <div class="col-md-2 mb-2">
                            <label class="brand-check {{ $defaultChecked ? 'is-checked' : '' }}"
                                   id="brand-label-{{ strtolower($value) }}">
                                <input type="checkbox"
                                       name="title_brands[]"
                                       value="{{ $value }}"
                                       class="brand-checkbox"
                                       {{ $defaultChecked ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        {{-- ══ SECTION 3: ODOMETER ══════════════════════════════════ --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="align-middle mr-2" data-feather="activity"></i>
                    Odometer
                </h5>
            </div>
            <div class="card-body">
                <div class="form-row">

                    {{-- Reading --}}
                    <div class="form-group col-md-3">
                        <label>Odometer Reading <span class="required-note">*</span></label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control @error('odometer_reading') is-invalid @enderror"
                                   name="odometer_reading"
                                   value="{{ old('odometer_reading', $odometer) }}"
                                   min="0">
                            <div class="input-group-append">
                                <span class="input-group-text">MI</span>
                            </div>
                        </div>
                        @error('odometer_reading')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-auto">AUTO</span>
                            From <code>odometer</code> meta
                        </small>
                    </div>

                    {{-- Condition --}}
                    <div class="form-group col-md-3">
                        <label>Condition <span class="required-note">*</span></label>
                        <select class="form-control @error('odometer_condition') is-invalid @enderror"
                                name="odometer_condition">
                            <option value="ACTUAL" selected>ACTUAL — Actual mileage</option>
                            <option value="NOT_ACTUAL">NOT_ACTUAL — Exceeds limits / not accurate</option>
                            <option value="EXEMPT">EXEMPT — Vehicle is exempt</option>
                            <option value="NO_ODOMETER">NO_ODOMETER — No odometer</option>
                        </select>
                        @error('odometer_condition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Still provide reading even if not actual
                        </small>
                    </div>

                    {{-- Date --}}
                    <div class="form-group col-md-3">
                        <label>Odometer Date <span class="required-note">*</span></label>
                        <input type="date"
                               class="form-control @error('odometer_date') is-invalid @enderror"
                               name="odometer_date"
                               value="{{ old('odometer_date', $saleDate) }}">
                        @error('odometer_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-auto">AUTO</span>
                            From <code>sale_date</code> meta
                        </small>
                    </div>

                </div>
            </div>
        </div>

        {{-- ══ SECTION 4: DISPOSING ENTITY ═════════════════════════ --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="align-middle mr-2" data-feather="building"></i>
                    Disposing Entity (Auction)
                </h5>
            </div>
            <div class="card-body">

                <div class="alert alert-secondary small">
                    <i class="align-middle mr-1" data-feather="info"></i>
                    Auction companies do not need to be pre-registered with CHAMP.
                    They can be submitted ad-hoc on each order.
                </div>

                <div class="form-row">
                    {{-- Name --}}
                    <div class="form-group col-md-4">
                        <label>Entity Name <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('disposing_name') is-invalid @enderror"
                               name="disposing_name"
                               value="{{ old('disposing_name', $vehicle->source) }}"
                               placeholder="e.g. Copart Inc.">
                        @error('disposing_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-auto">AUTO</span>
                            From <code>vehicles.source</code>
                        </small>
                    </div>

                    {{-- Address 1 --}}
                    <div class="form-group col-md-5">
                        <label>Address Line 1 <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('disposing_address1') is-invalid @enderror"
                               name="disposing_address1"
                               value="{{ old('disposing_address1') }}"
                               placeholder="Street address of auction location">
                        @error('disposing_address1')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-empty">REQUIRED</span>
                            Enter auction branch address
                        </small>
                    </div>

                    {{-- Address 2 --}}
                    <div class="form-group col-md-3">
                        <label>Address Line 2</label>
                        <input type="text"
                               class="form-control"
                               name="disposing_address2"
                               value="{{ old('disposing_address2') }}"
                               placeholder="Suite, unit (optional)">
                    </div>
                </div>

                <div class="form-row">
                    {{-- City --}}
                    <div class="form-group col-md-4">
                        <label>City <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('disposing_city') is-invalid @enderror"
                               name="disposing_city"
                               value="{{ old('disposing_city') }}">
                        @error('disposing_city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- State --}}
                    <div class="form-group col-md-3">
                        <label>State <span class="required-note">*</span></label>
                        <select class="form-control @error('disposing_state') is-invalid @enderror"
                                name="disposing_state">
                            <option value="">-- State --</option>
                            @foreach(['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'] as $state)
                                <option value="{{ $state }}" {{ old('disposing_state') == $state ? 'selected' : '' }}>
                                    {{ $state }}
                                </option>
                            @endforeach
                        </select>
                        @error('disposing_state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ZIP --}}
                    <div class="form-group col-md-2">
                        <label>ZIP Code <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('disposing_zip') is-invalid @enderror"
                               name="disposing_zip"
                               value="{{ old('disposing_zip') }}"
                               placeholder="00000"
                               maxlength="10">
                        @error('disposing_zip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- County --}}
                    <div class="form-group col-md-3">
                        <label>County</label>
                        <input type="text"
                               class="form-control"
                               name="disposing_county"
                               value="{{ old('disposing_county') }}"
                               placeholder="Optional">
                    </div>
                </div>

            </div>
        </div>

        {{-- ══ SECTION 5: TRANSFER DETAILS ══════════════════════════ --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="align-middle mr-2" data-feather="calendar"></i>
                    Transfer Details
                </h5>
            </div>
            <div class="card-body">
                <div class="form-row">

                    {{-- Transfer Date --}}
                    <div class="form-group col-md-3">
                        <label>Transfer Date <span class="required-note">*</span></label>
                        <input type="date"
                               class="form-control @error('transfer_date') is-invalid @enderror"
                               name="transfer_date"
                               value="{{ old('transfer_date', $saleDate) }}">
                        @error('transfer_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-auto">AUTO</span>
                            From <code>sale_date</code> meta
                        </small>
                    </div>

                    {{-- Internal Reference --}}
                    <div class="form-group col-md-4">
                        <label>Internal Reference (Correlation ID)</label>
                        <input type="text"
                               class="form-control font-monospace"
                               value="{{ $vehicle->purchase_lot ?? $vehicle->auction_lot ?? 'Auto-generated' }}"
                               readonly>
                        <small class="form-text">
                            <span class="field-badge badge-auto">AUTO</span>
                            Returned in every NDTC webhook for tracking
                        </small>
                    </div>

                </div>

                {{-- Notes --}}
                <div class="form-group">
                    <label>Declaration / Notes <small class="text-muted">(Optional)</small></label>
                    <textarea class="form-control"
                              name="notes"
                              rows="2"
                              placeholder="Any notes for the DMV reviewer — use for unusual cases only">{{ old('notes') }}</textarea>
                    <small class="form-text text-muted">
                        Submitted as a DECLARATION_PAGE document if filled in
                    </small>
                </div>

            </div>
        </div>

        {{-- ══ ACTIONS ═══════════════════════════════════════════════ --}}
        <div class="sticky-footer-actions">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="align-middle mr-1" data-feather="info"></i>
                    After creating the order you will be prompted to upload the title front &amp; back scan.
                </small>
                <div>
                    <a href="{{ url()->previous() }}"
                       class="btn btn-secondary mr-2">
                        Cancel
                    </a>
                    <button type="submit"
                            class="btn btn-primary"
                            id="submit-btn"
                            @if($role == 'viewer') disabled @endif>
                        <i class="align-middle mr-1" data-feather="send"></i>
                        Create NDTC Order
                    </button>
                </div>
            </div>
        </div>

    </form>

@endsection

@section('scripts')
<script>
$(document).ready(function () {

    // ── DESCRIPTION PARSER ────────────────────────────────────
    const MAKE_TO_NCIC = {
        'MERCEDES-BENZ': 'MERZ', 'CHEVROLET': 'CHEV', 'DODGE':      'DODG',
        'TOYOTA':        'TOYT', 'CADILLAC':  'CADI', 'HONDA':      'HOND',
        'JEEP':          'JEEP', 'FORD':      'FORD', 'GMC':        'GMC',
        'LINCOLN':       'LINC', 'LAND ROVER':'LNDR', 'LEXUS':      'LEXS',
        'SUBARU':        'SUBA', 'AUDI':      'AUDI', 'BMW':        'BMW',
        'NISSAN':        'NISS', 'HYUNDAI':   'HYUN', 'KIA':        'KIA',
        'VOLKSWAGEN':    'VOLK', 'VOLVO':     'VOLV', 'BUICK':      'BUIC',
        'INFINITI':      'INFI', 'MAZDA':     'MAZD', 'MINI':       'MINI',
        'MITSUBISHI':    'MITS', 'PONTIAC':   'PONT', 'PORSCHE':    'PORS',
        'RAM':           'RRAM', 'CHRYSLER':  'CHRY', 'ACURA':      'ACUR',
    };

    const MULTI_WORD = ['MERCEDES-BENZ', 'LAND ROVER', 'ROLLS ROYCE', 'ASTON MARTIN'];

    function parseDescription(desc) {
        if (!desc) return {};
        const upper = desc.toUpperCase().trim();
        const parts = upper.split(/\s+/);
        const year  = parts[0];
        const rest  = parts.slice(1).join(' ');

        let make = null, model = null;

        // Check multi-word makes first
        for (const mw of MULTI_WORD) {
            if (rest.startsWith(mw)) {
                make  = mw;
                model = rest.slice(mw.length).trim();
                break;
            }
        }

        // Single word make
        if (!make) {
            const first = parts[1];
            if (MAKE_TO_NCIC[first]) {
                make  = first;
                model = parts.slice(2).join(' ');
            }
        }

        return {
            year,
            makeNcic: make ? (MAKE_TO_NCIC[make] || make.slice(0, 4)) : '',
            model:    model || '',
        };
    }

    // Auto-fill on load
    const description = @json($vehicle->description ?? '');
    const parsed = parseDescription(description);

    if (parsed.year    && !$('#field-year').val())  $('#field-year').val(parsed.year);
    if (parsed.makeNcic)                             $('#field-make').val(parsed.makeNcic);
    if (parsed.model   && !$('#field-model').val()) $('#field-model').val(parsed.model);

    // ── BRAND CHECKBOX STYLING ────────────────────────────────
    $('.brand-checkbox').on('change', function () {
        $(this).closest('.brand-check').toggleClass('is-checked', this.checked);
    });

    // ── SUBMIT CONFIRMATION ───────────────────────────────────
    $('#ndtcOrderForm').on('submit', function (e) {
        const confirmed = confirm(
            'Submit this order to NDTC?\n\n' +
            'Once submitted, the system will wait for a confirmation from CHAMP ' +
            'before requesting document uploads.'
        );
        if (!confirmed) e.preventDefault();
    });

});
</script>
@endsection
