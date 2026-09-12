{{-- _vehicle.blade.php --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">
            <i class="align-middle mr-2" data-feather="truck"></i>
            Vehicle Information <span class="required-note">*</span>
        </h5>
        <a href="#"
           data-toggle="modal"
           data-target="#modal-vehicle-detail"
           data-id="{{ $vehicle->id }}"
           class="btn btn-sm btn-outline-success">
            <i class="align-middle mr-1" data-feather="edit-2"></i>Edit Vehicle Data
        </a>
    </div>
    <div class="card-body">
        @if(empty($order))
            <div class="alert alert-info">
                <i class="align-middle mr-1" data-feather="alert-circle"></i>
                Vehicle data is auto-filled from the database. To change any vehicle data,
                update the vehicle record first. Only the fields marked <span class="field-badge badge-manual">VERIFY</span>
                can be edited here for exceptional cases.
            </div>
        @else
            <div class="alert alert-warning">
                <i class="align-middle mr-1" data-feather="alert-triangle"></i>
                Reviewing data from the last submitted order. Correct any fields flagged by NDTC and resubmit.
            </div>
        @endif

        {{-- VIN (Read-only) --}}
        <div class="form-group col-md" style="padding-left: 1rem;">
            <label class="font-weight-bold">
                VIN <span class="required-note">*</span>
            </label>
            <input type="text"
                class="form-control font-monospace"
                name="vin"
                value="{{ $vehicle->vin }}"
                readonly>
            <small class="form-text"><span class="field-badge badge-auto">AUTO (Read-only)</span></small>
        </div>

        <div class="form-row">
            {{-- Year --}}
            <div class="form-group col-md-2">
                <label>Year <span class="required-note">*</span></label>
                <input type="number"
                       class="form-control"
                       name="year"
                       value="{{ $val('year', $parsedYear) }}">
                <small class="form-text"><span class="field-badge badge-manual">VERIFY</span> From vehicle data</small>
            </div>

            {{-- Make --}}
            <div class="form-group col-md-3">
                <label>Make (NCIC) <span class="required-note">*</span></label>
                <input type="text"
                    class="form-control"
                    name="make"
                    value="{{ $val('make', $parsedMake) }}">
                <small class="form-text"><span class="field-badge badge-manual">VERIFY</span> From vehicle data</small>
            </div>

            {{-- Model --}}
            <div class="form-group col-md-4">
                <label>Model <span class="required-note">*</span></label>
                <input type="text"
                       class="form-control"
                       name="model"
                       value="{{ $val('model', $parsedModel) }}">
                <small class="form-text"><span class="field-badge badge-manual">VERIFY</span> From vehicle data</small>
            </div>

            {{-- Vehicle Class --}}
            <div class="form-group col-md-3">
                <label>Vehicle Class <span class="required-note">*</span></label>
                <select class="form-control @error('vehicle_class') is-invalid @enderror"
                        name="vehicle_class">
                    @php $vehicleClass = $val('vehicle_class', 'CARS_AND_TRUCKS'); @endphp
                    <option value="CARS_AND_TRUCKS" {{ $vehicleClass == 'CARS_AND_TRUCKS' ? 'selected' : '' }}>Cars &amp; Trucks</option>
                    <option value="TRUCKS" {{ $vehicleClass == 'TRUCKS' ? 'selected' : '' }}>Trucks</option>
                    <option value="BUSES" {{ $vehicleClass == 'BUSES' ? 'selected' : '' }}>Buses</option>
                    <option value="TRAILERS_AND_SEMI_TRAILERS" {{ $vehicleClass == 'TRAILERS_AND_SEMI_TRAILERS' ? 'selected' : '' }}>Trailers &amp; Semi-Trailers</option>
                    <option value="TRAVEL_TRAILERS" {{ $vehicleClass == 'TRAVEL_TRAILERS' ? 'selected' : '' }}>Travel Trailers</option>
                    <option value="TRAILERS" {{ $vehicleClass == 'TRAILERS' ? 'selected' : '' }}>Trailers</option>
                    <option value="ATV" {{ $vehicleClass == 'ATV' ? 'selected' : '' }}>ATV</option>
                    <option value="ANTIQUE_MOTOR" {{ $vehicleClass == 'ANTIQUE_MOTOR' ? 'selected' : '' }}>Antique Motor</option>
                    <option value="MOBILE_HOME" {{ $vehicleClass == 'MOBILE_HOME' ? 'selected' : '' }}>Mobile Home</option>
                    <option value="FARM_TRUCKS" {{ $vehicleClass == 'FARM_TRUCKS' ? 'selected' : '' }}>Farm Trucks</option>
                    <option value="MOBILE_EQUIPMENT" {{ $vehicleClass == 'MOBILE_EQUIPMENT' ? 'selected' : '' }}>Mobile Equipment</option>
                    <option value="MOTORCYCLES" {{ $vehicleClass == 'MOTORCYCLES' ? 'selected' : '' }}>Motorcycles</option>
                    <option value="TAXI_CABS" {{ $vehicleClass == 'TAXI_CABS' ? 'selected' : '' }}>Taxi Cabs</option>
                    <option value="PUBLIC_SERVICE" {{ $vehicleClass == 'PUBLIC_SERVICE' ? 'selected' : '' }}>Public Service</option>
                    <option value="COUNTY_GOVERNMENT" {{ $vehicleClass == 'COUNTY_GOVERNMENT' ? 'selected' : '' }}>County Government</option>
                    <option value="STATE_GOVERNMENT" {{ $vehicleClass == 'STATE_GOVERNMENT' ? 'selected' : '' }}>State Government</option>
                    <option value="CITY_GOVERNMENT" {{ $vehicleClass == 'CITY_GOVERNMENT' ? 'selected' : '' }}>City Government</option>
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
                    @php $fuelType = $val('fuel_type', 'GAS'); @endphp
                    <option value="GAS" {{ $fuelType == 'GAS' ? 'selected' : '' }}>GAS — Gasoline</option>
                    <option value="DIESEL" {{ $fuelType == 'DIESEL' ? 'selected' : '' }}>DIESEL — Diesel</option>
                    <option value="ELECTRIC" {{ $fuelType == 'ELECTRIC' ? 'selected' : '' }}>ELECTRIC — Electric</option>
                    <option value="ELECTRIC_AND_GAS_HYBRID" {{ $fuelType == 'ELECTRIC_AND_GAS_HYBRID' ? 'selected' : '' }}>HYBRID — Electric &amp; Gas</option>
                    <option value="ELECTRIC_AND_DIESEL_HYBRID" {{ $fuelType == 'ELECTRIC_AND_DIESEL_HYBRID' ? 'selected' : '' }}>HYBRID — Electric &amp; Diesel</option>
                    <option value="FLEXIBLE" {{ $fuelType == 'FLEXIBLE' ? 'selected' : '' }}>FLEXIBLE — Flex Fuel</option>
                    <option value="ETHANOL" {{ $fuelType == 'ETHANOL' ? 'selected' : '' }}>ETHANOL</option>
                    <option value="PROPANE" {{ $fuelType == 'PROPANE' ? 'selected' : '' }}>PROPANE</option>
                    <option value="COMPRESSED_NATURAL_GAS" {{ $fuelType == 'COMPRESSED_NATURAL_GAS' ? 'selected' : '' }}>CNG</option>
                    <option value="LIQUID_NATURAL_GAS" {{ $fuelType == 'LIQUID_NATURAL_GAS' ? 'selected' : '' }}>LNG</option>
                    <option value="HYDROGEN_FUEL_CELL" {{ $fuelType == 'HYDROGEN_FUEL_CELL' ? 'selected' : '' }}>HYDROGEN</option>
                    <option value="UNKNOWN" {{ $fuelType == 'UNKNOWN' ? 'selected' : '' }}>UNKNOWN</option>
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
