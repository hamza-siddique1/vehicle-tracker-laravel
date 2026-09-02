@extends('layouts.app')

@section('title', 'Order Detail — ' . $order->vin)

@section('styles')
<style>
/* ── Order hero ──────────────────────────────── */
.order-hero{background:#293042;border-radius:.25rem;padding:1rem 1.25rem;margin-bottom:1rem}
.hero-vin{font-family:"Courier New",monospace;font-size:.72rem;color:rgba(255,255,255,.4);letter-spacing:.08em}
.hero-name{font-size:1rem;font-weight:600;color:#fff;margin:.2rem 0 0}
.hero-meta{display:flex;flex-wrap:wrap;gap:1rem;margin-top:.5rem}
.hero-meta-item{font-size:.72rem;color:rgba(255,255,255,.45);display:flex;align-items:center;gap:.3rem}

/* ── Pipeline ────────────────────────────────── */
.pipeline-wrap{background:#fff;border:1px solid #dee2e6;border-radius:.25rem;padding:.75rem 1rem;margin-bottom:1rem;overflow-x:auto}
.pipeline{display:flex;align-items:center;min-width:500px}
.pipe-step{display:flex;flex-direction:column;align-items:center;gap:4px;flex:1;position:relative}
.pipe-step:not(:last-child)::after{content:'';position:absolute;top:12px;left:calc(50% + 15px);right:calc(-50% + 15px);height:1.5px;background:#dee2e6}
.pipe-step.done::after{background:#3f80ea}
.pipe-step.err::after{background:#d9534f}
.pipe-dot{width:26px;height:26px;border-radius:50%;border:2px solid #dee2e6;background:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;color:#adb5bd;z-index:1;flex-shrink:0}
.pipe-step.done .pipe-dot{background:#3f80ea;border-color:#3f80ea;color:#fff}
.pipe-step.active .pipe-dot{border-color:#3f80ea;color:#3f80ea}
.pipe-step.err .pipe-dot{background:#fdf2f2;border-color:#d9534f;color:#d9534f}
.pipe-label{font-size:.65rem;color:#adb5bd;text-align:center;white-space:nowrap;font-weight:500;line-height:1.2}
.pipe-step.done .pipe-label{color:#3f80ea}
.pipe-step.err  .pipe-label{color:#d9534f}
.pipe-step.active .pipe-label{color:#3f80ea;font-weight:600}

/* ── Stat cards ──────────────────────────────── */
.stat-card{background:#fff;border:1px solid #dee2e6;border-radius:.25rem;padding:.875rem 1rem}
.stat-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:#adb5bd;font-weight:600;margin-bottom:4px}
.stat-value{font-size:1.15rem;font-weight:600;color:#212529;line-height:1}
.stat-sub{font-size:.7rem;color:#6c757d;margin-top:3px}

/* ── Badge-status ────────────────────────────── */
.badge-status{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:12px;font-size:.72rem;font-weight:600}
.badge-rejected{background:#fdf2f2;color:#d9534f}
.badge-approved{background:#f0faf4;color:#4bbf73}
.badge-processing{background:#eff6ff;color:#3f80ea}
.badge-draft{background:#f8f9fa;color:#6c757d;border:1px solid #dee2e6}
.badge-review{background:#fff8e1;color:#856404}
.badge-hold{background:#fff8e1;color:#856404}
.badge-rtf{background:#f0faf4;color:#4bbf73}
.badge-cancel{background:#f8f9fa;color:#6c757d}

/* ── Entity cards ────────────────────────────── */
.entity-card{background:#f8f9fa;border:1px solid #dee2e6;border-radius:.25rem;padding:.75rem;height:100%}
.entity-type{font-size:.62rem;text-transform:uppercase;letter-spacing:.08em;color:#adb5bd;font-weight:600;margin-bottom:3px}
.entity-name{font-size:.8rem;font-weight:600;color:#212529}
.entity-addr{font-size:.72rem;color:#6c757d;margin-top:3px;line-height:1.5}

/* ── Section cards ───────────────────────────── */
.section-card{background:#fff;border:1px solid #dee2e6;border-radius:.25rem}
.section-card .card-header{background:#fff;border-bottom:1px solid #dee2e6;padding:.65rem 1rem;display:flex;align-items:center;gap:8px}
.section-card .card-header .header-icon{width:26px;height:26px;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:.72rem;flex-shrink:0}
.section-card .card-header h6{font-size:.8rem;font-weight:600;color:#212529;margin:0}

/* ── Info rows ───────────────────────────────── */
.info-row{display:flex;justify-content:space-between;align-items:flex-start;padding:5px 0;border-bottom:1px solid #f5f7fb;gap:8px}
.info-row:last-child{border-bottom:none;padding-bottom:0}
.info-key{font-size:.75rem;color:#6c757d;flex-shrink:0;max-width:45%}
.info-val{font-size:.78rem;color:#212529;font-weight:500;text-align:right;word-break:break-all}
.info-val.mono{font-family:"Courier New",monospace;font-size:.7rem;letter-spacing:.04em}

/* ── Section divider ─────────────────────────── */
.section-divider{font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#adb5bd;display:flex;align-items:center;gap:8px;margin:.75rem 0 .5rem}
.section-divider::after{content:'';flex:1;height:1px;background:#f0f0f0}

/* ── Documents ───────────────────────────────── */
.doc-item{display:flex;align-items:center;gap:10px;padding:.75rem;background:#f8f9fa;border:1px solid #dee2e6;border-radius:.25rem;margin-bottom:.5rem}
.doc-item:hover{border-color:#adb5bd}
.doc-item.doc-rejected{background:#fdf2f2;border-color:#f5c6cb}
.doc-icon{width:34px;height:34px;border-radius:6px;background:#fff;border:1px solid #dee2e6;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.doc-icon.rejected{background:#fdf2f2;border-color:#f5c6cb}
.doc-info{flex:1;min-width:0}
.doc-name{font-size:.8rem;font-weight:600;color:#212529;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.doc-meta{font-size:.7rem;color:#6c757d;margin-top:2px;display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.doc-reason{font-size:.72rem;color:#d9534f;margin-top:4px;display:flex;align-items:flex-start;gap:4px}
.doc-actions{display:flex;gap:5px;flex-shrink:0}
.doc-upload-zone{border:2px dashed #dee2e6;border-radius:.25rem;padding:1.25rem;text-align:center;color:#adb5bd;font-size:.8rem;cursor:pointer}
.doc-upload-zone:hover{border-color:#3f80ea;color:#3f80ea;background:#eff6ff}

/* ── Timeline ────────────────────────────────── */
.tl-item{display:flex;gap:10px;padding-bottom:1rem;position:relative}
.tl-item:not(:last-child) .tl-line{position:absolute;left:14px;top:28px;bottom:0;width:1px;background:#dee2e6}
.tl-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;flex-shrink:0;z-index:1;border:1px solid #dee2e6;background:#f8f9fa}
.tl-dot.t-red{background:#fdf2f2;border-color:#f5c6cb;color:#d9534f}
.tl-dot.t-blue{background:#eff6ff;border-color:#bbd6fb;color:#3f80ea}
.tl-dot.t-green{background:#f0faf4;border-color:#b8e0c4;color:#4bbf73}
.tl-dot.t-gray{background:#f8f9fa;border-color:#dee2e6;color:#adb5bd}
.tl-dot.t-amber{background:#fff8e1;border-color:#ffeaa7;color:#856404}
.tl-body{flex:1;padding-top:3px}
.tl-event{font-size:.8rem;font-weight:600;color:#212529}
.tl-time{font-size:.7rem;color:#adb5bd;margin-top:1px}
.tl-detail{font-size:.75rem;color:#495057;margin-top:5px;background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;padding:6px 9px;line-height:1.55}

/* ── Rejection block ─────────────────────────── */
.rejection-block{background:#fdf2f2;border:1px solid #f5c6cb;border-radius:.25rem;padding:.875rem;margin-bottom:.75rem}
.rejection-title{font-size:.8rem;font-weight:600;color:#d9534f;margin-bottom:5px;display:flex;align-items:center;gap:5px}
.rejection-text{font-size:.78rem;color:#212529;line-height:1.6;background:#fff;border-radius:4px;padding:.5rem .75rem;white-space:pre-wrap;border:1px solid #f5c6cb}

.alert {
    padding: 10px;
}
</style>
@endsection
@php
    // ── Extract payload data ──────────────────────────────────────
    $payload   = $order->order_payload ?? [];
    $evidence  = $payload['evidence'] ?? [];
    $veh       = $evidence['vehicle'] ?? [];
    $title     = $evidence['existingTitle'] ?? [];
    $odo       = $veh['odometer'] ?? [];
    $odoRead   = $odo['reading'] ?? [];
    $disposing = $evidence['disposingEntities'][0] ?? [];
    $dispAddr  = $disposing['physicalAddress'] ?? [];
    $acquiring = $payload['acquiringEntity'] ?? [];
    $acqAddr   = $acquiring['physicalAddress'] ?? [];
    $titleWork = $payload['titleWorkEntity'] ?? [];
    $rep       = $titleWork['representative'] ?? [];

    // ── Status helpers ────────────────────────────────────────────
    $statusClass = match($order->status) {
        'REJECTED'                    => 'badge-rejected',
        'APPROVED', 'COMPLETED'       => 'badge-approved',
        'PROCESSING'                  => 'badge-processing',
        'MANUAL_REVIEW', 'ON_HOLD'    => 'badge-hold',
        'READY_TO_FINALIZE'           => 'badge-rtf',
        'READY_FOR_DOCUMENTS'         => 'badge-review',
        'CANCELLED', 'TITLE_TERMINATED' => 'badge-cancel',
        default                       => 'badge-draft',
    };

    $statusLabel = match($order->status) {
        'DRAFT'                 => 'Draft',
        'READY_FOR_DOCUMENTS'   => 'Ready for Documents',
        'READY_TO_FINALIZE'     => 'Ready to Finalize',
        'PROCESSING'            => 'Processing',
        'MANUAL_REVIEW'         => 'Manual Review',
        'ON_HOLD'               => 'On Hold',
        'APPROVED'              => 'Approved',
        'COMPLETED'             => 'Completed',
        'REJECTED'              => 'Rejected — Action Required',
        'CANCELLED'             => 'Cancelled',
        'AGING'                 => 'Aging — Action Needed',
        'TITLE_TERMINATED'      => 'Title Terminated',
        default                 => $order->status,
    };

    // ── Pipeline step helper ──────────────────────────────────────
    // step: 1=Created 2=RFD 3=Docs 4=Finalized 5=Processing 6=Decision 7=Approved
    $rank = match($order->status) {
        'DRAFT'               => 1,
        'READY_FOR_DOCUMENTS' => 2,
        'AGING'               => 2,
        'READY_TO_FINALIZE'   => 3,
        'PROCESSING'          => 5,
        'MANUAL_REVIEW','ON_HOLD' => 5,
        'REJECTED'            => 6,
        'APPROVED','COMPLETED'=> 7,
        'CANCELLED'           => 6,
        'TITLE_TERMINATED'    => 7,
        default               => 1,
    };

    $isRejected  = $order->status === 'REJECTED';
    $isApproved  = in_array($order->status, ['APPROVED','COMPLETED']);
    $isCancelled = $order->status === 'CANCELLED';

    // Timeline dot class helper
    $dotClass = fn(string $event) => match($event) {
        'ORDER_REJECTED'       => 't-red',
        'ORDER_APPROVED'       => 't-green',
        'READY_TO_FINALIZE'    => 't-green',
        'PROCESSING'           => 't-blue',
        'MANUAL_REVIEW'        => 't-amber',
        'ON_HOLD'              => 't-amber',
        'AGING_ORDER'          => 't-amber',
        'ORDER_CANCELED'       => 't-gray',
        'TITLE_TERMINATED'     => 't-red',
        default                => 't-gray',
    };

    $dotIcon = fn(string $event) => match($event) {
        'ORDER_REJECTED','ORDER_CANCELED','TITLE_TERMINATED' => 'fa-times',
        'ORDER_APPROVED','READY_TO_FINALIZE','READY_FOR_DOCUMENTS' => 'fa-check',
        'PROCESSING'  => 'fa-spinner',
        'MANUAL_REVIEW','ON_HOLD','AGING_ORDER' => 'fa-clock',
        default       => 'fa-circle',
    };

    // Rejection reasons
    $rejections = $order->rejection_reasons ?? [];

    $terminalStatuses = ['COMPLETED', 'CANCELLED', 'AUTO_REJECTED', 'MANUALLY_REJECTED', 'TITLE_TERMINATED'];
@endphp

@section('content')

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @elseif(session('account'))
        <x-alert type="success">{{ session('account') }}</x-alert>
    @endif
{{-- ══ PAGE HEADING ═══════════════════════════════════════════ --}}
<div class="d-flex align-items-start justify-content-between mb-3">
    <div>
        <h1 class="h3 mb-1">Order Detail</h1>
        <p class="text-muted mb-0" style="font-size:.8rem">
            NDTC ID: <code>{{ $order->ndtc_order_id ?? '—' }}</code>
            &nbsp;·&nbsp;
            Internal ref: <code>{{ $order->correlation_id }}</code>
        </p>
    </div>
    <a href="{{ route('ndtc.orders.index') }}" class="btn btn-secondary btn-sm">
        &larr; All Orders
    </a>
</div>

{{-- ══ ALERT BANNERS ══════════════════════════════════════════ --}}
@if($isRejected)
    <div class="alert alert-danger d-flex align-items-start mb-3">
        <i class="fas fa-exclamation-circle mr-2 mt-1" style="flex-shrink:0"></i>
        <div>
            <strong>DMV rejected this order.</strong>
            @if(!empty($rejections[0]['reasons'][0]))
                {{ $rejections[0]['reasons'][0] }}
            @endif
            Replace the flagged document in the <a href="#tab-documents" class="alert-link" data-toggle="tab">Documents tab</a>,
            then resubmit this same order. <strong>Do not create a new order.</strong>
        </div>
    </div>
@elseif($order->status === 'READY_TO_FINALIZE')
    <div class="alert alert-success d-flex align-items-start mb-3">
        <i class="fas fa-check-circle mr-2 mt-1" style="flex-shrink:0"></i>
        <div>
            <strong>Ready to finalize.</strong>
            All required documents have been received. Click Finalize to submit to the WV DMV.
        </div>
    </div>
@elseif($order->status === 'READY_FOR_DOCUMENTS')
    <div class="alert alert-warning d-flex align-items-start mb-3">
        <i class="fas fa-exclamation-triangle mr-2 mt-1" style="flex-shrink:0"></i>
        <div>
            <strong>Upload required.</strong>
            The order is ready. Please upload the title front and back scan to continue.
        </div>
    </div>
@elseif($order->status === 'MANUAL_REVIEW')
    <div class="alert alert-info d-flex align-items-start mb-3">
        <i class="fas fa-info-circle mr-2 mt-1" style="flex-shrink:0"></i>
        <div>
            <strong>Manual review in progress.</strong>
            The DMV is reviewing this order. No action required at this time.
        </div>
    </div>
@elseif($order->status === 'ON_HOLD')
    <div class="alert alert-warning d-flex align-items-start mb-3">
        <i class="fas fa-pause-circle mr-2 mt-1" style="flex-shrink:0"></i>
        <div>
            <strong>Order on hold.</strong>
            The DMV has placed this order on hold. No action required — processing may take longer.
        </div>
    </div>
@elseif($order->status === 'AGING')
    <div class="alert alert-warning d-flex align-items-start mb-3">
        <i class="fas fa-exclamation-triangle mr-2 mt-1" style="flex-shrink:0"></i>
        <div>
            <strong>Order aging.</strong>
            This order has been actionable for 5 or more days without action.
            Please finalize or cancel it.
        </div>
    </div>
@elseif($isApproved)
    <div class="alert alert-success d-flex align-items-start mb-3">
        <i class="fas fa-check-circle mr-2 mt-1" style="flex-shrink:0"></i>
        <div>
            <strong>Order approved.</strong>
            @if($order->new_title_number)
                New WV title issued: <strong>{{ $order->new_title_number }}</strong>
            @endif
        </div>
    </div>
@endif

{{-- ══ HERO BANNER ════════════════════════════════════════════ --}}
<div class="order-hero mb-3">
    <div class="d-flex align-items-start justify-content-between">
        <div>
            <div class="d-flex align-items-center mb-1" style="gap:.5rem">
                <span class="badge-status {{ $statusClass }}">
                    @if($isRejected)        <i class="fas fa-times"></i>
                    @elseif($isApproved)    <i class="fas fa-check"></i>
                    @elseif($isCancelled)   <i class="fas fa-ban"></i>
                    @else                   <i class="fas fa-circle-notch"></i>
                    @endif
                    {{ $statusLabel }}
                </span>
                <span class="badge bg-info" style="font-size:.72rem">
                    {{ $order->transaction_type }}
                </span>
            </div>
            <div class="hero-vin">VIN: {{ $order->vin }}</div>
            <div class="hero-name">{{ $order->vehicle_description }}</div>
            <div class="hero-meta">
                @if($order->ndtc_order_id)
                    <span class="hero-meta-item">
                        <i class="fas fa-hashtag"></i>
                        {{ Str::limit($order->ndtc_order_id, 16) }}
                    </span>
                @endif
                <span class="hero-meta-item">
                    <i class="fas fa-link"></i>
                    {{ $order->correlation_id }}
                </span>
                @if($order->transfer_date)
                    <span class="hero-meta-item">
                        <i class="fas fa-calendar"></i>
                        Transfer: {{ $order->transfer_date->format('M d, Y') }}
                    </span>
                @endif
                @if(!empty($title['issuingStateCode']))
                    <span class="hero-meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        Title state: {{ $title['issuingStateCode'] }}
                    </span>
                @endif
                @if(!empty($title['titleNumber']))
                    <span class="hero-meta-item">
                        <i class="fas fa-file-alt"></i>
                        Title #: {{ $title['titleNumber'] }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="d-flex flex-column align-items-end" style="gap:.5rem">
            <div class="d-flex" style="gap:.4rem">
                @if($order->canBeResubmitted())
                    <form action="{{ route('ndtc.orders.update', $order) }}" method="POST" class="d-inline">
                        @csrf @method('PUT')
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-paper-plane mr-1"></i>Resubmit
                        </button>
                    </form>
                @endif
                @if($order->canBeFinalized())
                    <form action="{{ route('ndtc.orders.finalize', $order) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-check mr-1"></i>Finalize
                        </button>
                    </form>
                @endif
                @if($order->isRejected())
                    <a href="{{ route('ndtc.orders.edit', $order) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit mr-1"></i>Edit Order
                    </a>
                @endif
                @if($order->canBeCancelled())
                    <form action="{{ route('ndtc.orders.cancel', $order) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary"
                                onclick="return confirm('Cancel this order? This cannot be undone.')">
                            <i class="fas fa-ban mr-1"></i>Cancel
                        </button>
                    </form>
                @endif
            </div>
            <span style="font-size:.68rem;color:rgba(255,255,255,.35)">
                Submission #{{ $order->submission_count }}
                @if($order->rejection_count > 0)
                    · Rejection #{{ $order->rejection_count }}
                @endif
            </span>
        </div>
    </div>
</div>

{{-- ══ PIPELINE ════════════════════════════════════════════════ --}}
<div class="pipeline-wrap">
    <div class="pipeline">
        @php
            $steps = [
                1 => 'Created',
                2 => 'Ready for<br>Documents',
                3 => 'Docs<br>Uploaded',
                4 => 'Finalized',
                5 => 'Processing',
                6 => $isCancelled ? 'Cancelled' : 'Rejected',
                7 => 'Approved',
            ];
        @endphp
        @foreach($steps as $step => $label)
            @php
                if ($step === 6) {
                    if ($isRejected)       $cls = 'err';
                    elseif ($isCancelled)  $cls = 'err';
                    elseif ($isApproved)   $cls = 'done';
                    elseif ($rank >= 6)    $cls = 'done';
                    else                   $cls = 'wait';
                } elseif ($step === 7) {
                    $cls = $isApproved ? 'done' : 'wait';
                } elseif ($step === 4) {
                    $cls = $order->finalized ? 'done' : ($rank === 4 ? 'active' : 'wait');
                } else {
                    if ($rank > $step)      $cls = 'done';
                    elseif ($rank === $step) $cls = 'active';
                    else                    $cls = 'wait';
                }
            @endphp
            <div class="pipe-step {{ $cls }}">
                <div class="pipe-dot">
                    @if($cls === 'done')         <i class="fas fa-check"></i>
                    @elseif($cls === 'err')       <i class="fas fa-times"></i>
                    @elseif($cls === 'active')    <i class="fas fa-circle" style="font-size:.4rem"></i>
                    @else                         –
                    @endif
                </div>
                <div class="pipe-label">{!! $label !!}</div>
            </div>
        @endforeach
    </div>
</div>

{{-- ══ STAT CARDS ══════════════════════════════════════════════ --}}
<div class="row mb-3">
    <div class="col-md-3 mb-2">
        <div class="stat-card">
            <div class="stat-label">Status</div>
            <div class="stat-value" style="font-size:.9rem;margin-top:3px">
                <span class="badge-status {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
            <div class="stat-sub">{{ $order->ndtc_status ?? '—' }}</div>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="stat-card">
            <div class="stat-label">Submission count</div>
            <div class="stat-value">{{ $order->submission_count }}</div>
            <div class="stat-sub">
                @if($order->finalized_at)
                    Last: {{ $order->finalized_at->format('M d, Y') }}
                @else
                    Not yet finalized
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="stat-card">
            <div class="stat-label">Rejection count</div>
            <div class="stat-value @if($order->rejection_count > 0) text-danger @endif">
                {{ $order->rejection_count }}
            </div>
            <div class="stat-sub">
                @if($order->rejected_at)
                    Last: {{ $order->rejected_at->format('M d, Y') }}
                @else
                    No rejections
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="stat-card">
            <div class="stat-label">Transfer date</div>
            <div class="stat-value" style="font-size:.95rem">
                {{ $order->transfer_date?->format('M d, Y') ?? '—' }}
            </div>
            <div class="stat-sub">
                @if($order->transfer_date)
                    {{ $order->transfer_date->diffForHumans() }}
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══ TABS ════════════════════════════════════════════════════ --}}
<ul class="nav nav-tabs" id="orderTabs">
    <li class="nav-item">
        <a class="nav-link active" href="#tab-overview" data-toggle="tab">
            <i class="fas fa-info-circle mr-1"></i>Overview
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#tab-documents" data-toggle="tab">
            <i class="fas fa-folder mr-1"></i>Documents
            @php $pendingDocs = $order->documents->where('status', 'FAILED')->count() @endphp
            @if($pendingDocs > 0)
                <span class="badge badge-danger ml-1" style="font-size:.6rem">{{ $pendingDocs }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#tab-timeline" data-toggle="tab">
            <i class="fas fa-history mr-1"></i>Timeline
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#tab-archive" data-toggle="tab">
            <i class="fas fa-archive mr-1"></i>Archive
        </a>
    </li>
    @if($isRejected)
        <li class="nav-item">
            <a class="nav-link" href="#tab-rejection" data-toggle="tab">
                <i class="fas fa-exclamation-circle mr-1"></i>Rejection
                <span class="badge badge-danger ml-1" style="font-size:.6rem">!</span>
            </a>
        </li>
    @endif
    @if($order->rejectionHistory->count() > 0)
        <li class="nav-item">
            <a class="nav-link" href="#tab-history" data-toggle="tab">
                <i class="fas fa-clock mr-1"></i>Rejection History
                <span class="badge badge-secondary ml-1" style="font-size:.6rem">
                    {{ $order->rejectionHistory->count() }}
                </span>
            </a>
        </li>
    @endif
</ul>

<div class="tab-content border border-top-0 rounded-bottom bg-white p-4">

    {{-- ══ TAB 1: OVERVIEW ════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="tab-overview">

        {{-- Entities --}}
        <div class="section-divider"><i class="fas fa-building"></i> Entities</div>
        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <div class="entity-card">
                    <div class="entity-type"><i class="fas fa-building mr-1"></i> Acquiring entity</div>
                    <div class="entity-name">{{ $acquiring['name'] ?? config('ndtc.acquiring_name', '—') }}</div>
                    <div class="entity-addr">
                        {{ $acqAddr['addressLine1'] ?? config('ndtc.acquiring_address1') }}<br>
                        {{ $acqAddr['city'] ?? config('ndtc.acquiring_city') }},
                        {{ $acqAddr['stateCode'] ?? config('ndtc.acquiring_state') }}
                        {{ $acqAddr['zipCode'] ?? config('ndtc.acquiring_zip') }}<br>
                        NRB #{{ config('ndtc.nrb_number') }}
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="entity-card">
                    <div class="entity-type"><i class="fas fa-gavel mr-1"></i> Disposing entity</div>
                    <div class="entity-name">{{ $disposing['name'] ?? '—' }}</div>
                    <div class="entity-addr">
                        @if(!empty($dispAddr['addressLine1']))
                            {{ $dispAddr['addressLine1'] }}<br>
                        @endif
                        @if(!empty($dispAddr['addressLine2']))
                            {{ $dispAddr['addressLine2'] }}<br>
                        @endif
                        {{ $dispAddr['city'] ?? '' }}
                        @if(!empty($dispAddr['stateCode']))
                            , {{ $dispAddr['stateCode'] }}
                        @endif
                        {{ $dispAddr['zipCode'] ?? '' }}<br>
                        <small class="text-muted">Ad-hoc (no pre-registration)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="entity-card">
                    <div class="entity-type"><i class="fas fa-shield-alt mr-1"></i> Title work entity</div>
                    <div class="entity-name">{{ $acquiring['name'] ?? config('ndtc.acquiring_name') }}</div>
                    <div class="entity-addr">
                        Agent: {{ ($rep['firstName'] ?? '') . ' ' . ($rep['lastName'] ?? '') }}<br>
                        {{ $rep['email'] ?? '' }}<br>
                        Role: {{ $rep['relationshipToEntity'] ?? 'AGENT' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Vehicle + Title --}}
        <div class="section-divider"><i class="fas fa-car"></i> Vehicle & Title</div>
        <div class="row mb-3">
            <div class="col-md-6 mb-2">
                <div class="section-card h-100">
                    <div class="card-header">
                        <div class="header-icon" style="background:#dbeafe;color:#1d4ed8">
                            <i class="fas fa-car"></i>
                        </div>
                        <h6>Vehicle Details</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="info-row">
                            <span class="info-key">VIN</span>
                            <span class="info-val mono">{{ $veh['vin'] ?? $order->vin }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Year</span>
                            <span class="info-val">{{ $veh['year'] ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Make (NCIC)</span>
                            <span class="info-val">{{ $veh['make'] ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Model</span>
                            <span class="info-val">{{ $veh['model'] ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Body style</span>
                            <span class="info-val">{{ $veh['bodyStyle'] ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Vehicle class</span>
                            <span class="info-val">{{ $veh['vehicleClass'] ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Fuel type</span>
                            <span class="info-val">{{ $veh['fuelType'] ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Weight</span>
                            <span class="info-val">
                                @if(!empty($veh['weight']['weight']))
                                    {{ number_format($veh['weight']['weight']) }} {{ $veh['weight']['unit'] ?? 'LBS' }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <div class="section-card h-100">
                    <div class="card-header">
                        <div class="header-icon" style="background:#dcfce7;color:#15803d">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h6>Existing Title</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="info-row">
                            <span class="info-key">Title number</span>
                            <span class="info-val mono">{{ $title['titleNumber'] ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Issuing state</span>
                            <span class="info-val">{{ $title['issuingStateCode'] ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Title type</span>
                            <span class="info-val">{{ $title['titleType'] ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Brands</span>
                            <span class="info-val">
                                @forelse($title['titleBrands'] ?? [] as $brand)
                                    <span class="badge badge-warning" style="font-size:.65rem">{{ $brand }}</span>
                                @empty
                                    —
                                @endforelse
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Odometer</span>
                            <span class="info-val">
                                @if(!empty($odoRead['reading']))
                                    {{ number_format($odoRead['reading']) }} {{ $odoRead['unit'] ?? 'MI' }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Odo condition</span>
                            <span class="info-val">{{ $odo['condition'] ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Odometer date</span>
                            <span class="info-val">
                                @if(!empty($odoRead['date']))
                                    {{ \Carbon\Carbon::parse($odoRead['date'])->format('M d, Y') }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Transfer date</span>
                            <span class="info-val">
                                {{ $order->transfer_date?->format('M d, Y') ?? '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order details --}}
        <div class="section-divider"><i class="fas fa-cog"></i> Order Details</div>
        <div class="row">
            <div class="col-md-6 mb-2">
                <div class="section-card">
                    <div class="card-header">
                        <div class="header-icon" style="background:#f3e8ff;color:#7c3aed">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h6>Order Information</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="info-row">
                            <span class="info-key">NDTC order ID</span>
                            <span class="info-val mono">{{ $order->ndtc_order_id ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Correlation ID</span>
                            <span class="info-val mono">{{ $order->correlation_id }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Transaction type</span>
                            <span class="info-val">{{ $order->transaction_type }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Vertical</span>
                            <span class="info-val">{{ $payload['vertical']['type'] ?? 'NATIONAL_RETAILER' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Created by</span>
                            <span class="info-val">{{ $order->createdBy?->name ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Created at</span>
                            <span class="info-val">{{ $order->created_at->format('M d, Y H:i') }} UTC</span>
                        </div>
                        @if($order->finalized_at)
                            <div class="info-row">
                                <span class="info-key">Finalized at</span>
                                <span class="info-val">{{ $order->finalized_at->format('M d, Y H:i') }} UTC</span>
                            </div>
                        @endif
                        @if($isApproved && $order->new_title_number)
                            <div class="info-row">
                                <span class="info-key">New title #</span>
                                <span class="info-val mono text-success font-weight-bold">
                                    {{ $order->new_title_number }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                @if($isRejected)
                    <div class="section-card">
                        <div class="card-header">
                            <div class="header-icon" style="background:#fef9c3;color:#854d0e">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h6>Current Rejection Summary</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="info-row">
                                <span class="info-key">NDTC status</span>
                                <span class="info-val">
                                    <span class="badge badge-danger" style="font-size:.65rem">
                                        {{ $order->ndtc_status ?? 'REJECTED' }}
                                    </span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-key">Rejected at</span>
                                <span class="info-val">{{ $order->rejected_at?->format('M d, Y H:i') }} UTC</span>
                            </div>
                            <div class="info-row">
                                <span class="info-key">Rejection count</span>
                                <span class="info-val text-danger font-weight-bold">
                                    {{ $order->rejection_count }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-key">Submission count</span>
                                <span class="info-val">{{ $order->submission_count }}</span>
                            </div>
                            <div class="mt-2">
                                <a href="#tab-rejection" class="btn btn-sm btn-outline-danger btn-block"
                                   data-toggle="tab">
                                    <i class="fas fa-exclamation-circle mr-1"></i>View Rejection Details
                                </a>
                            </div>
                        </div>
                    </div>
                @elseif($isApproved)
                    <div class="section-card">
                        <div class="card-header">
                            <div class="header-icon" style="background:#dcfce7;color:#15803d">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h6>Approval Details</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="info-row">
                                <span class="info-key">NDTC status</span>
                                <span class="info-val">
                                    <span class="badge badge-success" style="font-size:.65rem">
                                        {{ $order->ndtc_status }}
                                    </span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-key">Approved at</span>
                                <span class="info-val">{{ $order->approved_at?->format('M d, Y H:i') }} UTC</span>
                            </div>
                            @if($order->new_title_number)
                                <div class="info-row">
                                    <span class="info-key">New title number</span>
                                    <span class="info-val mono text-success font-weight-bold">
                                        {{ $order->new_title_number }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>{{-- /tab-overview --}}

    {{-- ══ TAB 2: DOCUMENTS ══════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-documents">

        @php
            $failedDocs   = $order->documents->where('status', 'FAILED');
            $uploadedDocs = $order->documents->whereNotIn('status', ['FAILED','REPLACED']);
        @endphp

        @if($failedDocs->count() > 0)
            <div class="alert alert-warning d-flex align-items-start mb-3">
                <i class="fas fa-exclamation-triangle mr-2 mt-1" style="flex-shrink:0"></i>
                <div>
                    <strong>{{ $failedDocs->count() }} document(s) require attention.</strong>
                    Replace the flagged document(s) before resubmitting.
                </div>
            </div>

            <div class="section-divider">
                <i class="fas fa-exclamation-circle text-danger"></i> Requires Action
            </div>

            @foreach($failedDocs as $doc)
                <div class="doc-item doc-rejected mb-2">
                    <div class="doc-icon rejected">
                        <i class="fas fa-file-pdf" style="color:#d9534f"></i>
                    </div>
                    <div class="doc-info">
                        <div class="doc-name">{{ $doc->file_display_name ?? $doc->document_content }}</div>
                        <div class="doc-meta">
                            <span class="badge badge-danger" style="font-size:.65rem">
                                <i class="fas fa-times mr-1"></i>Failed
                            </span>
                            @if($doc->file_mime_type) <span>{{ strtoupper(explode('/', $doc->file_mime_type)[1] ?? $doc->file_mime_type) }}</span> @endif
                            @if($doc->file_size_bytes) <span>{{ round($doc->file_size_bytes / 1024, 1) }} KB</span> @endif
                            @if($doc->uploaded_at) <span>Uploaded {{ $doc->uploaded_at->format('M d, Y') }}</span> @endif
                        </div>
                        @if($doc->upload_error)
                            <div class="doc-reason">
                                <i class="fas fa-exclamation-circle" style="flex-shrink:0;margin-top:1px"></i>
                                {{ $doc->upload_error }}
                            </div>
                        @endif
                    </div>
                    <div class="doc-actions">
                        @if($doc->canBeReplaced())
                            <button class="btn btn-sm btn-danger btn-xs"
                                    data-toggle="modal"
                                    data-target="#replaceDocModal"
                                    data-doc-id="{{ $doc->id }}"
                                    data-doc-content="{{ $doc->document_content }}">
                                <i class="fas fa-upload mr-1"></i>Replace
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif

        @if($uploadedDocs->count() > 0)
            <div class="section-divider">
                <i class="fas fa-check-circle text-success"></i> Uploaded Documents
            </div>

            @foreach($uploadedDocs as $doc)
                <div class="doc-item">
                    <div class="doc-icon">
                        <i class="fas fa-file-pdf" style="color:#3f80ea"></i>
                    </div>
                    <div class="doc-info">
                        <div class="doc-name">{{ $doc->file_display_name ?? $doc->document_content }}</div>
                        <div class="doc-meta">
                            <span class="badge badge-success" style="font-size:.65rem">
                                <i class="fas fa-check mr-1"></i>{{ ucfirst(strtolower($doc->status)) }}
                            </span>
                            @if($doc->file_mime_type)
                                <span>{{ strtoupper(explode('/', $doc->file_mime_type)[1] ?? '') }}</span>
                            @endif
                            @if($doc->file_size_bytes)
                                <span>{{ round($doc->file_size_bytes / 1024, 1) }} KB</span>
                            @endif
                            @if($doc->is_system_generated)
                                <span class="text-muted">System generated</span>
                            @elseif($doc->uploaded_at)
                                <span>Uploaded {{ $doc->uploaded_at->format('M d, Y') }}</span>
                            @endif
                            <span class="badge badge-secondary" style="font-size:.6rem">
                                {{ $doc->document_content }}
                            </span>
                        </div>
                    </div>
                    <div class="doc-actions">
                        @if($doc->ndtc_document_id)
                            <a href="{{ route('ndtc.orders.documents.view', [$order, $doc]) }}"
                               class="btn btn-sm btn-outline-secondary btn-xs" target="_blank">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                        @endif
                        @if($doc->canBeReplaced() && !$order->isTerminal())
                            <button class="btn btn-sm btn-outline-primary btn-xs"
                                    data-toggle="modal"
                                    data-target="#replaceDocModal"
                                    data-doc-id="{{ $doc->id }}"
                                    data-doc-content="{{ $doc->document_content }}">
                                <i class="fas fa-upload"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif

        @if(!$order->isTerminal())
            <div class="section-divider mt-3"><i class="fas fa-plus"></i> Add More</div>
            <form action="{{ route('ndtc.orders.documents.store', $order) }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                          <label class="form-label small font-weight-bold">Document type</label>
                          <select class="form-control form-control-sm" name="document_content">
                              <optgroup label="— Common for TNL orders —">
                                  <option value="TITLE_FRONT_AND_BACK">Title Front and Back</option>
                                  <option value="TITLE_FRONT">Title Front only</option>
                                  <option value="TITLE_BACK">Title Back only</option>
                                  <option value="POWER_OF_ATTORNEY">Power of Attorney</option>
                                  <option value="SECURE_ELECTRONIC_POWER_OF_ATTORNEY">Secure Electronic Power of Attorney</option>
                                  <option value="ODOMETER_DISCLOSURE">Odometer Disclosure</option>
                                  <option value="POWER_OF_ATTORNEY_WITH_ODOMETER_DISCLOSURE">POA with Odometer Disclosure</option>
                                  <option value="BILL_OF_SALE">Bill of Sale</option>
                                  <option value="CERTIFICATE_OF_COMPLETION">Certificate of Completion</option>
                              </optgroup>
                              <optgroup label="— Supporting documents —">
                                  <option value="LIEN_RELEASE">Lien Release</option>
                                  <option value="LOAN_AGREEMENT">Loan Agreement</option>
                                  <option value="SECURITY_AGREEMENT">Security Agreement</option>
                                  <option value="TITLE_REASSIGNMENT">Title Reassignment</option>
                                  <option value="VIN_VERIFICATION">VIN Verification</option>
                                  <option value="MANUFACTURER_CERTIFICATE">Manufacturer Certificate</option>
                                  <option value="INSURANCE_SETTLEMENT">Insurance Settlement</option>
                              </optgroup>
                              <optgroup label="— Statements and corrections —">
                                  <option value="STATEMENT_OF_NO_TITLE">Statement of No Title</option>
                                  <option value="STATEMENT_OF_REPOSSESSION">Statement of Repossession</option>
                                  <option value="STATEMENT_OF_MISSING_EVIDENCE">Statement of Missing Evidence</option>
                                  <option value="STATEMENT_OF_IDENTITY">Statement of Identity</option>
                                  <option value="REQUEST_FOR_MISSING_EVIDENCE">Request for Missing Evidence</option>
                                  <option value="LETTER_OF_CORRECTION">Letter of Correction</option>
                                  <option value="ONE_AND_THE_SAME">One and the Same</option>
                                  <option value="DOING_BUSINESS_AS">Doing Business As</option>
                                  <option value="DECLARATION_PAGE">Declaration Page</option>
                              </optgroup>
                              <optgroup label="— Reports —">
                                  <option value="MVR_REPORT">MVR Report</option>
                                  <option value="NMVTIS_REPORT">NMVTIS Report</option>
                              </optgroup>
                              <optgroup label="— Other —">
                                  <option value="OTHER_EVIDENCE">Other Evidence</option>
                              </optgroup>
                          </select>
                          <small class="form-text text-muted">
                              For most Copart / IAAI TNL orders you will only need
                              <strong>Title Front and Back</strong> and <strong>Power of Attorney</strong>.
                          </small>
                      </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="form-label small font-weight-bold">File</label>
                            <input type="file" class="form-control-file" name="document"
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <small class="form-text text-muted">PDF, JPG, PNG · Max 20MB · Min 300 DPI</small>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary btn-sm btn-block">
                                <i class="fas fa-upload mr-1"></i> Upload Document
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif

    </div>{{-- /tab-documents --}}

    {{-- ══ TAB 3: TIMELINE ═══════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-timeline">

        <p class="text-muted mb-3 small">
            <i class="fas fa-info-circle mr-1"></i>
            Complete webhook event log from CHAMP — newest first.
            READY_TO_FINALIZE appears once per document upload.
        </p>

        @forelse($order->webhookLogs as $log)
            <div class="tl-item">
                @if(!$loop->last)
                    <div class="tl-line"></div>
                @endif
                <div class="tl-dot {{ $dotClass($log->event) }}">
                    <i class="fas {{ $dotIcon($log->event) }}"></i>
                </div>
                <div class="tl-body">
                    <div class="tl-event">{{ $log->event }}</div>
                    <div class="tl-time">
                        {{ $log->received_at->format('M d, Y · H:i:s') }} UTC
                        @if($log->ndtc_status)
                            &nbsp;·&nbsp; Status: {{ $log->ndtc_status }}
                        @endif
                    </div>
                    @if(!empty($log->payload['order']['rejections']))
                        <div class="tl-detail">
                            @foreach($log->payload['order']['rejections'] as $r)
                                {{ implode(' ', $r['reasons'] ?? []) }}
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">
                <i class="fas fa-history fa-2x mb-2 d-block"></i>
                No webhook events received yet.
            </div>
        @endforelse

        {{-- Manual entry for order creation --}}
        @if($order->webhookLogs->count() > 0)
            <div class="tl-item">
                <div class="tl-dot t-gray"><i class="fas fa-plus"></i></div>
                <div class="tl-body">
                    <div class="tl-event">Order Created</div>
                    <div class="tl-time">
                        {{ $order->created_at->format('M d, Y · H:i:s') }} UTC
                        &nbsp;·&nbsp; Ref: {{ $order->correlation_id }}
                    </div>
                    <div class="tl-detail">
                        Order created via NDTC API v3. NDTC ID: {{ $order->ndtc_order_id }}
                    </div>
                </div>
            </div>
        @endif

    </div>{{-- /tab-timeline --}}

    {{-- ══ TAB 4: REJECTION (only if rejected) ═══════════════ --}}
    @if($isRejected)
        <div class="tab-pane fade" id="tab-rejection">

            <div class="d-flex align-items-center justify-content-between p-3 mb-3"
                 style="background:#fdf2f2;border:1px solid #f5c6cb;border-radius:.25rem;gap:1rem">
                <div>
                    <div class="font-weight-bold text-danger mb-1">Action Required</div>
                    <div class="small text-muted">
                        Fix the issue below, replace the flagged document in the Documents tab,
                        then resubmit this same order.
                    </div>
                </div>
                <form action="{{ route('ndtc.orders.update', $order) }}" method="POST">
                    @csrf @method('PUT')
                    <button type="submit" class="btn btn-danger flex-shrink-0">
                        <i class="fas fa-paper-plane mr-1"></i>Resubmit Order
                    </button>
                </form>
            </div>

            @forelse($rejections as $i => $rejection)
                <div class="rejection-block">
                    <div class="rejection-title">
                        <i class="fas fa-exclamation-circle"></i>
                        Rejection {{ $i + 1 }}
                        @if(!empty($rejection['element']))
                            &nbsp;·&nbsp; Source: {{ $rejection['element'] }}
                        @endif
                        @if(!empty($rejection['code']))
                            &nbsp;·&nbsp; Code: {{ $rejection['code'] }}
                        @endif
                    </div>
                    <div class="rejection-text">{{ implode("\n", $rejection['reasons'] ?? ['No reason provided']) }}</div>
                </div>
            @empty
                <div class="alert alert-warning">No detailed rejection reasons available. Check the Timeline tab for raw webhook data.</div>
            @endforelse

            <div class="section-card mt-3">
                <div class="card-header">
                    <div class="header-icon" style="background:#dbeafe;color:#1d4ed8">
                        <i class="fas fa-list-ol"></i>
                    </div>
                    <h6>How to fix this</h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-start mb-2" style="gap:.75rem">
                        <div style="width:22px;height:22px;border-radius:50%;background:#3f80ea;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0">1</div>
                        <div class="small">Identify the issue from the rejection reason above.</div>
                    </div>
                    <div class="d-flex align-items-start mb-2" style="gap:.75rem">
                        <div style="width:22px;height:22px;border-radius:50%;background:#3f80ea;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0">2</div>
                        <div class="small">
                            If a document was rejected — go to the
                            <a href="#tab-documents" data-toggle="tab"><strong>Documents tab</strong></a>
                            and click <strong>Replace</strong>.
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-2" style="gap:.75rem">
                        <div style="width:22px;height:22px;border-radius:50%;background:#3f80ea;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0">3</div>
                        <div class="small">
                            If order data was wrong — click
                            <a href="{{ route('ndtc.orders.edit', $order) }}"><strong>Edit Order</strong></a>
                            to correct the fields.
                        </div>
                    </div>
                    <div class="d-flex align-items-start" style="gap:.75rem">
                        <div style="width:22px;height:22px;border-radius:50%;background:#3f80ea;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0">4</div>
                        <div class="small">Click <strong>Resubmit Order</strong> above.</div>
                    </div>
                    <div class="alert alert-info mt-3 mb-0 small">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Do not create a new order.</strong>
                        Always resubmit this same order — a new order for the same VIN will be blocked.
                    </div>
                </div>
            </div>

        </div>{{-- /tab-rejection --}}
    @endif

    {{-- ══ TAB 5: REJECTION HISTORY ══════════════════════════ --}}
    @if($order->rejectionHistory->count() > 0)
        <div class="tab-pane fade" id="tab-history">

            <p class="text-muted mb-3 small">
                <i class="fas fa-info-circle mr-1"></i>
                Permanent record of all DMV decisions across every submission.
            </p>

            @foreach($order->rejectionHistory->sortByDesc('rejected_at') as $history)
                <div class="section-card mb-3">
                    <div class="card-header" style="background:#fdf2f2">
                        <div class="header-icon" style="background:#f5c6cb;color:#d9534f">
                            <i class="fas fa-times"></i>
                        </div>
                        <h6 class="text-danger">Submission #{{ $history->submission_number }} — Rejected</h6>
                        <span class="ml-auto text-muted" style="font-size:.72rem">
                            {{ $history->rejected_at->format('M d, Y · H:i') }} UTC
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-row">
                                    <span class="info-key">NDTC status</span>
                                    <span class="info-val">
                                        <span class="badge badge-danger" style="font-size:.65rem">
                                            {{ $history->ndtc_status ?? 'REJECTED' }}
                                        </span>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-key">Rejected at</span>
                                    <span class="info-val">{{ $history->rejected_at->format('M d, Y H:i') }} UTC</span>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="small text-muted font-weight-bold mb-1">Rejection reasons:</div>
                                @foreach($history->rejection_reasons ?? [] as $r)
                                    <div class="rejection-text mb-1" style="font-size:.75rem">
                                        {{ implode("\n", $r['reasons'] ?? ['No reason provided']) }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>{{-- /tab-history --}}
    @endif

    <div class="tab-pane fade" id="tab-archive">
        <div class="d-flex align-items-start gap-3 p-4 border rounded-3 bg-light-subtle">
            <div class="d-flex align-items-center justify-content-center rounded-circle bg-danger-subtle"
                style="width: 40px; height: 40px; flex-shrink: 0;">
                <i class="fas fa-archive text-danger"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1">Archive this order</h6>
                <p class="text-muted small mb-3">
                    This will hide the order from the active list. It won't be permanently deleted.
                </p>
                <form action="{{ route('ndtc.orders.archive', $order) }}" method="POST"
                    onsubmit="return confirm('Archive this order? It will be hidden from the active list but not permanently deleted.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-archive me-1"></i> Archive Order
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>{{-- /tab-content --}}

{{-- Replace document modal --}}
@if(!$order->isTerminal())
<div class="modal fade" id="replaceDocModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="" method="POST" enctype="multipart/form-data" id="replaceDocForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Replace Document</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        The existing document will be deleted from CHAMP and replaced with your new file.
                    </div>
                    <div class="form-group">
                        <label class="form-label font-weight-bold">New file</label>
                        <input type="file" class="form-control-file" name="document"
                               accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="form-text text-muted">Min 300 DPI. Color scan. Max 20MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-upload mr-1"></i>Replace Document
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif




@endsection

@section('scripts')
<script>
$(document).ready(function () {

    // Wire replace document modal to correct endpoint
    $('#replaceDocModal').on('show.bs.modal', function (e) {
        var docId = $(e.relatedTarget).data('doc-id');
        var baseUrl = '{{ route('ndtc.orders.documents.replace', [$order, '__DOC__']) }}';
        $('#replaceDocForm').attr('action', baseUrl.replace('__DOC__', docId));
    });

    // Switch to docs tab from alert link
    $('a[href="#tab-documents"]').on('click', function (e) {
        e.preventDefault();
        $('#orderTabs a[href="#tab-documents"]').tab('show');
    });

    // Poll for status update when order is in a transient state


    @if(!in_array($order->status, $terminalStatuses))
        var currentStatus = '{{ $order->status }}';
        var pollInterval = setInterval(function () {
            $.get('{{ route('ndtc.orders.status', $order) }}', function (data) {
                if (data.status !== currentStatus) {
                    clearInterval(pollInterval);
                    window.location.reload();
                }
            });
        }, 30000); // poll every 2 seconds
    @endif

});
</script>
@endsection
