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
                        <label>Title Type <span class="required-note"></span></label>
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
                        'SALVAGE'                => 'SALVAGE',
                        'JUNK'                   => 'JUNK',
                        'TOTALED'                => 'TOTALED',
                        'LEMON'                  => 'LEMON',
                        'FLOOD'                  => 'FLOOD',
                        'FIRE'                   => 'FIRE',
                        'WATER_DAMAGE'           => 'WATER_DAMAGE',
                        'DISMANTLED'             => 'DISMANTLED',
                        'REBUILT'                => 'REBUILT',
                        'WARRANTY_RETURN'        => 'WARRANTY_RETURN',
                        'STREET_ROD'             => 'STREET_ROD',
                        'REPLICA'                => 'REPLICA',
                        'OWNER_RETAINED'         => 'OWNER_RETAINED',
                        'MEMORANDUM_COPY'        => 'MEMORANDUM_COPY',
                        'VIN_REPLACED'           => 'VIN_REPLACED',
                        'GRAY_MARKET'            => 'GRAY_MARKET',
                        'MANUFACTURER_BUY_BACK'  => 'MANUFACTURER_BUY_BACK',
                        'REPAIRED'               => 'REPAIRED',
                        'CRUSHED'                => 'CRUSHED',
                        'EXPORT_ONLY'            => 'EXPORT_ONLY',
                        'RECONSTRUCTED'          => 'RECONSTRUCTED',
                        'UNRECOVERED_THEFT'      => 'UNRECOVERED_THEFT',
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

            <div class="card" id="noTitleReasonBlock"
                style="display: {{ in_array($val('ndtc_transaction_type', 'TNL'), ['DNT','EOL','RWUT','RWOT']) ? 'block' : 'none' }};">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="align-middle mr-2" data-feather="alert-circle"></i>
                        No Title Image Reason <span class="required-note">*</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Reason title image was not uploaded <span class="required-note">*</span></label>
                        <textarea class="form-control @error('no_title_image_reason') is-invalid @enderror"
                                name="no_title_image_reason"
                                rows="2"
                                maxlength="790"
                                placeholder="e.g. Title not in possession">{{ $val('no_title_image_reason') }}</textarea>
                        @error('no_title_image_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
