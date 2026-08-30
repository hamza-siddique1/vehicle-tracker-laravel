@extends('layouts.app')

@section('title', 'NDTC Orders')

@section('content')

@php
    $statusColors = [
        'DRAFT'               => 'secondary',
        'READY_FOR_DOCUMENTS' => 'warning',
        'READY_TO_FINALIZE'   => 'info',
        'PROCESSING'          => 'primary',
        'MANUAL_REVIEW'       => 'warning',
        'ON_HOLD'             => 'warning',
        'APPROVED'            => 'success',
        'COMPLETED'           => 'success',
        'REJECTED'            => 'danger',
        'CANCELLED'           => 'secondary',
        'AGING'               => 'warning',
        'TITLE_TERMINATED'    => 'dark',
    ];
    $statusLabels = [
        'DRAFT'               => 'Draft',
        'READY_FOR_DOCUMENTS' => 'Ready for Docs',
        'READY_TO_FINALIZE'   => 'Ready to Finalize',
        'PROCESSING'          => 'Processing',
        'MANUAL_REVIEW'       => 'Manual Review',
        'ON_HOLD'             => 'On Hold',
        'APPROVED'            => 'Approved',
        'COMPLETED'           => 'Completed',
        'REJECTED'            => 'Rejected',
        'CANCELLED'           => 'Cancelled',
        'AGING'               => 'Aging',
        'TITLE_TERMINATED'    => 'Title Terminated',
    ];
@endphp

<div class="px-4 py-3">

    {{-- ══ PAGE HEADING ══════════════════════════════════════════ --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 mb-0">NDTC Orders</h1>
            <p class="text-muted mb-0" style="font-size:.8rem">
                Electronic title transfers via CHAMP Titles · WV DMV
            </p>
        </div>
    </div>

    {{-- ══ STAT CARDS ═════════════════════════════════════════════ --}}
    @include('pages.ndtc.partials._stats')

    {{-- ══ FILTERS ═════════════════════════════════════════════════ --}}
    @include('pages.ndtc.partials._filters')

    {{-- ══ TABLE ══════════════════════════════════════════════════ --}}
    <div class="card">
        <div class="card-body p-0">
            @if($orders->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-file-alt fa-2x mb-2 d-block"></i>
                    No orders found.
                    @if(request()->hasAny(['status','search','date_from','date_to']))
                        <a href="{{ route('ndtc.orders.index') }}">Clear filters</a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.82rem">
                        <thead class="thead-light">
                            <tr>
                                <th style="min-width:180px">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'vin', 'dir' => request('sort') === 'vin' && request('dir') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-dark text-decoration-none">
                                        Vehicle
                                        @if(request('sort') === 'vin')
                                            <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th style="min-width:120px">NDTC Order ID</th>
                                <th style="width:70px">Type</th>
                                <th style="min-width:130px">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'dir' => request('sort') === 'status' && request('dir') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-dark text-decoration-none">
                                        Status
                                        @if(request('sort') === 'status')
                                            <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'transfer_date', 'dir' => request('sort') === 'transfer_date' && request('dir') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-dark text-decoration-none">
                                        Transfer date
                                        @if(request('sort') === 'transfer_date')
                                            <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="fas fa-sort ml-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-center">Submissions</th>
                                <th class="text-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'rejection_count', 'dir' => request('sort') === 'rejection_count' && request('dir') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-dark text-decoration-none">
                                        Rejections
                                        @if(request('sort') === 'rejection_count')
                                            <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => request('sort') === 'created_at' && request('dir') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-dark text-decoration-none">
                                        Created
                                        @if(request('sort') === 'created_at')
                                            <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="fas fa-sort ml-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>New Title #</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr class="{{ $order->status === 'REJECTED' ? 'table-danger' : ($order->status === 'AGING' ? 'table-warning' : '') }}">

                                    {{-- Vehicle --}}
                                    <td>
                                        <div class="font-weight-bold"
                                             style="font-family:'Courier New',monospace;font-size:.75rem">
                                            {{ $order->vin }}
                                        </div>
                                        @if($order->vehicle_description)
                                            <div class="text-muted" style="font-size:.72rem">
                                                {{ $order->vehicle_description }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- NDTC Order ID --}}
                                    <td>
                                        @if($order->ndtc_order_id)
                                            <code style="font-size:.68rem">
                                                {{ Str::limit($order->ndtc_order_id, 20) }}
                                            </code>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- Transaction type --}}
                                    <td>
                                        <span class="badge badge-light">{{ $order->transaction_type }}</span>
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        <span class="badge badge-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                            {{ $statusLabels[$order->status] ?? $order->status }}
                                        </span>
                                        @if($order->status === 'REJECTED')
                                            <div class="text-danger" style="font-size:.65rem;margin-top:2px">
                                                <i class="fas fa-exclamation-circle"></i> Action needed
                                            </div>
                                        @elseif($order->status === 'READY_TO_FINALIZE')
                                            <div class="text-success" style="font-size:.65rem;margin-top:2px">
                                                <i class="fas fa-check-circle"></i> Finalize now
                                            </div>
                                        @elseif($order->status === 'READY_FOR_DOCUMENTS')
                                            <div class="text-warning" style="font-size:.65rem;margin-top:2px">
                                                <i class="fas fa-upload"></i> Upload docs
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Transfer date --}}
                                    <td style="white-space:nowrap">
                                        {{ $order->transfer_date?->format('M d, Y') ?? '—' }}
                                    </td>

                                    {{-- Submissions --}}
                                    <td class="text-center">
                                        {{ $order->submission_count }}
                                    </td>

                                    {{-- Rejections --}}
                                    <td class="text-center">
                                        @if($order->rejection_count > 0)
                                            <span class="badge badge-danger">{{ $order->rejection_count }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>

                                    {{-- Created --}}
                                    <td style="white-space:nowrap">
                                        <div>{{ $order->created_at->format('M d, Y') }}</div>
                                        <div class="text-muted" style="font-size:.7rem">
                                            {{ $order->created_at->format('H:i') }} UTC
                                        </div>
                                    </td>

                                    {{-- New title # --}}
                                    <td>
                                        @if($order->new_title_number)
                                            <code class="text-success" style="font-size:.72rem">
                                                {{ $order->new_title_number }}
                                            </code>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-right" style="white-space:nowrap">
                                        <a href="{{ route('ndtc.orders.show', $order) }}"
                                           class="btn btn-sm btn-outline-primary btn-xs">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @if($order->status === 'REJECTED')
                                            <a href="{{ route('ndtc.orders.edit', $order) }}"
                                               class="btn btn-sm btn-warning btn-xs ml-1">
                                                <i class="fas fa-edit"></i> Fix
                                            </a>
                                        @endif
                                        @if($order->canBeFinalized())
                                            <form action="{{ route('ndtc.orders.finalize', $order) }}"
                                                  method="POST" class="d-inline ml-1">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success btn-xs">
                                                    <i class="fas fa-check"></i> Finalize
                                                </button>
                                            </form>
                                        @endif
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top">
                    <div class="text-muted small">
                        Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }}
                        of {{ $orders->total() }} orders
                    </div>
                    {{ $orders->withQueryString()->links('pagination::bootstrap-4') }}
                </div>

            @endif
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
$(document).ready(function () {
    // Auto-submit filter form on sort link click — already handled by href
    // Preserve existing filters when sorting
});
</script>
@endsection
