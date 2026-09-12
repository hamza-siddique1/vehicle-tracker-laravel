{{-- _odometer.blade.php --}}
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
                           value="{{ (int) round((float) $val('odometer_reading', $odometer)) }}"
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
                @php $odoCondition = $val('odometer_condition', 'ACTUAL'); @endphp
                <select class="form-control @error('odometer_condition') is-invalid @enderror"
                        name="odometer_condition">
                    <option value="ACTUAL" {{ $odoCondition == 'ACTUAL' ? 'selected' : '' }}>ACTUAL — Actual mileage</option>
                    <option value="NOT_ACTUAL" {{ $odoCondition == 'NOT_ACTUAL' ? 'selected' : '' }}>NOT_ACTUAL — Exceeds limits</option>
                    <option value="EXEMPT" {{ $odoCondition == 'EXEMPT' ? 'selected' : '' }}>EXEMPT — Vehicle is exempt</option>
                    <option value="EXCEEDS_MECHANICAL_LIMIT" {{ $odoCondition == 'EXCEEDS_MECHANICAL_LIMIT' ? 'selected' : '' }}>EXCEEDS_MECHANICAL_LIMIT</option>
                    <option value="NO_ODOMETER" {{ $odoCondition == 'NO_ODOMETER' ? 'selected' : '' }}>NO_ODOMETER — No odometer</option>
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
                @php $odoUnit = $val('odometer_unit', 'MI'); @endphp
                <select class="form-control @error('odometer_unit') is-invalid @enderror"
                        name="odometer_unit">
                    <option value="MI" {{ $odoUnit == 'MI' ? 'selected' : '' }}>MI — Miles</option>
                    <option value="KM" {{ $odoUnit == 'KM' ? 'selected' : '' }}>KM — Kilometers</option>
                </select>
                @error('odometer_unit')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>
