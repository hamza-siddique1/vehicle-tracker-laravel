{{-- resources/views/ndtc/orders/create.blade.php --}}

@extends('layouts.app')

@section('title', 'Create NDTC Order - Transfer No Lien')

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
        .badge-optional { background: #e5e7eb; color: #374151; }

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

        .entity-type-badge {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 10px;
            background: #e9ecef;
        }

        .help-text-block {
            background: #f8f9fa;
            border-left: 3px solid #3b7ddd;
            padding: 0.5rem 1rem;
            margin: 0.5rem 0;
            border-radius: 4px;
        }
    </style>
@endsection

@php
    $role = Auth()->user()->role;

    // ── Pull vehicle metas ────────────────────────────────────
    $metas = $vehicle->metas->pluck('meta_value', 'meta_key');

    $odometer      = $metas->get('odometer', '');
    $saleDate      = $metas->get('sale_date', '');
    $titleState    = $metas->get('sale_title_state', '');
    $titleType     = $metas->get('sale_title_type', 'PAPER');
    $auctionSource = $vehicle->source ?? '';

    // Parse description → year / make / model
    $descParts  = explode(' ', trim($vehicle->description), 3);
    $parsedYear = $descParts[0] ?? '';

    // Determine NCIC Make code from description
    $makeMap = [
        'ACURA' => 'ACUR', 'AUDI' => 'AUDI', 'BMW' => 'BMW', 'BUICK' => 'BUIC',
        'CADILLAC' => 'CADI', 'CHEVROLET' => 'CHEV', 'CHRYSLER' => 'CHRY',
        'DODGE' => 'DODG', 'FORD' => 'FORD', 'GMC' => 'GMC', 'HONDA' => 'HOND',
        'HYUNDAI' => 'HYUN', 'INFINITI' => 'INFI', 'JEEP' => 'JEEP', 'KIA' => 'KIA',
        'LEXUS' => 'LEXS', 'LINCOLN' => 'LINC', 'LAND ROVER' => 'LNDR',
        'MAZDA' => 'MAZD', 'MERCEDES-BENZ' => 'MERZ', 'MINI' => 'MINI',
        'MITSUBISHI' => 'MITS', 'NISSAN' => 'NISS', 'PONTIAC' => 'PONT',
        'PORSCHE' => 'PORS', 'RAM' => 'RRAM', 'SUBARU' => 'SUBA',
        'TOYOTA' => 'TOYT', 'VOLKSWAGEN' => 'VOLK', 'VOLVO' => 'VOLV'
    ];

    $parsedMake = '';
    $parsedModel = '';
    if (isset($descParts[1])) {
        $makeKey = strtoupper($descParts[1]);
        if (isset($makeMap[$makeKey])) {
            $parsedMake = $makeMap[$makeKey];
            $parsedModel = $descParts[2] ?? '';
        } elseif (isset($descParts[2])) {
            // Check multi-word makes
            $twoWord = strtoupper($descParts[1] . ' ' . $descParts[2]);
            if (isset($makeMap[$twoWord])) {
                $parsedMake = $makeMap[$twoWord];
                $parsedModel = $descParts[3] ?? '';
            }
        }
    }

    // ── TEST DEFAULTS (Remove before production) ──────────────
    $testDefaults = app()->environment('local') ? [
        'title_number'          => 'NY123456789',
        'weight'                => '4500',
        'odometer_reading'      => '4200',
        'body_style'            => '4W',
        'odometer_date'         => '2024-01-18',
        'transfer_date'         => '2024-01-18',
        'disposing_address1'    => '123 Auction Drive',
        'disposing_city'        => 'Linden',
        'disposing_state'       => 'NJ',
        'disposing_zip'         => '07036',
        'issuing_state'         => 'AL',
        'acquiring_entity_name' => 'John Doe Auto Sales',
        'acquiring_address1'    => '456 Business Blvd',
        'acquiring_city'        => 'Columbus',
        'acquiring_state'       => 'OH',
        'acquiring_zip'         => '43215',
        'title_work_representative_first' => 'Jane',
        'title_work_representative_last'  => 'Doe',
        'title_work_representative_email' => 'jane.doe@company.com',
        'title_work_representative_phone' => '555-555-5555',
    ] : [];

    // Helper — checks old() first, then testDefaults, then your DB value
    $val = fn($field, $dbValue = null) =>
        old($field, $testDefaults[$field] ?? $dbValue ?? '');
@endphp

@section('content')
    <h1 class="h3 mb-3">Create NDTC Order - Transfer No Lien (TNL)</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
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
    <div class="d-flex align-items-center gap-2 mb-3" style="gap:12px; flex-wrap: wrap;">
        <small><span class="field-badge badge-auto">AUTO</span> Auto-filled from DB (read-only)</small>
        <small><span class="field-badge badge-manual">VERIFY</span> Pre-filled but editable</small>
        <small><span class="field-badge badge-empty">REQUIRED</span> Must be entered</small>
        <small><span class="field-badge badge-optional">OPTIONAL</span> Optional field</small>
    </div>

    <form action="{{ route('ndtc.orders.store', $vehicle) }}"
          method="POST"
          id="ndtcOrderForm">
        @csrf

        {{-- Hidden fields --}}
        <input type="hidden" name="vehicle_id"          value="{{ $vehicle->id }}">
        <input type="hidden" name="transaction_type"    value="TNL">
        <input type="hidden" name="vertical_type"       value="NATIONAL_RETAILER">
        <input type="hidden" name="correlation_id"      value="{{ $vehicle->id }}">

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 1: ACQUIRING ENTITY (REQUIRED)                              --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="align-middle mr-2" data-feather="user-check"></i>
                    Acquiring Entity <span class="required-note">*</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="help-text-block">
                    <small class="text-muted">
                        <i class="align-middle mr-1" data-feather="info"></i>
                        The entity acquiring the vehicle. Must have an NRB Number or Entity ID.
                        If the entity is not in the system, contact support to register them.
                    </small>
                </div>

                {{-- Entity ID / NRB Number --}}
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>NRB Number or Entity ID <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('acquiring_entity_id') is-invalid @enderror"
                               name="acquiring_entity_id"
                               value="{{ $val('acquiring_entity_id') }}"
                               placeholder="e.g. 12345 or GUID">
                        @error('acquiring_entity_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-empty">REQUIRED</span>
                            NRB Number (numeric) or Entity ID (GUID)
                        </small>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Entity Name <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('acquiring_entity_name') is-invalid @enderror"
                               name="acquiring_entity_name"
                               value="{{ $val('acquiring_entity_name') }}"
                               placeholder="Full entity name">
                        @error('acquiring_entity_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-manual">VERIFY</span>
                            Must match NRB record
                        </small>
                    </div>
                </div>

                {{-- Physical Address --}}
                <div class="section-label">Physical Address</div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Address Line 1 <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('acquiring_address1') is-invalid @enderror"
                               name="acquiring_address1"
                               value="{{ $val('acquiring_address1') }}"
                               placeholder="Street address">
                        @error('acquiring_address1')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label>Address Line 2 <span class="badge badge-optional">OPTIONAL</span></label>
                        <input type="text"
                               class="form-control"
                               name="acquiring_address2"
                               value="{{ old('acquiring_address2') }}"
                               placeholder="Suite, unit, etc.">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>City <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('acquiring_city') is-invalid @enderror"
                               name="acquiring_city"
                               value="{{ $val('acquiring_city') }}">
                        @error('acquiring_city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>State <span class="required-note">*</span></label>
                        <select class="form-control @error('acquiring_state') is-invalid @enderror"
                                name="acquiring_state">
                            <option value="">-- Select --</option>
                            @foreach(['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'] as $state)
                                <option value="{{ $state }}" {{ $val('acquiring_state') == $state ? 'selected' : '' }}>
                                    {{ $state }}
                                </option>
                            @endforeach
                        </select>
                        @error('acquiring_state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>ZIP Code <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('acquiring_zip') is-invalid @enderror"
                               name="acquiring_zip"
                               value="{{ $val('acquiring_zip') }}"
                               placeholder="5 digits"
                               maxlength="5">
                        @error('acquiring_zip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Mailing Address (Optional) --}}
                <div class="mt-3">
                    <div class="d-flex align-items-center mb-2">
                        <label class="mb-0 mr-3 font-weight-bold">Mailing Address</label>
                        <span class="field-badge badge-optional">OPTIONAL</span>
                        <small class="text-muted ml-2">Check if different from physical address</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Address Line 1</label>
                            <input type="text"
                                   class="form-control"
                                   name="acquiring_mailing_address1"
                                   value="{{ old('acquiring_mailing_address1') }}"
                                   placeholder="Mailing address">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Address Line 2</label>
                            <input type="text"
                                   class="form-control"
                                   name="acquiring_mailing_address2"
                                   value="{{ old('acquiring_mailing_address2') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>City</label>
                            <input type="text"
                                   class="form-control"
                                   name="acquiring_mailing_city"
                                   value="{{ old('acquiring_mailing_city') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>State</label>
                            <select class="form-control" name="acquiring_mailing_state">
                                <option value="">-- Select --</option>
                                @foreach(['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'] as $state)
                                    <option value="{{ $state }}" {{ old('acquiring_mailing_state') == $state ? 'selected' : '' }}>
                                        {{ $state }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>ZIP Code</label>
                            <input type="text"
                                   class="form-control"
                                   name="acquiring_mailing_zip"
                                   value="{{ old('acquiring_mailing_zip') }}"
                                   maxlength="5">
                        </div>
                        <div class="form-group col-md-2">
                            <label>County</label>
                            <input type="text"
                                   class="form-control"
                                   name="acquiring_mailing_county"
                                   value="{{ old('acquiring_mailing_county') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 2: TITLE WORK ENTITY (REQUIRED)                             --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="align-middle mr-2" data-feather="briefcase"></i>
                    Title Work Entity <span class="required-note">*</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="help-text-block">
                    <small class="text-muted">
                        <i class="align-middle mr-1" data-feather="info"></i>
                        The entity performing the title work. Must be registered with CHAMP.
                    </small>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>NRB Number or Entity ID <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('title_work_entity_id') is-invalid @enderror"
                               name="title_work_entity_id"
                               value="{{ $val('title_work_entity_id') }}"
                               placeholder="e.g. 12345 or GUID">
                        @error('title_work_entity_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-empty">REQUIRED</span>
                            NRB Number or Entity ID
                        </small>
                    </div>
                </div>

                {{-- Representative --}}
                <div class="section-label">Representative <span class="required-note">*</span></div>
                <div class="help-text-block">
                    <small class="text-muted">
                        <i class="align-middle mr-1" data-feather="info"></i>
                        <strong>Agent:</strong> Associate of the entity<br>
                        <strong>Power of Attorney:</strong> Authorized to act on behalf of the entity
                    </small>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>First Name <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('representative_first') is-invalid @enderror"
                               name="representative_first"
                               value="{{ $val('representative_first') }}">
                        @error('representative_first')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>Last Name <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('representative_last') is-invalid @enderror"
                               name="representative_last"
                               value="{{ $val('representative_last') }}">
                        @error('representative_last')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>Email <span class="required-note">*</span></label>
                        <input type="email"
                               class="form-control @error('representative_email') is-invalid @enderror"
                               name="representative_email"
                               value="{{ $val('representative_email') }}"
                               placeholder="john@company.com">
                        @error('representative_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>Relationship <span class="required-note">*</span></label>
                        <select class="form-control @error('representative_relationship') is-invalid @enderror"
                                name="representative_relationship">
                            <option value="AGENT" {{ old('representative_relationship') == 'AGENT' ? 'selected' : '' }}>
                                AGENT
                            </option>
                            <option value="POWER_OF_ATTORNEY" {{ old('representative_relationship') == 'POWER_OF_ATTORNEY' ? 'selected' : '' }}>
                                POWER_OF_ATTORNEY
                            </option>
                        </select>
                        @error('representative_relationship')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Phone Number <span class="badge badge-optional">OPTIONAL</span></label>
                        <input type="text"
                               class="form-control"
                               name="representative_phone"
                               value="{{ $val('representative_phone') }}"
                               placeholder="555-555-5555">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Phone Type <span class="badge badge-optional">OPTIONAL</span></label>
                        <select class="form-control" name="representative_phone_type">
                            <option value="MOBILE" {{ old('representative_phone_type') == 'MOBILE' ? 'selected' : '' }}>MOBILE</option>
                            <option value="HOME" {{ old('representative_phone_type') == 'HOME' ? 'selected' : '' }}>HOME</option>
                            <option value="WORK" {{ old('representative_phone_type') == 'WORK' ? 'selected' : '' }}>WORK</option>
                            <option value="OTHER" {{ old('representative_phone_type') == 'OTHER' ? 'selected' : '' }}>OTHER</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 3: VEHICLE INFORMATION (REQUIRED)                            --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="align-middle mr-2" data-feather="truck"></i>
                    Vehicle Information <span class="required-note">*</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="align-middle mr-1" data-feather="alert-circle"></i>
                    Vehicle data is auto-filled from the database. To change any vehicle data,
                    update the vehicle record first. Only the fields marked <span class="field-badge badge-manual">VERIFY</span>
                    can be edited here for exceptional cases.
                </div>

                {{-- VIN (Read-only) --}}
                <div class="form-group row">
                    <label class="col-md-2 col-form-label font-weight-bold">
                        VIN <span class="required-note">*</span>
                    </label>
                    <div class="col-sm-6">
                        <input type="text"
                               class="form-control font-monospace"
                               name="vin"
                               value="{{ $vehicle->vin }}"
                               readonly>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center">
                        <span class="field-badge badge-auto">AUTO (Read-only)</span>
                    </div>
                </div>

                <div class="form-row">
                    {{-- Year (Read-only) --}}
                    <div class="form-group col-md-2">
                        <label>Year <span class="required-note">*</span></label>
                        <input type="number"
                               class="form-control"
                               name="year"
                               value="{{ $parsedYear }}"
                               readonly>
                        <small class="form-text"><span class="field-badge badge-auto">Read-only</span></small>
                    </div>

                    {{-- Make (Read-only) --}}
                    <div class="form-group col-md-3">
                        <label>Make (NCIC) <span class="required-note">*</span></label>
                        <select class="form-control" name="make" disabled>
                            <option value="{{ $parsedMake }}" selected>{{ $parsedMake }}</option>
                        </select>
                        <input type="hidden" name="make" value="{{ $parsedMake }}">
                        <small class="form-text"><span class="field-badge badge-auto">Read-only</span></small>
                    </div>

                    {{-- Model (Read-only) --}}
                    <div class="form-group col-md-4">
                        <label>Model <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control"
                               name="model"
                               value="{{ $parsedModel }}"
                               readonly>
                        <small class="form-text"><span class="field-badge badge-auto">Read-only</span></small>
                    </div>

                    {{-- Vehicle Class --}}
                    <div class="form-group col-md-3">
                        <label>Vehicle Class <span class="required-note">*</span></label>
                        <select class="form-control @error('vehicle_class') is-invalid @enderror"
                                name="vehicle_class">
                            <option value="CARS_AND_TRUCKS" {{ old('vehicle_class', 'CARS_AND_TRUCKS') == 'CARS_AND_TRUCKS' ? 'selected' : '' }}>Cars &amp; Trucks</option>
                            <option value="TRUCKS" {{ old('vehicle_class') == 'TRUCKS' ? 'selected' : '' }}>Trucks</option>
                            <option value="BUSES" {{ old('vehicle_class') == 'BUSES' ? 'selected' : '' }}>Buses</option>
                            <option value="TRAILERS_AND_SEMI_TRAILERS" {{ old('vehicle_class') == 'TRAILERS_AND_SEMI_TRAILERS' ? 'selected' : '' }}>Trailers &amp; Semi-Trailers</option>
                            <option value="TRAVEL_TRAILERS" {{ old('vehicle_class') == 'TRAVEL_TRAILERS' ? 'selected' : '' }}>Travel Trailers</option>
                            <option value="TRAILERS" {{ old('vehicle_class') == 'TRAILERS' ? 'selected' : '' }}>Trailers</option>
                            <option value="ATV" {{ old('vehicle_class') == 'ATV' ? 'selected' : '' }}>ATV</option>
                            <option value="ANTIQUE_MOTOR" {{ old('vehicle_class') == 'ANTIQUE_MOTOR' ? 'selected' : '' }}>Antique Motor</option>
                            <option value="MOBILE_HOME" {{ old('vehicle_class') == 'MOBILE_HOME' ? 'selected' : '' }}>Mobile Home</option>
                            <option value="FARM_TRUCKS" {{ old('vehicle_class') == 'FARM_TRUCKS' ? 'selected' : '' }}>Farm Trucks</option>
                            <option value="MOBILE_EQUIPMENT" {{ old('vehicle_class') == 'MOBILE_EQUIPMENT' ? 'selected' : '' }}>Mobile Equipment</option>
                            <option value="MOTORCYCLES" {{ old('vehicle_class') == 'MOTORCYCLES' ? 'selected' : '' }}>Motorcycles</option>
                            <option value="TAXI_CABS" {{ old('vehicle_class') == 'TAXI_CABS' ? 'selected' : '' }}>Taxi Cabs</option>
                            <option value="PUBLIC_SERVICE" {{ old('vehicle_class') == 'PUBLIC_SERVICE' ? 'selected' : '' }}>Public Service</option>
                            <option value="COUNTY_GOVERNMENT" {{ old('vehicle_class') == 'COUNTY_GOVERNMENT' ? 'selected' : '' }}>County Government</option>
                            <option value="STATE_GOVERNMENT" {{ old('vehicle_class') == 'STATE_GOVERNMENT' ? 'selected' : '' }}>State Government</option>
                            <option value="CITY_GOVERNMENT" {{ old('vehicle_class') == 'CITY_GOVERNMENT' ? 'selected' : '' }}>City Government</option>
                        </select>
                        @error('vehicle_class')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text"><span class="field-badge badge-manual">VERIFY</span> Select appropriate class</small>
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
                                <option value="SD" {{ $val('body_style') == 'SD' ? 'selected' : '' }}>SD — Sedan</option>
                                <option value="4W" {{ $val('body_style') == '4W' ? 'selected' : '' }}>4W — SUV / 4DR Wagon</option>
                                <option value="UT" {{ $val('body_style') == 'UT' ? 'selected' : '' }}>UT — 2DR Sport Utility</option>
                                <option value="CP" {{ $val('body_style') == 'CP' ? 'selected' : '' }}>CP — Coupe</option>
                                <option value="CV" {{ $val('body_style') == 'CV' ? 'selected' : '' }}>CV — Convertible</option>
                                <option value="HB" {{ $val('body_style') == 'HB' ? 'selected' : '' }}>HB — Hatchback</option>
                                <option value="SW" {{ $val('body_style') == 'SW' ? 'selected' : '' }}>SW — Station Wagon</option>
                                <option value="PK" {{ $val('body_style') == 'PK' ? 'selected' : '' }}>PK — Pickup</option>
                                <option value="PV" {{ $val('body_style') == 'PV' ? 'selected' : '' }}>PV — Passenger Van</option>
                                <option value="CG" {{ $val('body_style') == 'CG' ? 'selected' : '' }}>CG — Cargo Van</option>
                                <option value="TK" {{ $val('body_style') == 'TK' ? 'selected' : '' }}>TK — Truck</option>
                            </optgroup>
                            <optgroup label="Other">
                                <option value="2D" {{ $val('body_style') == '2D' ? 'selected' : '' }}>2D — 2 Door Sedan</option>
                                <option value="2H" {{ $val('body_style') == '2H' ? 'selected' : '' }}>2H — Hatchback 2DR</option>
                                <option value="2T" {{ $val('body_style') == '2T' ? 'selected' : '' }}>2T — Hardtop 2DR</option>
                                <option value="2W" {{ $val('body_style') == '2W' ? 'selected' : '' }}>2W — Wagon 2DR</option>
                                <option value="3C" {{ $val('body_style') == '3C' ? 'selected' : '' }}>3C — Extended Cab</option>
                                <option value="4C" {{ $val('body_style') == '4C' ? 'selected' : '' }}>4C — 4DR Ext Cab</option>
                                <option value="4T" {{ $val('body_style') == '4T' ? 'selected' : '' }}>4T — Hardtop 4DR</option>
                                <option value="MC" {{ $val('body_style') == 'MC' ? 'selected' : '' }}>MC — Motorcycle</option>
                                <option value="MH" {{ $val('body_style') == 'MH' ? 'selected' : '' }}>MH — Motor Home</option>
                                <option value="TL" {{ $val('body_style') == 'TL' ? 'selected' : '' }}>TL — Trailer</option>
                            </optgroup>
                        </select>
                        @error('body_style')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text"><span class="field-badge badge-manual">VERIFY</span> From vehicle data</small>
                    </div>

                    {{-- Fuel Type --}}
                    <div class="form-group col-md-3">
                        <label>Fuel Type <span class="required-note">*</span></label>
                        <select class="form-control @error('fuel_type') is-invalid @enderror"
                                name="fuel_type">
                            <option value="GAS" {{ old('fuel_type', 'GAS') == 'GAS' ? 'selected' : '' }}>GAS — Gasoline</option>
                            <option value="DIESEL" {{ old('fuel_type') == 'DIESEL' ? 'selected' : '' }}>DIESEL — Diesel</option>
                            <option value="ELECTRIC" {{ old('fuel_type') == 'ELECTRIC' ? 'selected' : '' }}>ELECTRIC — Electric</option>
                            <option value="ELECTRIC_AND_GAS_HYBRID" {{ old('fuel_type') == 'ELECTRIC_AND_GAS_HYBRID' ? 'selected' : '' }}>HYBRID — Electric &amp; Gas</option>
                            <option value="ELECTRIC_AND_DIESEL_HYBRID" {{ old('fuel_type') == 'ELECTRIC_AND_DIESEL_HYBRID' ? 'selected' : '' }}>HYBRID — Electric &amp; Diesel</option>
                            <option value="FLEXIBLE" {{ old('fuel_type') == 'FLEXIBLE' ? 'selected' : '' }}>FLEXIBLE — Flex Fuel</option>
                            <option value="ETHANOL" {{ old('fuel_type') == 'ETHANOL' ? 'selected' : '' }}>ETHANOL</option>
                            <option value="PROPANE" {{ old('fuel_type') == 'PROPANE' ? 'selected' : '' }}>PROPANE</option>
                            <option value="COMPRESSED_NATURAL_GAS" {{ old('fuel_type') == 'COMPRESSED_NATURAL_GAS' ? 'selected' : '' }}>CNG</option>
                            <option value="LIQUID_NATURAL_GAS" {{ old('fuel_type') == 'LIQUID_NATURAL_GAS' ? 'selected' : '' }}>LNG</option>
                            <option value="HYDROGEN_FUEL_CELL" {{ old('fuel_type') == 'HYDROGEN_FUEL_CELL' ? 'selected' : '' }}>HYDROGEN</option>
                            <option value="UNKNOWN" {{ old('fuel_type') == 'UNKNOWN' ? 'selected' : '' }}>UNKNOWN</option>
                        </select>
                        @error('fuel_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text"><span class="field-badge badge-manual">VERIFY</span> From vehicle data</small>
                    </div>

                    {{-- Weight --}}
                    <div class="form-group col-md-3">
                        <label>Vehicle Weight (LBS) <span class="required-note">*</span></label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control @error('weight') is-invalid @enderror"
                                   name="weight"
                                   value="{{ $val('weight') }}"
                                   placeholder="e.g. 4200"
                                   min="100" max="99999">
                            <div class="input-group-append">
                                <span class="input-group-text">LBS</span>
                            </div>
                            @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text"><span class="field-badge badge-manual">VERIFY</span> From vehicle data</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 4: EXISTING TITLE (REQUIRED)                                --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="align-middle mr-2" data-feather="file-text"></i>
                    Existing Title Information <span class="required-note">*</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="align-middle mr-1" data-feather="alert-triangle"></i>
                    <strong>Important:</strong> The title number must exactly match what is in NMVTIS.
                    Verify against the physical paper title in hand.
                </div>

                <div class="form-row">
                    {{-- Title Number --}}
                    <div class="form-group col-md-4">
                        <label>Title Number <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('title_number') is-invalid @enderror"
                               name="title_number"
                               value="{{ $val('title_number') }}"
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
                                <option value="{{ $state }}" {{ $val('issuing_state', $titleState) == $state ? 'selected' : '' }}>
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
                            <option value="PAPER" {{ old('title_type', $titleType) == 'PAPER' ? 'selected' : '' }}>
                                PAPER
                            </option>
                            <option value="DIGITAL" {{ old('title_type', $titleType) == 'DIGITAL' ? 'selected' : '' }}>
                                DIGITAL
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

                    {{-- Control Number (Optional) --}}
                    <div class="form-group col-md-3">
                        <label>Control Number <span class="badge badge-optional">OPTIONAL</span></label>
                        <input type="text"
                               class="form-control"
                               name="control_number"
                               value="{{ old('control_number') }}"
                               placeholder="Required if issuing state records one">
                        <small class="form-text text-muted">Required in some states</small>
                    </div>
                </div>

                {{-- Title Brands --}}
                <div class="section-label">Title Brands <span class="badge badge-optional">OPTIONAL</span></div>
                <p class="text-muted small mb-2">
                    Vehicles from Copart / IAAI typically carry a Salvage brand.
                    Verify against the physical title and check all that apply.
                </p>
                <div class="form-row">
                    @foreach([
                        'SALVAGE'  => 'SALVAGE',
                        'JUNK'     => 'JUNK',
                        'REBUILT'  => 'REBUILT',
                        'FLOOD'    => 'FLOOD',
                        'FIRE'     => 'FIRE',
                        'LEMON'    => 'LEMON',
                        'WATER_DAMAGE' => 'WATER_DAMAGE',
                        'DISMANTLED' => 'DISMANTLED',
                        'RECONSTRUCTED' => 'RECONSTRUCTED',
                        'UNRECOVERED_THEFT' => 'UNRECOVERED_THEFT',
                        'EXPORT_ONLY' => 'EXPORT_ONLY',
                        'OWNER_RETAINED' => 'OWNER_RETAINED',
                        'MANUFACTURER_BUY_BACK' => 'MANUFACTURER_BUY_BACK',
                        'REPAIRED' => 'REPAIRED',
                        'CRUSHED' => 'CRUSHED',
                    ] as $value => $label)
                        <div class="col-md-3 mb-2">
                            <label class="brand-check" id="brand-label-{{ strtolower($value) }}">
                                <input type="checkbox"
                                       name="title_brands[]"
                                       value="{{ $value }}"
                                       class="brand-checkbox"
                                       {{ old('title_brands') && in_array($value, old('title_brands')) ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>

                {{-- Liens (Optional) --}}
                <div class="section-label">Liens <span class="badge badge-optional">OPTIONAL</span></div>
                <div class="help-text-block">
                    <small class="text-muted">
                        <i class="align-middle mr-1" data-feather="info"></i>
                        For Transfer No Lien (TNL), the title should typically have no liens,
                        but include them if the vehicle has an existing lien.
                    </small>
                </div>

                <div id="liens-container">
                    <div class="lien-entry border p-3 mb-2 rounded">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Lienholder Name</label>
                                <input type="text"
                                       class="form-control"
                                       name="liens[0][name]"
                                       value="{{ old('liens.0.name') }}"
                                       placeholder="e.g. Acme Bank">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Lien Type</label>
                                <select class="form-control" name="liens[0][type]">
                                    <option value="NON_ELECTRONIC" {{ old('liens.0.type') == 'NON_ELECTRONIC' ? 'selected' : '' }}>NON_ELECTRONIC</option>
                                    <option value="ELECTRONIC" {{ old('liens.0.type') == 'ELECTRONIC' ? 'selected' : '' }}>ELECTRONIC</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Issue Date</label>
                                <input type="date"
                                       class="form-control"
                                       name="liens[0][issue_date]"
                                       value="{{ old('liens.0.issue_date') }}">
                            </div>
                            <div class="form-group col-md-2">
                                <label>Discharge Date</label>
                                <input type="date"
                                       class="form-control"
                                       name="liens[0][discharge_date]"
                                       value="{{ old('liens.0.discharge_date') }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Lienholder Address</label>
                                <input type="text"
                                       class="form-control"
                                       name="liens[0][address1]"
                                       value="{{ old('liens.0.address1') }}"
                                       placeholder="Street address">
                            </div>
                            <div class="form-group col-md-2">
                                <label>City</label>
                                <input type="text"
                                       class="form-control"
                                       name="liens[0][city]"
                                       value="{{ old('liens.0.city') }}">
                            </div>
                            <div class="form-group col-md-2">
                                <label>State</label>
                                <input type="text"
                                       class="form-control"
                                       name="liens[0][state]"
                                       value="{{ old('liens.0.state') }}"
                                       placeholder="OH">
                            </div>
                            <div class="form-group col-md-2">
                                <label>ZIP</label>
                                <input type="text"
                                       class="form-control"
                                       name="liens[0][zip]"
                                       value="{{ old('liens.0.zip') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addLienEntry()">
                    <i class="align-middle mr-1" data-feather="plus"></i>
                    Add Another Lien
                </button>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 5: ODOMETER (REQUIRED)                                      --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="align-middle mr-2" data-feather="activity"></i>
                    Odometer <span class="required-note">*</span>
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
                                   value="{{ $val('odometer_reading', $odometer) }}"
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
                            <option value="ACTUAL" {{ old('odometer_condition') == 'ACTUAL' ? 'selected' : '' }}>ACTUAL — Actual mileage</option>
                            <option value="NOT_ACTUAL" {{ old('odometer_condition') == 'NOT_ACTUAL' ? 'selected' : '' }}>NOT_ACTUAL — Exceeds limits</option>
                            <option value="EXEMPT" {{ old('odometer_condition') == 'EXEMPT' ? 'selected' : '' }}>EXEMPT — Vehicle is exempt</option>
                            <option value="NO_ODOMETER" {{ old('odometer_condition') == 'NO_ODOMETER' ? 'selected' : '' }}>NO_ODOMETER — No odometer</option>
                        </select>
                        @error('odometer_condition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Date --}}
                    <div class="form-group col-md-3">
                        <label>Odometer Date <span class="required-note">*</span></label>
                        <input type="date"
                               class="form-control @error('odometer_date') is-invalid @enderror"
                               name="odometer_date"
                               value="{{ $val('odometer_date', $saleDate) }}">
                        @error('odometer_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-auto">AUTO</span>
                            From <code>sale_date</code> meta
                        </small>
                    </div>

                    {{-- Unit --}}
                    <div class="form-group col-md-3">
                        <label>Unit <span class="required-note">*</span></label>
                        <select class="form-control @error('odometer_unit') is-invalid @enderror"
                                name="odometer_unit">
                            <option value="MI" {{ old('odometer_unit', 'MI') == 'MI' ? 'selected' : '' }}>MI — Miles</option>
                            <option value="KM" {{ old('odometer_unit') == 'KM' ? 'selected' : '' }}>KM — Kilometers</option>
                        </select>
                        @error('odometer_unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 6: DISPOSING ENTITY (AUCTION) (REQUIRED)                    --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="align-middle mr-2" data-feather="building"></i>
                    Disposing Entity (Auction) <span class="required-note">*</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-secondary small">
                    <i class="align-middle mr-1" data-feather="info"></i>
                    Auction companies do not need to be pre-registered with CHAMP.
                    They can be submitted ad-hoc on each order. Enter the auction branch address.
                </div>

                <div class="form-row">
                    {{-- Entity Type (Hidden, default: company) --}}
                    <input type="hidden" name="disposing_type" value="company">

                    {{-- Name --}}
                    <div class="form-group col-md-4">
                        <label>Entity Name <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('disposing_name') is-invalid @enderror"
                               name="disposing_name"
                               value="{{ $val('disposing_name', $auctionSource) }}"
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
                               value="{{ $val('disposing_address1') }}"
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
                        <label>Address Line 2 <span class="badge badge-optional">OPTIONAL</span></label>
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
                               value="{{ $val('disposing_city') }}">
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
                                <option value="{{ $state }}" {{ $val('disposing_state') == $state ? 'selected' : '' }}>
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
                               value="{{ $val('disposing_zip') }}"
                               placeholder="00000"
                               maxlength="10">
                        @error('disposing_zip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- County --}}
                    <div class="form-group col-md-3">
                        <label>County <span class="badge badge-optional">OPTIONAL</span></label>
                        <input type="text"
                               class="form-control"
                               name="disposing_county"
                               value="{{ old('disposing_county') }}"
                               placeholder="Optional">
                    </div>
                </div>

                {{-- Phone (Optional) --}}
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Phone Number <span class="badge badge-optional">OPTIONAL</span></label>
                        <input type="text"
                               class="form-control"
                               name="disposing_phone"
                               value="{{ old('disposing_phone') }}"
                               placeholder="555-555-5555">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Phone Type <span class="badge badge-optional">OPTIONAL</span></label>
                        <select class="form-control" name="disposing_phone_type">
                            <option value="MOBILE" {{ old('disposing_phone_type') == 'MOBILE' ? 'selected' : '' }}>MOBILE</option>
                            <option value="HOME" {{ old('disposing_phone_type') == 'HOME' ? 'selected' : '' }}>HOME</option>
                            <option value="WORK" {{ old('disposing_phone_type') == 'WORK' ? 'selected' : '' }}>WORK</option>
                            <option value="OTHER" {{ old('disposing_phone_type') == 'OTHER' ? 'selected' : '' }}>OTHER</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 7: TRANSFER DETAILS (REQUIRED)                              --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
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
                               value="{{ $val('transfer_date', $saleDate) }}">
                        @error('transfer_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-auto">AUTO</span>
                            From <code>sale_date</code> meta
                        </small>
                    </div>

                    {{-- Requested Title Type --}}
                    <div class="form-group col-md-3">
                        <label>Requested Title Type <span class="required-note">*</span></label>
                        <select class="form-control @error('requested_title_type') is-invalid @enderror"
                                name="requested_title_type">
                            <option value="PAPER" {{ old('requested_title_type', 'PAPER') == 'PAPER' ? 'selected' : '' }}>
                                PAPER
                            </option>
                            <option value="DIGITAL" {{ old('requested_title_type') == 'DIGITAL' ? 'selected' : '' }}>
                                DIGITAL
                            </option>
                        </select>
                        @error('requested_title_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-manual">VERIFY</span>
                            Select the title type to request
                        </small>
                    </div>

                    {{-- Internal Reference --}}
                    <div class="form-group col-md-4">
                        <label>Internal Reference (Correlation ID)</label>
                        <input type="text"
                               class="form-control font-monospace"
                               value="{{ $vehicle->purchase_lot ?? $vehicle->auction_lot ?? $vehicle->id }}"
                               readonly>
                        <small class="form-text">
                            <span class="field-badge badge-auto">AUTO</span>
                            Returned in every NDTC webhook for tracking
                        </small>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="form-group">
                    <label>Declaration / Notes <span class="badge badge-optional">OPTIONAL</span></label>
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

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- ACTIONS                                                             --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
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

// ── LIEN DYNAMIC ADD ──────────────────────────────────────────
let lienIndex = 1;

function addLienEntry() {
    const container = document.getElementById('liens-container');
    const entry = document.createElement('div');
    entry.className = 'lien-entry border p-3 mb-2 rounded';
    entry.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Lien #${lienIndex + 1}</strong>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.lien-entry').remove()">
                <i class="align-middle mr-1" data-feather="x"></i> Remove
            </button>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Lienholder Name</label>
                <input type="text" class="form-control" name="liens[${lienIndex}][name]" placeholder="e.g. Acme Bank">
            </div>
            <div class="form-group col-md-3">
                <label>Lien Type</label>
                <select class="form-control" name="liens[${lienIndex}][type]">
                    <option value="NON_ELECTRONIC">NON_ELECTRONIC</option>
                    <option value="ELECTRONIC">ELECTRONIC</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label>Issue Date</label>
                <input type="date" class="form-control" name="liens[${lienIndex}][issue_date]">
            </div>
            <div class="form-group col-md-2">
                <label>Discharge Date</label>
                <input type="date" class="form-control" name="liens[${lienIndex}][discharge_date]">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Lienholder Address</label>
                <input type="text" class="form-control" name="liens[${lienIndex}][address1]" placeholder="Street address">
            </div>
            <div class="form-group col-md-2">
                <label>City</label>
                <input type="text" class="form-control" name="liens[${lienIndex}][city]">
            </div>
            <div class="form-group col-md-2">
                <label>State</label>
                <input type="text" class="form-control" name="liens[${lienIndex}][state]" placeholder="OH">
            </div>
            <div class="form-group col-md-2">
                <label>ZIP</label>
                <input type="text" class="form-control" name="liens[${lienIndex}][zip]">
            </div>
        </div>
    `;
    container.appendChild(entry);
    lienIndex++;
}
</script>
@endsection
