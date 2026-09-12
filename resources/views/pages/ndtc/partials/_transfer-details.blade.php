{{-- _transfer-details.blade.php --}}
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
                @php $requestedTitleType = $val('requested_title_type', 'PAPER'); @endphp
                <select class="form-control @error('requested_title_type') is-invalid @enderror"
                        name="requested_title_type">
                    <option value="PAPER" {{ $requestedTitleType == 'PAPER' ? 'selected' : '' }}>
                        PAPER
                    </option>
                    <option value="DIGITAL" {{ $requestedTitleType == 'DIGITAL' ? 'selected' : '' }}>
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
                       value="{{ $order->order_payload['correlationId'] ?? ($vehicle->purchase_lot ?? $vehicle->auction_lot ?? $vehicle->id) }}"
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
                      placeholder="Any notes for the DMV reviewer — use for unusual cases only">{{ $val('notes') }}</textarea>
            <small class="form-text text-muted">
                Submitted as a DECLARATION_PAGE document if filled in
            </small>
        </div>
    </div>
</div>
