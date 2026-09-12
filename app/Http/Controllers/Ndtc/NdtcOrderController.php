<?php

namespace App\Http\Controllers\Ndtc;

use App\Actions\Ndtc\SyncOrderFromChamp;
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
use Illuminate\Support\Facades\Cache;

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
        $query = NdtcOrder::with(['vehicle', 'createdBy']);

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
            $query->where(function ($q) {
                $q->whereIn('status', ['REJECTED', 'READY_FOR_DOCUMENTS', 'READY_TO_FINALIZE'])
                ->orWhere('is_aging', true);
            });
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
        $sort = $request->input('sort', 'status_priority');
        $dir  = $request->input('dir', 'desc');
        $allowed = ['vin','status','transfer_date','created_at','rejection_count','approved_at'];
        if ($sort === 'status_priority') {
            // Custom priority: action-needed first, cancelled last, recency as tiebreaker
            $statusOrder = [
                'NOT_FOUND',
                'REJECTED',
                'READY_TO_FINALIZE',
                'READY_FOR_DOCUMENTS',
                'AGING',
                'DRAFT',
                'ON_HOLD',
                'PROCESSING',
                'MANUAL_REVIEW',
                'APPROVED',
                'COMPLETED',
                'TITLE_TERMINATED',
                'CANCELLED',
            ];

            $orderedList = implode(',', array_map(fn ($s) => "'{$s}'", $statusOrder));
            $query->orderByDesc('is_aging') // aging orders always float to the top of their status group
                ->orderByRaw("FIELD(status, {$orderedList})")
                ->latest('created_at');
        } elseif (in_array($sort, $allowed)) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        }
        else{
            $query->latest('created_at');
        }

        $statsQuery = clone $query;

        $stats = [
            'total'              => (clone $statsQuery)->count(),
            'draft'              => (clone $statsQuery)->where('status', 'DRAFT')->count(),
            'ready_for_docs'     => (clone $statsQuery)->where('status', 'READY_FOR_DOCUMENTS')->count(),
            'ready_to_finalize'  => (clone $statsQuery)->where('status', 'READY_TO_FINALIZE')->count(),
            'processing'         => (clone $statsQuery)->whereIn('status', ['PROCESSING', 'MANUAL_REVIEW'])->count(),
            'on_hold_aging'      => (clone $statsQuery)
                                        ->where(function ($q) {
                                            $q->where('status', NdtcOrder::STATUS_ON_HOLD)
                                            ->orWhere('is_aging', true);
                                        })
                                        ->count(),
            'completed'          => (clone $statsQuery)->whereIn('status', ['APPROVED', 'COMPLETED'])->count(),
            'rejected'           => (clone $statsQuery)->where('status', 'REJECTED')->count(),
            'canceled'           => (clone $statsQuery)->where('status', 'CANCELLED')->count(),
            'manual_review'      => (clone $statsQuery)->where('status', 'MANUAL_REVIEW')->count(),
            'title_terminated'   => (clone $statsQuery)->where('status', 'TITLE_TERMINATED')->count(),
        ];

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
            $response = $this->api->createOrder($payload, $request->input('transaction_type'));
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

        // dd($order->documents);

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

        return view('pages.ndtc.edit', compact('order', 'vehicle', 'payload'));
    }

    // ── UPDATE ────────────────────────────────────────────────────
    public function update(UpdateNdtcOrderRequest $request, NdtcOrder $order)
    {
        if (!$order->isRejected()) {
            return redirect()
                ->route('ndtc.orders.show', $order)
                ->with('error', 'Only rejected orders can be updated.');
        }

        $payload = $this->builder->fromRequest($request, $order->correlation_id);
        $payload['correlationId'] = $order->correlation_id;

        try {
            $this->api->updateOrder($order->ndtc_order_id, $payload, $order->transaction_type);
        } catch (\Exception $e) {
            $message = $this->extractApiErrorMessage($e);

            return redirect()
                ->route('ndtc.orders.show', $order)
                ->with('error', $message);
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
        } catch (\Exception $e) { //hamza
            $message = $this->extractApiErrorMessage($e);
            return back()->with('error', 'Failed to finalize order: ' . $message);
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

        // if ($order->status !== NdtcOrder::STATUS_READY_FOR_DOCUMENTS
        //     && $order->status !== NdtcOrder::STATUS_READY_TO_FINALIZE) {
        //     return back()->with('error', 'Documents can only be uploaded when order is ready for documents.');
        // }

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
            'is_system_generated' => false,
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
            'is_system_generated' => false,
        ]);

        UploadNdtcDocuments::dispatch($order, $newDocument, storage_path('app/' . $tempPath));

        return back()->with('success', 'Document replaced and queued for upload.');
    }

    public function deleteDocument(NdtcOrder $order, NdtcOrderDocument $document)
    {
        if ($document->ndtc_order_id !== $order->id) {
            abort(404);
        }

        // Delete from NDTC first, if it was ever actually uploaded there
        if ($document->ndtc_document_id) {
            try {
                $this->api->deleteDocument($order->ndtc_order_id, $document->ndtc_document_id);
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to delete document from NDTC: ' . $e->getMessage());
            }
        }

        $document->delete(); // soft delete locally

        return back()->with('success', 'Document deleted successfully.');
    }

    // ── DOCUMENT VIEW ─────────────────────────────────────────────
    public function viewDocument(NdtcOrder $order, NdtcOrderDocument $document)
    {
        if ($document->ndtc_order_id !== $order->id) {
            abort(404);
        }

        if (! $document->ndtc_document_id) {
            return back()->with('error', 'Document is not yet available on NDTC.');
        }

        $cacheKey = "ndtc_doc_url:{$order->ndtc_order_id}:{$document->ndtc_document_id}";

        try {
            $presignedUrl = Cache::remember($cacheKey, 240, function () use ($order, $document) {
                $response = $this->api->getDocument($order->ndtc_order_id, $document->ndtc_document_id);
                return is_array($response) ? $response['presignedUrl'] : $response;
            });
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return back()->with('error', 'Failed to retrieve document: ' . $e->getMessage());
        }

        return redirect()->away($presignedUrl);
    }

    public function archive(NdtcOrder $order)
    {
        $order->delete();

        return redirect()
            ->route('ndtc.orders.index', $order)
            ->with('success', "Order for VIN {$order->vin} has been archived.");
    }

    public function syncFromChamp(NdtcOrder $order, SyncOrderFromChamp $sync)
    {
        try {
            $sync->execute($order);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to sync with NDTC: ' . $e->getMessage());
        }

        return back()->with('success', 'Order synced with NDTC — status and document list updated.');
    }

    private function extractApiErrorMessage(\Exception $e): string
    {
        $raw = $e->getMessage();

        // The exception message may already be JSON, or it may be wrapped
        // (e.g. "Client error: `PUT ...` resulted in a `400 Bad Request`
        // response: {json}") depending on your HTTP client (Guzzle, etc.)
        $jsonStart = strpos($raw, '{');
        $jsonString = $jsonStart !== false ? substr($raw, $jsonStart) : $raw;

        $decoded = json_decode($jsonString, true);

        if (json_last_error() === JSON_ERROR_NONE
            && isset($decoded['resultInfo']['errors'][0]['details'])) {
            return $decoded['resultInfo']['errors'][0]['details'];
        }

        // Fallback if parsing fails or shape doesn't match
        return 'Failed to update order. Please try again or contact support.';
    }
}
