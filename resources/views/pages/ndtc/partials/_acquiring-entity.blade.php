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
                        <label>NRB Number <span class="required-note">*</span></label>
                        <input type="text"
                               class="form-control @error('acquiring_entity_id') is-invalid @enderror"
                               name="acquiring_entity_id"
                               value="{{ $val('acquiring_entity_id') }}"
                               placeholder="e.g. 12345">
                        @error('acquiring_entity_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text">
                            <span class="field-badge badge-empty">REQUIRED</span>
                            NRB Number (numeric)
                        </small>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Entity Name <span class="required-note"></span></label>
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
                        <label>Address Line 1 <span class="required-note"></span></label>
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
                        <label>City <span class="required-note"></span></label>
                        <input type="text"
                               class="form-control @error('acquiring_city') is-invalid @enderror"
                               name="acquiring_city"
                               value="{{ $val('acquiring_city') }}">
                        @error('acquiring_city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>State <span class="required-note"></span></label>
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
                        <label>ZIP Code <span class="required-note"></span></label>
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
