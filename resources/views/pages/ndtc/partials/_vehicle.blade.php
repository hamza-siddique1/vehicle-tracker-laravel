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
                            <option value="METHANOL" {{ old('fuel_type') == 'METHANOL' ? 'selected' : '' }}>METHANOL</option>
                            <option value="PROPANE" {{ old('fuel_type') == 'PROPANE' ? 'selected' : '' }}>PROPANE</option>
                            <option value="COMPRESSED_NATURAL_GAS" {{ old('fuel_type') == 'COMPRESSED_NATURAL_GAS' ? 'selected' : '' }}>CNG — Compressed Natural Gas</option>
                            <option value="LIQUID_NATURAL_GAS" {{ old('fuel_type') == 'LIQUID_NATURAL_GAS' ? 'selected' : '' }}>LNG — Liquid Natural Gas</option>
                            <option value="HYDROGEN_FUEL_CELL" {{ old('fuel_type') == 'HYDROGEN_FUEL_CELL' ? 'selected' : '' }}>HYDROGEN — Fuel Cell</option>
                            <option value="CONVERTIBLE" {{ old('fuel_type') == 'CONVERTIBLE' ? 'selected' : '' }}>CONVERTIBLE</option>
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
