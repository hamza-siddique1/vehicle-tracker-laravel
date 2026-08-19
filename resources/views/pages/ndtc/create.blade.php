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
            transition: border-color .15s, background-color .15s;
        }
        .brand-check:hover      { border-color: #3b7ddd; }
        .brand-check.is-checked {
            border-color: #3b7ddd;
            background: #eef4fd;
        }

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

        .help-text-block {
            background: #f8f9fa;
            border: 3px solid #3b7ddd;
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
    <h1 class="h3 mb-3" id="pageTitle">Create NDTC Order - Transfer No Lien (TNL)</h1>

    {{-- Legend --}}
    <div class="d-flex align-items-center gap-2 mb-3" style="gap:12px; flex-wrap: wrap;">
        <small><span class="field-badge badge-auto">AUTO</span> Auto-filled from DB (read-only)</small>
        <small><span class="field-badge badge-manual">VERIFY</span> Pre-filled but editable</small>
        <small><span class="field-badge badge-empty">REQUIRED</span> Must be entered</small>
        <small><span class="field-badge badge-optional">OPTIONAL</span> Optional field</small>
    </div>
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

    {{-- Place above the VIN banner, before the <form> --}}
    <div class="card mb-3">
        <div class="card-body">
            <label class="font-weight-bold mb-2 d-block">Transaction Type <span class="required-note">*</span></label>
            <select class="form-control @error('ndtc_transaction_type') is-invalid @enderror"
                    name="ndtc_transaction_type"
                    id="txnTypeSelect"
                    style="max-width: 380px;"
                    onchange="document.getElementById('noTitleReasonBlock').style.display = ['DNT','EOL','RWUT','RWOT'].includes(this.value) ? 'block' : 'none';">
                <option value="TNL"  {{ $val('ndtc_transaction_type', 'TNL') == 'TNL'  ? 'selected' : '' }}>Transfer No Lien</option>
                <option value="TWL"  {{ $val('ndtc_transaction_type') == 'TWL'  ? 'selected' : '' }}>Transfer With Lien</option>
                <option value="TWEL" {{ $val('ndtc_transaction_type') == 'TWEL' ? 'selected' : '' }}>Transfer With Electronic Lien</option>
                <option value="DNT"  {{ $val('ndtc_transaction_type') == 'DNT'  ? 'selected' : '' }}>Dealer No Title</option>
                <option value="EOL"  {{ $val('ndtc_transaction_type') == 'EOL'  ? 'selected' : '' }}>End of Lease</option>
                <option value="RT"   {{ $val('ndtc_transaction_type') == 'RT'   ? 'selected' : '' }}>Recovered Theft</option>
                <option value="RWT"  {{ $val('ndtc_transaction_type') == 'RWT'  ? 'selected' : '' }}>Repossession With Title</option>
                <option value="RWUT" {{ $val('ndtc_transaction_type') == 'RWUT' ? 'selected' : '' }}>Repossession With Unfiled Title</option>
                <option value="RWOT" {{ $val('ndtc_transaction_type') == 'RWOT' ? 'selected' : '' }}>Repossession Without Title</option>
                <option value="SNL"  {{ $val('ndtc_transaction_type') == 'SNL'  ? 'selected' : '' }}>Salvage No Lien</option>
                <option value="SNT"  {{ $val('ndtc_transaction_type') == 'SNT'  ? 'selected' : '' }}>Salvage No Title</option>
                <option value="SWL"  {{ $val('ndtc_transaction_type') == 'SWL'  ? 'selected' : '' }}>Salvage With Lien</option>
                <option value="SPR"  {{ $val('ndtc_transaction_type') == 'SPR'  ? 'selected' : '' }}>Single Party Retitling</option>
                <option value="SPS"  {{ $val('ndtc_transaction_type') == 'SPS'  ? 'selected' : '' }}>Single Party Salvage</option>
                <option value="UTNL" {{ $val('ndtc_transaction_type') == 'UTNL' ? 'selected' : '' }}>Unrecovered Theft No Lien</option>
                <option value="UTNT" {{ $val('ndtc_transaction_type') == 'UTNT' ? 'selected' : '' }}>Unrecovered Theft No Title</option>
                <option value="UTWL" {{ $val('ndtc_transaction_type') == 'UTWL' ? 'selected' : '' }}>Unrecovered Theft With Lien</option>
            </select>
            @error('ndtc_transaction_type')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

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
            <div class="txn-type" id="txnBadge">TNL</div>
            <div class="txn-label">Transaction</div>
        </div>
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

        @php
            $transactionType = old('ndtc_transaction_type', $order->transaction_type ?? 'TNL');
            $needsNoTitleReason = in_array($transactionType, ['DNT', 'EOL', 'RWUT', 'RWOT']);
        @endphp

        @if($needsNoTitleReason)
            @include('pages.ndtc.partials._no-title-reason')
        @endif
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 1: ACQUIRING ENTITY (REQUIRED)                              --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        @include('pages.ndtc.partials._acquiring-entity')

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 2: TITLE WORK ENTITY (REQUIRED)                             --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        @include('pages.ndtc.partials._title-work-entity')
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 3: VEHICLE INFORMATION (REQUIRED)                            --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        @include('pages.ndtc.partials._vehicle')

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 4: EXISTING TITLE (REQUIRED)                                --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        @include('pages.ndtc.partials._existing-title')

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 5: ODOMETER (REQUIRED)                                      --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        @include('pages.ndtc.partials._odometer')

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 6: DISPOSING ENTITY (AUCTION) (REQUIRED)                    --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        @include('pages.ndtc.partials._disposing-entity')

        {{-- ═══════════════════════════════════════════════════════════════════════ --}}
        {{-- SECTION 7: TRANSFER DETAILS (REQUIRED)                              --}}
        {{-- ═══════════════════════════════════════════════════════════════════════ --}}

        @include('pages.ndtc.partials._transfer-details')

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

    const TXN_LABELS = {
        'TNL':  'Transfer No Lien (TNL)',
        'TWL':  'Transfer With Lien (TWL)',
        'TWEL': 'Transfer With Electronic Lien (TWEL)',
        'DNT':  'Dealer No Title (DNT)',
        'EOL':  'End of Lease (EOL)',
        'RT':   'Recovered Theft (RT)',
        'RWT':  'Repossession With Title (RWT)',
        'RWUT': 'Repossession With Unfiled Title (RWUT)',
        'RWOT': 'Repossession Without Title (RWOT)',
        'SNL':  'Salvage No Lien (SNL)',
        'SNT':  'Salvage No Title (SNT)',
        'SWL':  'Salvage With Lien (SWL)',
        'SPR':  'Single Party Retitling (SPR)',
        'SPS':  'Single Party Salvage (SPS)',
        'UTNL': 'Unrecovered Theft No Lien (UTNL)',
        'UTNT': 'Unrecovered Theft No Title (UTNT)',
        'UTWL': 'Unrecovered Theft With Lien (UTWL)',
    };

    const NO_TITLE_REASON_TYPES = ['DNT', 'EOL', 'RWUT', 'RWOT'];

    $('#txnTypeSelect').on('change', function () {
        const type = $(this).val();

        // Update H1
        $('#pageTitle').text('Create NDTC Order - ' + TXN_LABELS[type]);

        // Update VIN banner badge
        $('#txnBadge').text(type);

        // Toggle No Title Reason block
        $('#noTitleReasonBlock').toggle(NO_TITLE_REASON_TYPES.includes(type));
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
