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
