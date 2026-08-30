<?php
// app/Http/Controllers/Ndtc/NdtcOrderController.php

namespace App\Http\Controllers\Ndtc;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ndtc\StoreNdtcOrderRequest;
use App\Http\Requests\Ndtc\UpdateNdtcOrderRequest;
use App\Jobs\Ndtc\UploadNdtcDocuments;
use App\Models\NdtcOrder;
use App\Models\NdtcOrderDocument;
use App\Models\Vehicle;
use App\Services\Ndtc\NdtcApiService;
use App\Services\Ndtc\NdtcDescriptionParser;
use App\Services\Ndtc\NdtcPayloadBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NdtcOrderController extends Controller
{
    public function __construct(
        private NdtcApiService $api,
        private NdtcPayloadBuilder $builder,
        private NdtcDescriptionParser $parser,
    ) {}

    // ── INDEX ─────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = NdtcOrder::with(['vehicle', 'createdBy'])->latest('created_at');

        // Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('vin',              'like', "%{$s}%")
                ->orWhere('ndtc_order_id',  'like', "%{$s}%")
                ->orWhere('correlation_id', 'like', "%{$s}%")
                ->orWhere('new_title_number','like',"%{$s}%")
                ->orWhere('vehicle_description','like',"%{$s}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Transaction type filter
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }

        // Checkbox filters
        if ($request->boolean('has_rejections')) {
            $query->where('rejection_count', '>', 0);
        }
        if ($request->boolean('finalized')) {
            $query->where('finalized', true);
        }
        if ($request->boolean('needs_action')) {
            $query->whereIn('status', ['REJECTED', 'AGING', 'READY_FOR_DOCUMENTS', 'READY_TO_FINALIZE']);
        }

        // New filters (map to your actual columns/relations)
        if ($request->filled('vehicle_make')) {
            $query->whereHas('vehicle', function ($q) use ($request) {
                $q->where('make', $request->vehicle_make);
            });
        }
        if ($request->filled('issuing_state')) {
            $query->where('issuing_state_code', $request->issuing_state);
        }
        if ($request->filled('title_brand')) {
            // adjust if title_brands is stored as JSON array
            $query->whereJsonContains('title_brands', $request->title_brand);
        }
        if ($request->filled('lien_type')) {
            $query->where('lien_type', $request->lien_type);
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $dir  = $request->get('dir', 'desc');
        $allowed = ['vin','status','transfer_date','created_at','rejection_count','approved_at'];
        if (in_array($sort, $allowed)) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        }

        $statsQuery = clone $query;

        $stats = [
            'total'              => (clone $statsQuery)->count(),
            'draft'              => (clone $statsQuery)->where('status', 'DRAFT')->count(),
            'ready_for_docs'     => (clone $statsQuery)->where('status', 'READY_FOR_DOCUMENTS')->count(),
            'ready_to_finalize'  => (clone $statsQuery)->where('status', 'READY_TO_FINALIZE')->count(),
            'processing'         => (clone $statsQuery)->whereIn('status', ['PROCESSING', 'MANUAL_REVIEW'])->count(),
            'on_hold_aging'      => (clone $statsQuery)->whereIn('status', ['ON_HOLD', 'AGING'])->count(),
            'completed'          => (clone $statsQuery)->whereIn('status', ['APPROVED', 'COMPLETED'])->count(),
            'rejected'           => (clone $statsQuery)->where('status', 'REJECTED')->count(),
            'canceled'           => (clone $statsQuery)->where('status', 'CANCELLED')->count(),
            'manual_review'      => (clone $statsQuery)->where('status', 'MANUAL_REVIEW')->count(),
            'title_terminated'   => (clone $statsQuery)->where('status', 'TITLE_TERMINATED')->count(),
        ];

        dd($stats);

        $orders = $query->paginate(10)->withQueryString();

        return view('pages.ndtc.index', compact('orders', 'stats'));
    }

    // ── CREATE ────────────────────────────────────────────────────
    public function create(int $vehicleId)
    {
        $vehicle = Vehicle::with('metas')
                          ->findOrFail($vehicleId);

        // Check no active order exists for this VIN
        $existing = NdtcOrder::where('vin', $vehicle->vin)
                              ->whereNotIn('status', NdtcOrder::TERMINAL_STATUSES)
                              ->first();

        if ($existing) {
            return redirect()
                ->route('ndtc.orders.show', $existing)
                ->with('warning', 'An active NDTC order already exists for this VIN.');
        }

        $metas   = $vehicle->metas->pluck('meta_value', 'meta_key');
        $parsed  = $this->parser->parse($vehicle->description);

        return view('pages.ndtc.create', [
            'vehicle'    => $vehicle,
            'metas'      => $metas,
            'parsed'     => $parsed,
            'odometer'   => $metas->get('odometer'),
            'saleDate'   => $metas->get('sale_date'),
            'titleState' => $metas->get('sale_title_state'),
            'titleType'  => $metas->get('sale_title_type', 'PAPER'),
        ]);
    }

    // ── STORE ─────────────────────────────────────────────────────
    public function store(StoreNdtcOrderRequest $request)
    {
        $correlationId = strtoupper($request->input('vin')) . '-' . now()->format('YmdHis');
        // Build NDTC payload
        $payload              = $this->builder->fromRequest($request, $correlationId);
        // Call NDTC API
        try {
            $response = $this->api->createOrder($payload);
            // $response = [
            //     'orderId' => Str::random(24),
            // ];
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create order with NDTC: ' . $e->getMessage());
        }

        $ndtcOrderId = $response['orderId'] ?? null;

        if (!$ndtcOrderId) {
            return back()
                ->withInput()
                ->with('error', 'NDTC did not return an order ID. Please try again.');
        }

        // Save locally
        $order = NdtcOrder::create([
            'vehicle_id'          => $request->input('vehicle_id'),
            'created_by'          => auth()->id(),
            'ndtc_order_id'       => $ndtcOrderId,
            'correlation_id'      => $correlationId,
            'vin'                 => $request->input('vin'),
            'vehicle_description' => $request->input('year') . ' '
                                   . $request->input('make') . ' '
                                   . $request->input('model'),
            'transaction_type'    => $request->input('transaction_type', 'TNL'),
            'status'              => NdtcOrder::STATUS_DRAFT,
            'transfer_date'       => $request->input('transfer_date'),
            'order_payload'       => $payload,
        ]);

        return redirect()
            ->route('ndtc.orders.show', $order)
            ->with('success', 'Order created successfully. Waiting for CHAMP to confirm before uploading documents.');
    }

    // ── SHOW ──────────────────────────────────────────────────────
    public function show(NdtcOrder $order)
    {
        $order->load([
            'vehicle',
            'documents',
            'webhookLogs' => fn($q) => $q->orderBy('received_at', 'desc'),
            'rejectionHistory' => fn($q) => $q->orderBy('rejected_at', 'desc'),
            'createdBy',
        ]);

        //dd($order->webhookLogs);

        return view('pages.ndtc.show', compact('order'));
    }

    // ── EDIT ──────────────────────────────────────────────────────
    public function edit(NdtcOrder $order)
    {
        if (!$order->isRejected()) {
            return redirect()
                ->route('ndtc.orders.show', $order)
                ->with('error', 'Only rejected orders can be edited.');
        }

        $vehicle = $order->vehicle()->with('metas')->first();
        $payload = $order->order_payload ?? [];

        return view('ndtc.orders.edit', compact('order', 'vehicle', 'payload'));
    }

    // ── UPDATE ────────────────────────────────────────────────────
    public function update(UpdateNdtcOrderRequest $request, NdtcOrder $order)
    {
        if (!$order->isRejected()) {
            return redirect()
                ->route('ndtc.orders.show', $order)
                ->with('error', 'Only rejected orders can be updated.');
        }

        $payload = $this->builder->fromRequest($request);
        $payload['correlationId'] = $order->correlation_id;

        try {
            $this->api->updateOrder($order->ndtc_order_id, $payload);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update order: ' . $e->getMessage());
        }

        $order->update([
            'order_payload'      => $payload,
            'status'             => NdtcOrder::STATUS_DRAFT,
            'finalized'          => false,
            'ready_to_finalize'  => false,
        ]);

        return redirect()
            ->route('ndtc.orders.show', $order)
            ->with('success', 'Order updated. Waiting for CHAMP confirmation before next steps.');
    }

    // ── FINALIZE ──────────────────────────────────────────────────
    public function finalize(NdtcOrder $order)
    {
        if (!$order->canBeFinalized()) {
            return back()->with('error', 'This order cannot be finalized at this time.');
        }

        try {
            $this->api->finalizeOrder($order->ndtc_order_id);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to finalize order: ' . $e->getMessage());
        }

        $order->update([
            'finalized'          => true,
            'finalized_at'       => now(),
            'submission_count'   => $order->submission_count + 1,
        ]);

        return back()->with('success', 'Order finalized and submitted to DMV. Waiting for decision.');
    }

    // ── CANCEL ────────────────────────────────────────────────────
    public function cancel(NdtcOrder $order)
    {
        if (!$order->canBeCancelled()) {
            return back()->with('error', 'This order cannot be cancelled at this time.');
        }

        try {
            $this->api->cancelOrder($order->ndtc_order_id);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }

        // Webhook ORDER_CANCELLED will update status
        // But update locally immediately for UI feedback
        $order->update([
            'status'       => NdtcOrder::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('ndtc.orders.index')
            ->with('success', 'Order cancelled successfully.');
    }

    // ── DOCUMENT UPLOAD ───────────────────────────────────────────
    public function storeDocument(Request $request, NdtcOrder $order)
    {
        $request->validate([
            'document'         => ['required', 'file', 'max:20480', 'mimes:pdf,jpeg,png'],
            'document_content' => ['required', 'string'],
        ]);

        if ($order->status !== NdtcOrder::STATUS_READY_FOR_DOCUMENTS
            && $order->status !== NdtcOrder::STATUS_READY_TO_FINALIZE) {
            return back()->with('error', 'Documents can only be uploaded when order is ready for documents.');
        }

        // Store file temporarily
        $file        = $request->file('document');
        $mimeType    = $file->getMimeType();
        $displayName = $file->getClientOriginalName();
        $tempPath    = $file->store('temp/ndtc', 'local');

        // Create document record
        $document = NdtcOrderDocument::create([
            'ndtc_order_id'    => $order->id,
            'document_content' => $request->input('document_content'),
            'file_display_name'=> $displayName,
            'file_mime_type'   => $mimeType,
            'file_size_bytes'  => $file->getSize(),
            'status'           => NdtcOrderDocument::STATUS_PENDING,
        ]);

        // Dispatch upload job
        UploadNdtcDocuments::dispatch($order, $document, storage_path('app/' . $tempPath));

        return back()->with('success', 'Document queued for upload. It will appear shortly.');
    }

    // ── DOCUMENT REPLACE ──────────────────────────────────────────
    public function replaceDocument(Request $request, NdtcOrder $order, NdtcOrderDocument $document)
    {
        if ($document->is_system_generated) {
            return back()->with('error', 'System generated documents cannot be replaced.');
        }

        $request->validate([
            'document' => ['required', 'file', 'max:20480', 'mimes:pdf,jpeg,png'],
        ]);

        // Delete old document from NDTC
        if ($document->ndtc_document_id) {
            try {
                $this->api->deleteDocument($order->ndtc_order_id, $document->ndtc_document_id);
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to remove old document: ' . $e->getMessage());
            }
        }

        $document->update([
            'status'            => NdtcOrderDocument::STATUS_REPLACED,
            'ndtc_document_id'  => null,
        ]);

        // Store new file and upload
        $file     = $request->file('document');
        $tempPath = $file->store('temp/ndtc', 'local');

        $newDocument = NdtcOrderDocument::create([
            'ndtc_order_id'    => $order->id,
            'document_content' => $document->document_content,
            'file_display_name'=> $file->getClientOriginalName(),
            'file_mime_type'   => $file->getMimeType(),
            'file_size_bytes'  => $file->getSize(),
            'status'           => NdtcOrderDocument::STATUS_PENDING,
        ]);

        UploadNdtcDocuments::dispatch($order, $newDocument, storage_path('app/' . $tempPath));

        return back()->with('success', 'Document replaced and queued for upload.');
    }
}
