{{-- resources/views/ndtc/orders/create.blade.php --}}

@extends('layouts.app')

@section('title', 'Create NDTC Order')

@section('styles')
<style>
/* ── TOPBAR ── */
.topbar{height:58px;background:#fff;border-bottom:1px solid #dee2e6;display:flex;align-items:center;justify-content:space-between;padding:0 1.25rem;position:fixed;top:0;left:240px;right:0;z-index:99;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.breadcrumb{margin:0;background:transparent;padding:0;font-size:.8rem}
.breadcrumb-item a{color:#3b7ddd;text-decoration:none}
.breadcrumb-item a:hover{text-decoration:underline}

/* ── MAIN ── */
.main{margin-left:240px;padding-top:58px}
.page-content{padding:1.5rem}

/* ── HERO ── */
.order-hero{background:linear-gradient(135deg,#0f1b2d 0%,#1e3a5f 100%);border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1.25rem}
.hero-vin{font-family:"Courier New",monospace;font-size:.85rem;letter-spacing:.08em;color:rgba(255,255,255,.45);margin-bottom:3px}
.hero-name{font-size:1.3rem;font-weight:600;color:#fff;line-height:1.2}
.hero-meta{display:flex;flex-wrap:wrap;gap:12px;margin-top:8px}
.hero-meta-item{font-size:.75rem;color:rgba(255,255,255,.45);display:flex;align-items:center;gap:4px}
.txn-pill{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);border-radius:5px;padding:2px 9px;font-size:.7rem;font-weight:600;color:rgba(255,255,255,.75)}

/* ── PIPELINE ── */
.pipeline-wrap{background:#fff;border:1px solid #dee2e6;border-radius:8px;padding:.875rem 1rem;margin-bottom:1.25rem;overflow-x:auto}
.pipeline{display:flex;align-items:center;min-width:500px}
.pipe-step{display:flex;flex-direction:column;align-items:center;gap:4px;flex:1;position:relative}
.pipe-step:not(:last-child)::after{content:'';position:absolute;top:12px;left:calc(50% + 15px);right:calc(-50% + 15px);height:1.5px;background:#dee2e6}
.pipe-step.done::after{background:#3b7ddd}
.pipe-step.err::after{background:#dc3545}
.pipe-dot{width:26px;height:26px;border-radius:50%;border:2px solid #dee2e6;background:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;color:#adb5bd;z-index:1;flex-shrink:0}
.pipe-step.done .pipe-dot{background:#3b7ddd;border-color:#3b7ddd;color:#fff}
.pipe-step.active .pipe-dot{background:#fff;border-color:#3b7ddd;color:#3b7ddd;box-shadow:0 0 0 3px rgba(59,125,221,.15)}
.pipe-step.err .pipe-dot{background:#fdf2f2;border-color:#dc3545;color:#dc3545}
.pipe-step.wait .pipe-dot{background:#f8f9fa;border-color:#dee2e6;color:#adb5bd}
.pipe-label{font-size:.65rem;color:#adb5bd;text-align:center;white-space:nowrap;font-weight:500;line-height:1.2}
.pipe-step.done .pipe-label{color:#3b7ddd}
.pipe-step.active .pipe-label{color:#3b7ddd;font-weight:600}
.pipe-step.err .pipe-label{color:#dc3545}

/* ── STAT CARDS ── */
.stat-card{background:#fff;border:1px solid #dee2e6;border-radius:8px;padding:.875rem 1rem}
.stat-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:#adb5bd;font-weight:600;margin-bottom:4px}
.stat-value{font-size:1.15rem;font-weight:600;color:#212529;line-height:1}
.stat-sub{font-size:.7rem;color:#6c757d;margin-top:3px}

/* ── SECTION CARD ── */
.section-card{background:#fff;border:1px solid #dee2e6;border-radius:8px;margin-bottom:1rem}
.section-card .card-header{background:#fff;border-bottom:1px solid #dee2e6;padding:.75rem 1rem;display:flex;align-items:center;gap:8px;border-radius:8px 8px 0 0}
.section-card .card-header h6{font-size:.8rem;font-weight:600;color:#212529;margin:0}
.section-card .card-header .header-icon{width:26px;height:26px;border-radius:5px;display:flex;align-items:center;justify-content:center;font-size:.75rem;flex-shrink:0}
.section-card .card-body{padding:1rem}

/* ── INFO ROWS ── */
.info-row{display:flex;justify-content:space-between;align-items:flex-start;padding:5px 0;border-bottom:1px solid #f5f7fb;gap:8px}
.info-row:last-child{border-bottom:none;padding-bottom:0}
.info-key{font-size:.75rem;color:#6c757d;flex-shrink:0;max-width:45%}
.info-val{font-size:.78rem;color:#212529;font-weight:500;text-align:right;word-break:break-all}
.info-val.mono{font-family:"Courier New",monospace;font-size:.7rem;letter-spacing:.04em}

/* ── ENTITY CARDS ── */
.entity-card{background:#f8f9fa;border:1px solid #dee2e6;border-radius:7px;padding:.75rem}
.entity-type{font-size:.62rem;text-transform:uppercase;letter-spacing:.08em;color:#adb5bd;font-weight:600;margin-bottom:3px}
.entity-name{font-size:.8rem;font-weight:600;color:#212529}
.entity-addr{font-size:.72rem;color:#6c757d;margin-top:3px;line-height:1.5}

/* ── TABS ── */
.nav-tabs{border-bottom:1px solid #dee2e6;background:#fff;border-radius:8px 8px 0 0;padding:0 1rem}
.nav-tabs .nav-link{font-size:.8rem;font-weight:500;color:#6c757d;border:none;border-bottom:2px solid transparent;padding:.65rem .875rem;margin-bottom:-1px;display:flex;align-items:center;gap:5px}
.nav-tabs .nav-link:hover{color:#212529;border-bottom-color:#dee2e6}
.nav-tabs .nav-link.active{color:#3b7ddd;border-bottom-color:#3b7ddd;background:transparent}
.tab-content{background:#fff;border:1px solid #dee2e6;border-top:none;border-radius:0 0 8px 8px;padding:1.25rem}

/* ── BADGES ── */
.badge-status{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:12px;font-size:.72rem;font-weight:600}
.badge-rejected{background:#fdf2f2;color:#dc3545}
.badge-approved{background:#f0faf4;color:#28a745}
.badge-processing{background:#eff6ff;color:#3b7ddd}
.badge-draft{background:#f8f9fa;color:#6c757d;border:1px solid #dee2e6}
.badge-review{background:#fff8e1;color:#856404}
.badge-hold{background:#fff8e1;color:#856404}
.badge-rtf{background:#f0faf4;color:#28a745}

/* ── DOCUMENTS ── */
.doc-item{display:flex;align-items:center;gap:10px;padding:.75rem;background:#f8f9fa;border:1px solid #dee2e6;border-radius:7px;margin-bottom:.5rem;transition:border-color .15s}
.doc-item:hover{border-color:#adb5bd}
.doc-item.doc-rejected{background:#fdf2f2;border-color:#f5c6cb}
.doc-icon{width:34px;height:34px;border-radius:6px;background:#fff;border:1px solid #dee2e6;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.doc-icon.rejected{background:#fdf2f2;border-color:#f5c6cb}
.doc-info{flex:1;min-width:0}
.doc-name{font-size:.8rem;font-weight:600;color:#212529;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.doc-meta{font-size:.7rem;color:#6c757d;margin-top:2px;display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.doc-reason{font-size:.72rem;color:#dc3545;margin-top:4px;display:flex;align-items:flex-start;gap:4px}
.doc-actions{display:flex;gap:5px;flex-shrink:0}
.doc-upload-zone{border:2px dashed #dee2e6;border-radius:7px;padding:1.25rem;text-align:center;color:#adb5bd;font-size:.8rem;cursor:pointer;transition:all .15s}
.doc-upload-zone:hover{border-color:#3b7ddd;color:#3b7ddd;background:#eff6ff}

/* ── TIMELINE ── */
.tl-item{display:flex;gap:10px;padding-bottom:1rem;position:relative}
.tl-item:not(:last-child) .tl-line{position:absolute;left:14px;top:28px;bottom:0;width:1px;background:#dee2e6}
.tl-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;flex-shrink:0;z-index:1;border:1px solid #dee2e6;background:#f8f9fa}
.tl-dot.t-red{background:#fdf2f2;border-color:#f5c6cb;color:#dc3545}
.tl-dot.t-blue{background:#eff6ff;border-color:#bbd6fb;color:#3b7ddd}
.tl-dot.t-green{background:#f0faf4;border-color:#b8e0c4;color:#28a745}
.tl-dot.t-gray{background:#f8f9fa;border-color:#dee2e6;color:#adb5bd}
.tl-dot.t-amber{background:#fff8e1;border-color:#ffeaa7;color:#856404}
.tl-body{flex:1;padding-top:3px}
.tl-event{font-size:.8rem;font-weight:600;color:#212529}
.tl-time{font-size:.7rem;color:#adb5bd;margin-top:1px}
.tl-detail{font-size:.75rem;color:#495057;margin-top:5px;background:#f8f9fa;border:1px solid #dee2e6;border-radius:5px;padding:6px 9px;line-height:1.55}

/* ── REJECTION ── */
.rejection-block{background:#fdf2f2;border:1px solid #f5c6cb;border-radius:7px;padding:.875rem;margin-bottom:.75rem}
.rejection-title{font-size:.8rem;font-weight:600;color:#dc3545;margin-bottom:5px;display:flex;align-items:center;gap:5px}
.rejection-text{font-size:.78rem;color:#212529;line-height:1.6;background:#fff;border-radius:4px;padding:.5rem .75rem;white-space:pre-wrap;border:1px solid #f5c6cb}

/* ── MISC ── */
.section-divider{font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#adb5bd;padding:4px 0;display:flex;align-items:center;gap:8px;margin-bottom:.75rem}
.section-divider::after{content:'';flex:1;height:1px;background:#f0f0f0}
.btn-xs{padding:.2rem .55rem;font-size:.72rem}
.action-bar{background:#fff;border:1px solid #dee2e6;border-radius:8px;padding:.875rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between}
.action-bar-left{display:flex;align-items:center;gap:.5rem}
.action-bar-right{display:flex;align-items:center;gap:.5rem}
.submission-counter{font-size:.72rem;color:#6c757d;background:#f8f9fa;border:1px solid #dee2e6;border-radius:5px;padding:3px 8px}
.empty-tab{text-align:center;padding:2rem;color:#adb5bd}
.empty-tab i{font-size:2rem;display:block;margin-bottom:.75rem}
</style>
@endsection

@section('content')

<div class="main">
<div class="page-content">

  <!-- ══ PAGE HEADING ═══════════════════════════════════════════ -->
  <div class="d-flex align-items-start justify-content-between mb-3">
    <div>
      <h1 class="h4 mb-1">Order Detail</h1>
      <p class="text-muted mb-0" style="font-size:.8rem">
        NDTC ID: <code>68b9ce9f048f613ebb46f8e2</code>
        &nbsp;·&nbsp; Internal ref: <code>LOT-47821903</code>
      </p>
    </div>
    <a href="#" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-arrow-left mr-1"></i> Back to Orders
    </a>
  </div>

  <!-- ══ REJECTION ALERT ════════════════════════════════════════ -->
  <div class="alert alert-danger d-flex align-items-start mb-3" style="border-radius:8px">
    <i class="fas fa-exclamation-circle mr-2 mt-1" style="flex-shrink:0"></i>
    <div>
      <strong>DMV rejected this order.</strong>
      The title scan was rejected due to poor image quality.
      Replace the flagged document in the <a href="#" class="alert-link" onclick="switchTab('docs')">Documents tab</a>,
      then resubmit the order. <strong>Do not create a new order.</strong>
    </div>
  </div>

  <!-- ══ HERO BANNER ════════════════════════════════════════════ -->
  <div class="order-hero mb-3">
    <div class="d-flex align-items-start justify-content-between">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1" style="gap:.5rem">
          <span class="badge-status badge-rejected">
            <i class="fas fa-times"></i> Rejected — Action Required
          </span>
          <span class="txn-pill">TNL</span>
        </div>
        <div class="hero-vin">VIN: 1FA6P8R09R5505113</div>
        <div class="hero-name">2024 Ford Mustang Dark Horse</div>
        <div class="hero-meta">
          <span class="hero-meta-item"><i class="fas fa-hashtag"></i> 68b9ce9f…f8e2</span>
          <span class="hero-meta-item"><i class="fas fa-link"></i> LOT-47821903</span>
          <span class="hero-meta-item"><i class="fas fa-calendar"></i> Transfer: Jan 18, 2024</span>
          <span class="hero-meta-item"><i class="fas fa-map-marker-alt"></i> Title state: NJ</span>
          <span class="hero-meta-item"><i class="fas fa-file-alt"></i> Title #: NJ12345678</span>
        </div>
      </div>
      <div class="d-flex flex-column align-items-end" style="gap:.5rem">
        <div class="d-flex" style="gap:.4rem">
          <button class="btn btn-sm btn-danger">
            <i class="fas fa-paper-plane mr-1"></i>Resubmit Order
          </button>
          <button class="btn btn-sm btn-outline-light">
            <i class="fas fa-ban mr-1"></i>Cancel
          </button>
          <button class="btn btn-sm btn-outline-light">
            <i class="fas fa-sync-alt mr-1"></i>Refresh
          </button>
        </div>
        <span style="font-size:.68rem;color:rgba(255,255,255,.35)">Submission #1 · Rejection #1</span>
      </div>
    </div>
  </div>

  <!-- ══ PIPELINE ═══════════════════════════════════════════════ -->
  <div class="pipeline-wrap">
    <div class="pipeline">
      <div class="pipe-step done">
        <div class="pipe-dot"><i class="fas fa-check"></i></div>
        <div class="pipe-label">Created</div>
      </div>
      <div class="pipe-step done">
        <div class="pipe-dot"><i class="fas fa-check"></i></div>
        <div class="pipe-label">Ready for<br>Documents</div>
      </div>
      <div class="pipe-step done">
        <div class="pipe-dot"><i class="fas fa-check"></i></div>
        <div class="pipe-label">Docs<br>Uploaded</div>
      </div>
      <div class="pipe-step done">
        <div class="pipe-dot"><i class="fas fa-check"></i></div>
        <div class="pipe-label">Finalized</div>
      </div>
      <div class="pipe-step done">
        <div class="pipe-dot"><i class="fas fa-check"></i></div>
        <div class="pipe-label">Processing</div>
      </div>
      <div class="pipe-step err">
        <div class="pipe-dot"><i class="fas fa-times"></i></div>
        <div class="pipe-label">Rejected</div>
      </div>
      <div class="pipe-step wait">
        <div class="pipe-dot">–</div>
        <div class="pipe-label">Approved</div>
      </div>
    </div>
  </div>

  <!-- ══ STAT CARDS ═════════════════════════════════════════════ -->
  <div class="row mb-3">
    <div class="col-md-3 mb-2">
      <div class="stat-card">
        <div class="stat-label">Status</div>
        <div class="stat-value" style="font-size:.9rem;margin-top:3px">
          <span class="badge-status badge-rejected"><i class="fas fa-times"></i> Rejected</span>
        </div>
        <div class="stat-sub">MANUALLY_REJECTED by DMV</div>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="stat-card">
        <div class="stat-label">Submission count</div>
        <div class="stat-value">1</div>
        <div class="stat-sub">First submission</div>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="stat-card">
        <div class="stat-label">Rejection count</div>
        <div class="stat-value" style="color:#dc3545">1</div>
        <div class="stat-sub">Rejected Jan 22, 2024</div>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="stat-card">
        <div class="stat-label">Transfer date</div>
        <div class="stat-value" style="font-size:.95rem">Jan 18, 2024</div>
        <div class="stat-sub">6 days ago</div>
      </div>
    </div>
  </div>

  <!-- ══ TABS ═══════════════════════════════════════════════════ -->
  <ul class="nav nav-tabs" id="orderTabs">
    <li class="nav-item">
      <a class="nav-link active" href="#tab-overview" data-toggle="tab">
        <i class="fas fa-info-circle mr-1"></i>Overview
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#tab-documents" data-toggle="tab" id="docs-tab">
        <i class="fas fa-folder mr-1"></i>Documents
        <span class="badge badge-danger ml-1" style="font-size:.6rem">1</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#tab-timeline" data-toggle="tab">
        <i class="fas fa-history mr-1"></i>Timeline
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#tab-rejection" data-toggle="tab">
        <i class="fas fa-exclamation-circle mr-1"></i>Rejection
        <span class="badge badge-danger ml-1" style="font-size:.6rem">!</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#tab-history" data-toggle="tab">
        <i class="fas fa-clock mr-1"></i>Rejection History
      </a>
    </li>
  </ul>

  <div class="tab-content">

    <!-- ══ TAB 1: OVERVIEW ══════════════════════════════════════ -->
    <div class="tab-pane fade show active" id="tab-overview">

      <!-- Entities -->
      <div class="section-divider"><i class="fas fa-building"></i> Entities</div>
      <div class="row mb-3">
        <div class="col-md-4 mb-2">
          <div class="entity-card">
            <div class="entity-type"><i class="fas fa-building mr-1"></i> Acquiring entity</div>
            <div class="entity-name">AutoRetail NJ LLC</div>
            <div class="entity-addr">890 Main Street, Suite 200<br>Newark, NJ 07102<br>NRB #00247</div>
          </div>
        </div>
        <div class="col-md-4 mb-2">
          <div class="entity-card">
            <div class="entity-type"><i class="fas fa-gavel mr-1"></i> Disposing entity (Auction)</div>
            <div class="entity-name">Copart Inc.</div>
            <div class="entity-addr">123 Auction Drive<br>Linden, NJ 07036<br>Ad-hoc (no pre-registration)</div>
          </div>
        </div>
        <div class="col-md-4 mb-2">
          <div class="entity-card">
            <div class="entity-type"><i class="fas fa-shield-alt mr-1"></i> Title work entity</div>
            <div class="entity-name">AutoRetail NJ LLC</div>
            <div class="entity-addr">Agent: Jane Doe<br>jane@autoretail.com<br>Role: AGENT</div>
          </div>
        </div>
      </div>

      <!-- Vehicle + Title -->
      <div class="section-divider"><i class="fas fa-car"></i> Vehicle & Title</div>
      <div class="row mb-3">
        <div class="col-md-6 mb-2">
          <div class="section-card h-100">
            <div class="card-header">
              <div class="header-icon" style="background:#dbeafe;color:#1d4ed8"><i class="fas fa-car"></i></div>
              <h6>Vehicle Details</h6>
            </div>
            <div class="card-body p-3">
              <div class="info-row"><span class="info-key">VIN</span><span class="info-val mono">1FA6P8R09R5505113</span></div>
              <div class="info-row"><span class="info-key">Year</span><span class="info-val">2024</span></div>
              <div class="info-row"><span class="info-key">Make</span><span class="info-val">FORD (FORD)</span></div>
              <div class="info-row"><span class="info-key">Model</span><span class="info-val">Mustang Dark Horse</span></div>
              <div class="info-row"><span class="info-key">Body style</span><span class="info-val">CP — Coupe</span></div>
              <div class="info-row"><span class="info-key">Vehicle class</span><span class="info-val">Cars & Trucks</span></div>
              <div class="info-row"><span class="info-key">Fuel type</span><span class="info-val">GAS</span></div>
              <div class="info-row"><span class="info-key">Weight</span><span class="info-val">3,849 LBS</span></div>
            </div>
          </div>
        </div>
        <div class="col-md-6 mb-2">
          <div class="section-card h-100">
            <div class="card-header">
              <div class="header-icon" style="background:#dcfce7;color:#15803d"><i class="fas fa-file-certificate"></i></div>
              <h6>Existing Title</h6>
            </div>
            <div class="card-body p-3">
              <div class="info-row"><span class="info-key">Title number</span><span class="info-val mono">NJ12345678</span></div>
              <div class="info-row"><span class="info-key">Issuing state</span><span class="info-val">New Jersey (NJ)</span></div>
              <div class="info-row"><span class="info-key">Title type</span><span class="info-val">Paper</span></div>
              <div class="info-row">
                <span class="info-key">Title brands</span>
                <span class="info-val">
                  <span class="badge badge-warning" style="font-size:.65rem">SALVAGE</span>
                </span>
              </div>
              <div class="info-row"><span class="info-key">Odometer</span><span class="info-val">1,240 MI</span></div>
              <div class="info-row"><span class="info-key">Odometer condition</span><span class="info-val">ACTUAL</span></div>
              <div class="info-row"><span class="info-key">Odometer date</span><span class="info-val">Jan 18, 2024</span></div>
              <div class="info-row"><span class="info-key">Transfer date</span><span class="info-val">Jan 18, 2024</span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Details -->
      <div class="section-divider"><i class="fas fa-cog"></i> Order Details</div>
      <div class="row">
        <div class="col-md-6 mb-2">
          <div class="section-card">
            <div class="card-header">
              <div class="header-icon" style="background:#f3e8ff;color:#7c3aed"><i class="fas fa-info-circle"></i></div>
              <h6>Order Information</h6>
            </div>
            <div class="card-body p-3">
              <div class="info-row"><span class="info-key">NDTC order ID</span><span class="info-val mono">68b9ce9f048f613ebb46f8e2</span></div>
              <div class="info-row"><span class="info-key">Correlation ID</span><span class="info-val mono">1FA6P8R09R5505113-20240118</span></div>
              <div class="info-row"><span class="info-key">Transaction type</span><span class="info-val">TNL — Transfer No Lien</span></div>
              <div class="info-row"><span class="info-key">Vertical</span><span class="info-val">NATIONAL_RETAILER</span></div>
              <div class="info-row"><span class="info-key">Created by</span><span class="info-val">Jane Doe</span></div>
              <div class="info-row"><span class="info-key">Created at</span><span class="info-val">Jan 18, 2024 17:40 UTC</span></div>
              <div class="info-row"><span class="info-key">Finalized at</span><span class="info-val">Jan 20, 2024 09:11 UTC</span></div>
            </div>
          </div>
        </div>
        <div class="col-md-6 mb-2">
          <div class="section-card">
            <div class="card-header">
              <div class="header-icon" style="background:#fef9c3;color:#854d0e"><i class="fas fa-exclamation-triangle"></i></div>
              <h6>Current Rejection Summary</h6>
            </div>
            <div class="card-body p-3">
              <div class="info-row"><span class="info-key">NDTC status</span><span class="info-val"><span class="badge badge-danger" style="font-size:.65rem">MANUALLY_REJECTED</span></span></div>
              <div class="info-row"><span class="info-key">Rejected at</span><span class="info-val">Jan 22, 2024 14:33 UTC</span></div>
              <div class="info-row"><span class="info-key">Source</span><span class="info-val">STATE (WV DMV)</span></div>
              <div class="info-row"><span class="info-key">Rejection count</span><span class="info-val text-danger font-weight-bold">1</span></div>
              <div class="info-row"><span class="info-key">Submission count</span><span class="info-val">1</span></div>
              <div class="mt-2">
                <a href="#tab-rejection" class="btn btn-sm btn-outline-danger btn-block" data-toggle="tab">
                  <i class="fas fa-exclamation-circle mr-1"></i>View Rejection Details
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /tab-overview -->

    <!-- ══ TAB 2: DOCUMENTS ══════════════════════════════════════ -->
    <div class="tab-pane fade" id="tab-documents">

      <div class="alert alert-warning d-flex align-items-start mb-3">
        <i class="fas fa-exclamation-triangle mr-2 mt-1" style="flex-shrink:0"></i>
        <div>
          <strong>1 document requires attention.</strong>
          The title scan was rejected — replace it with a new high-resolution scan
          before resubmitting the order.
        </div>
      </div>

      <!-- Rejected document -->
      <div class="section-divider"><i class="fas fa-exclamation-circle text-danger"></i> Requires Action</div>
      <div class="doc-item doc-rejected mb-3">
        <div class="doc-icon rejected">
          <i class="fas fa-file-pdf" style="color:#dc3545"></i>
        </div>
        <div class="doc-info">
          <div class="doc-name">Front and Back of Title</div>
          <div class="doc-meta">
            <span class="badge badge-danger" style="font-size:.65rem"><i class="fas fa-times mr-1"></i>Rejected</span>
            <span>PDF · 16.3 KB</span>
            <span><i class="fas fa-calendar mr-1"></i>Uploaded Jan 18, 2024</span>
            <span class="text-muted">ID: ecbdc4a7…39b</span>
          </div>
          <div class="doc-reason">
            <i class="fas fa-exclamation-circle" style="flex-shrink:0;margin-top:1px"></i>
            DMV rejection: "Evidence Illegible — Bad image. Re-scan at 300 DPI or above in color."
          </div>
        </div>
        <div class="doc-actions">
          <button class="btn btn-sm btn-danger btn-xs">
            <i class="fas fa-upload mr-1"></i>Replace
          </button>
          <button class="btn btn-sm btn-outline-secondary btn-xs">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <!-- Accepted documents -->
      <div class="section-divider"><i class="fas fa-check-circle text-success"></i> Uploaded Documents</div>

      <div class="doc-item">
        <div class="doc-icon"><i class="fas fa-file-pdf" style="color:#3b7ddd"></i></div>
        <div class="doc-info">
          <div class="doc-name">Nonresident Business Title Assignment</div>
          <div class="doc-meta">
            <span class="badge badge-success" style="font-size:.65rem"><i class="fas fa-check mr-1"></i>Uploaded</span>
            <span>PDF · 1.9 MB</span>
            <span>System generated</span>
            <span class="badge badge-secondary" style="font-size:.6rem">CLEARINGHOUSE_TITLE_APPLICATION</span>
          </div>
        </div>
        <div class="doc-actions">
          <button class="btn btn-sm btn-outline-secondary btn-xs">
            <i class="fas fa-eye mr-1"></i>View
          </button>
        </div>
      </div>

      <div class="doc-item">
        <div class="doc-icon"><i class="fas fa-file-pdf" style="color:#3b7ddd"></i></div>
        <div class="doc-info">
          <div class="doc-name">Certificate of Completion</div>
          <div class="doc-meta">
            <span class="badge badge-success" style="font-size:.65rem"><i class="fas fa-check mr-1"></i>Uploaded</span>
            <span>PDF</span>
            <span><i class="fas fa-calendar mr-1"></i>Uploaded Jan 18, 2024</span>
            <span class="badge badge-secondary" style="font-size:.6rem">CERTIFICATE_OF_COMPLETION</span>
          </div>
        </div>
        <div class="doc-actions">
          <button class="btn btn-sm btn-outline-secondary btn-xs">
            <i class="fas fa-eye mr-1"></i>View
          </button>
          <button class="btn btn-sm btn-outline-primary btn-xs">
            <i class="fas fa-upload"></i>
          </button>
        </div>
      </div>

      <div class="doc-item">
        <div class="doc-icon"><i class="fas fa-file-pdf" style="color:#3b7ddd"></i></div>
        <div class="doc-info">
          <div class="doc-name">CADE Cover Page</div>
          <div class="doc-meta">
            <span class="badge badge-success" style="font-size:.65rem"><i class="fas fa-check mr-1"></i>Uploaded</span>
            <span>PDF</span>
            <span>System generated</span>
            <span class="badge badge-secondary" style="font-size:.6rem">CADE_COVER_PAGE</span>
          </div>
        </div>
        <div class="doc-actions">
          <button class="btn btn-sm btn-outline-secondary btn-xs">
            <i class="fas fa-eye mr-1"></i>View
          </button>
        </div>
      </div>

      <!-- Add document -->
      <div class="section-divider mt-3"><i class="fas fa-plus"></i> Add More</div>
      <div class="doc-upload-zone" onclick="alert('File picker would open here')">
        <i class="fas fa-cloud-upload-alt fa-2x mb-2 d-block"></i>
        Click to add an additional document (POA, Odometer Disclosure, etc.)
        <div style="font-size:.7rem;margin-top:4px">PDF, JPG, PNG · Max 20MB · Min 300 DPI</div>
      </div>

    </div><!-- /tab-documents -->

    <!-- ══ TAB 3: TIMELINE ════════════════════════════════════════ -->
    <div class="tab-pane fade" id="tab-timeline">

      <p class="text-muted mb-3" style="font-size:.78rem">
        <i class="fas fa-info-circle mr-1"></i>
        Complete webhook event log from CHAMP — newest first.
        READY_TO_FINALIZE can appear multiple times, once per document upload.
      </p>

      <div class="tl-item">
        <div class="tl-line"></div>
        <div class="tl-dot t-red"><i class="fas fa-times"></i></div>
        <div class="tl-body">
          <div class="tl-event">ORDER_REJECTED</div>
          <div class="tl-time">Jan 22, 2024 · 14:33:07 UTC &nbsp;·&nbsp; Status: MANUALLY_REJECTED</div>
          <div class="tl-detail">Source: STATE (WV DMV)<br>
"The transfer agreement was rejected due to Evidence Illegible. Note: Bad image — please re-scan at 300 DPI or above in color."</div>
        </div>
      </div>

      <div class="tl-item">
        <div class="tl-line"></div>
        <div class="tl-dot t-blue"><i class="fas fa-spinner"></i></div>
        <div class="tl-body">
          <div class="tl-event">PROCESSING</div>
          <div class="tl-time">Jan 20, 2024 · 09:12:44 UTC &nbsp;·&nbsp; Status: PROCESSING</div>
          <div class="tl-detail">Order passed initial validation and is now processing with the WV DMV.</div>
        </div>
      </div>

      <div class="tl-item">
        <div class="tl-line"></div>
        <div class="tl-dot t-green"><i class="fas fa-check"></i></div>
        <div class="tl-body">
          <div class="tl-event">READY_TO_FINALIZE</div>
          <div class="tl-time">Jan 18, 2024 · 17:52:01 UTC</div>
          <div class="tl-detail">Certificate of Completion uploaded. All required documents present.</div>
        </div>
      </div>

      <div class="tl-item">
        <div class="tl-line"></div>
        <div class="tl-dot t-green"><i class="fas fa-check"></i></div>
        <div class="tl-body">
          <div class="tl-event">READY_TO_FINALIZE</div>
          <div class="tl-time">Jan 18, 2024 · 17:48:30 UTC</div>
          <div class="tl-detail">Title front and back uploaded. All required documents present.</div>
        </div>
      </div>

      <div class="tl-item">
        <div class="tl-line"></div>
        <div class="tl-dot t-gray"><i class="fas fa-file"></i></div>
        <div class="tl-body">
          <div class="tl-event">READY_FOR_DOCUMENTS</div>
          <div class="tl-time">Jan 18, 2024 · 17:41:12 UTC</div>
          <div class="tl-detail">Order initialized. System ready to accept document uploads.</div>
        </div>
      </div>

      <div class="tl-item">
        <div class="tl-dot t-gray"><i class="fas fa-plus"></i></div>
        <div class="tl-body">
          <div class="tl-event">Order Created</div>
          <div class="tl-time">Jan 18, 2024 · 17:40:05 UTC &nbsp;·&nbsp; Ref: LOT-47821903</div>
          <div class="tl-detail">Order created via NDTC API v3. NDTC ID: 68b9ce9f048f613ebb46f8e2</div>
        </div>
      </div>

    </div><!-- /tab-timeline -->

    <!-- ══ TAB 4: REJECTION ═══════════════════════════════════════ -->
    <div class="tab-pane fade" id="tab-rejection">

      <!-- Resubmit banner -->
      <div class="d-flex align-items-center justify-content-between p-3 mb-3"
           style="background:#fdf2f2;border:1px solid #f5c6cb;border-radius:8px;gap:1rem">
        <div>
          <div class="font-weight-bold text-danger mb-1">Action Required</div>
          <div style="font-size:.78rem;color:#495057">
            Fix the issue below, replace the flagged document in the Documents tab,
            then resubmit this same order.
          </div>
        </div>
        <button class="btn btn-danger flex-shrink-0" style="white-space:nowrap">
          <i class="fas fa-paper-plane mr-1"></i>Resubmit Order
        </button>
      </div>

      <!-- Rejection block -->
      <div class="rejection-block">
        <div class="rejection-title">
          <i class="fas fa-exclamation-circle"></i>
          Rejection #1 &nbsp;·&nbsp; Source: STATE (WV DMV) &nbsp;·&nbsp; Code: STATE
        </div>
        <div class="rejection-text">The transfer agreement was rejected due to "Evidence Illegible".

Note: "Bad image — the title front and back scan is too low resolution. Please re-scan at 300 DPI or above in color and reupload the TITLE_FRONT_AND_BACK document."</div>
      </div>

      <!-- What to do -->
      <div class="section-card mt-3">
        <div class="card-header">
          <div class="header-icon" style="background:#dbeafe;color:#1d4ed8"><i class="fas fa-list-ol"></i></div>
          <h6>How to fix this</h6>
        </div>
        <div class="card-body p-3">
          <div class="d-flex align-items-start mb-2" style="gap:.75rem">
            <div style="width:22px;height:22px;border-radius:50%;background:#3b7ddd;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0">1</div>
            <div style="font-size:.8rem">Re-scan the physical title (front and back) at <strong>minimum 300 DPI</strong> in <strong>color</strong>. Make sure both sides are clearly legible and not cut off.</div>
          </div>
          <div class="d-flex align-items-start mb-2" style="gap:.75rem">
            <div style="width:22px;height:22px;border-radius:50%;background:#3b7ddd;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0">2</div>
            <div style="font-size:.8rem">Go to the <a href="#tab-documents" data-toggle="tab"><strong>Documents tab</strong></a> and click <strong>Replace</strong> on the rejected Title Front & Back document.</div>
          </div>
          <div class="d-flex align-items-start mb-2" style="gap:.75rem">
            <div style="width:22px;height:22px;border-radius:50%;background:#3b7ddd;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0">3</div>
            <div style="font-size:.8rem">Once replaced, come back here and click <strong>Resubmit Order</strong>.</div>
          </div>
          <div class="alert alert-info mt-3 mb-0" style="font-size:.78rem">
            <i class="fas fa-info-circle mr-1"></i>
            <strong>Important:</strong> Do not create a new order. Always resubmit this same order so the DMV has the full correction context. A new order for the same VIN will be blocked as a duplicate.
          </div>
        </div>
      </div>

    </div><!-- /tab-rejection -->

    <!-- ══ TAB 5: REJECTION HISTORY ══════════════════════════════ -->
    <div class="tab-pane fade" id="tab-history">

      <p class="text-muted mb-3" style="font-size:.78rem">
        <i class="fas fa-info-circle mr-1"></i>
        Permanent record of all DMV decisions across every submission.
      </p>

      <!-- History entry -->
      <div class="section-card mb-3">
        <div class="card-header" style="background:#fdf2f2">
          <div class="header-icon" style="background:#f5c6cb;color:#dc3545"><i class="fas fa-times"></i></div>
          <h6 class="text-danger">Submission #1 — Rejected</h6>
          <span class="ml-auto text-muted" style="font-size:.72rem">Jan 22, 2024 · 14:33 UTC</span>
        </div>
        <div class="card-body p-3">
          <div class="row">
            <div class="col-md-4">
              <div class="info-row"><span class="info-key">NDTC status</span><span class="info-val"><span class="badge badge-danger" style="font-size:.65rem">MANUALLY_REJECTED</span></span></div>
              <div class="info-row"><span class="info-key">Rejected at</span><span class="info-val">Jan 22, 2024 14:33 UTC</span></div>
              <div class="info-row"><span class="info-key">Source</span><span class="info-val">STATE (WV DMV)</span></div>
            </div>
            <div class="col-md-8">
              <div style="font-size:.72rem;color:#6c757d;margin-bottom:4px;font-weight:600">Rejection reasons:</div>
              <div class="rejection-text" style="font-size:.75rem">The transfer agreement was rejected due to "Evidence Illegible".
Note: "Bad image — re-scan at 300 DPI or above in color."</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state for future -->
      <div class="text-center text-muted py-3" style="font-size:.78rem">
        <i class="fas fa-check-circle text-success d-block mb-1" style="font-size:1.5rem"></i>
        No further rejection history. This order has been rejected once.
      </div>

    </div><!-- /tab-history -->

  </div><!-- /tab-content -->

</div><!-- page-content -->
</div><!-- main -->

@endsection

@section('scripts')

@endsection
