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
