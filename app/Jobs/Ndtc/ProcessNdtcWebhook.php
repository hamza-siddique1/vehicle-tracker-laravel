<?php
// app/Jobs/Ndtc/ProcessNdtcWebhook.php

namespace App\Jobs\Ndtc;

use App\Models\NdtcOrder;
use App\Models\NdtcRejectionHistory;
use App\Models\NdtcWebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessNdtcWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(private array $payload) {}

    public function handle(): void
    {
        $event   = $this->payload['event']   ?? null;
        $orderId = $this->payload['orderId'] ?? null;

        // Find local order
        $order = NdtcOrder::where('ndtc_order_id', $orderId)->first();

        // Update log as processed
        $this->markLogProcessed($orderId);

        if (!$order) {
            Log::warning('NDTC webhook — unknown order', ['orderId' => $orderId, 'event' => $event]);
            return;
        }

        match($event) {
            'READY_FOR_DOCUMENTS' => $this->onReadyForDocuments($order),
            'READY_TO_FINALIZE'   => $this->onReadyToFinalize($order),
            'PROCESSING'          => $this->onProcessing($order),
            'ORDER_APPROVED'      => $this->onApproved($order),
            'ORDER_REJECTED'      => $this->onRejected($order),
            'MANUAL_REVIEW'       => $this->onManualReview($order),
            'ON_HOLD'             => $this->onHold($order),
            'AGING_ORDER'         => $this->onAging($order),
            'ORDER_CANCELLED'     => $this->onCancelled($order),
            'TITLE_TERMINATED'    => $this->onTitleTerminated($order),
            default               => Log::info('NDTC unknown event', ['event' => $event]),
        };
    }

    private function onReadyForDocuments(NdtcOrder $order): void
    {
        $order->update([
            'status'            => NdtcOrder::STATUS_READY_FOR_DOCUMENTS,
            'finalized'         => false,
            'ready_to_finalize' => false,
        ]);
    }

    private function onReadyToFinalize(NdtcOrder $order): void
    {
        $order->update([
            'status'            => NdtcOrder::STATUS_READY_TO_FINALIZE,
            'ready_to_finalize' => true,
        ]);
    }

    private function onProcessing(NdtcOrder $order): void
    {
        if (!$this->canTransitionTo($order, 'PROCESSING')) return;

        $order->update([
            'status'    => NdtcOrder::STATUS_PROCESSING,
            'finalized' => true,
        ]);
    }

    private function onApproved(NdtcOrder $order): void
    {
        $newTitle = $this->payload['order']['evidenceDetail']['newTitle'] ?? [];

        $order->update([
            'status'           => NdtcOrder::STATUS_APPROVED,
            'ndtc_status'      => $this->payload['status'],
            'new_title_number' => $newTitle['titleNumber'] ?? null,
            'approved_at'      => now(),
            'last_webhook'     => $this->payload,
        ]);
    }

    private function onRejected(NdtcOrder $order): void
    {
        $rejections = $this->payload['order']['rejections'] ?? [];

        // Store in permanent rejection history
        NdtcRejectionHistory::create([
            'ndtc_order_id'     => $order->id,
            'submission_number' => $order->submission_count,
            'ndtc_status'       => $this->payload['status'],
            'rejection_reasons' => $rejections,
            'webhook_payload'   => $this->payload,
            'rejected_at'       => now(),
        ]);

        $order->update([
            'status'            => NdtcOrder::STATUS_REJECTED,
            'ndtc_status'       => $this->payload['status'],
            'finalized'         => false,
            'ready_to_finalize' => false,
            'rejection_reasons' => $rejections,
            'rejection_count'   => $order->rejection_count + 1,
            'rejected_at'       => now(),
            'last_webhook'      => $this->payload,
        ]);
    }

    private function onManualReview(NdtcOrder $order): void
    {
        $order->update(['status' => NdtcOrder::STATUS_MANUAL_REVIEW]);
    }

    private function onHold(NdtcOrder $order): void
    {
        $order->update(['status' => NdtcOrder::STATUS_ON_HOLD]);
    }

    private function onAging(NdtcOrder $order): void
    {
        $order->update(['status' => NdtcOrder::STATUS_AGING]);
    }

    private function onCancelled(NdtcOrder $order): void
    {
        $order->update([
            'status'       => NdtcOrder::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    private function onTitleTerminated(NdtcOrder $order): void
    {
        $order->update(['status' => NdtcOrder::STATUS_TITLE_TERMINATED]);
    }

    private function canTransitionTo(NdtcOrder $order, string $newStatus): bool
    {
        $rank = [
            'DRAFT'               => 1,
            'READY_FOR_DOCUMENTS' => 2,
            'READY_TO_FINALIZE'   => 3,
            'PROCESSING'          => 4,
            'MANUAL_REVIEW'       => 5,
            'ON_HOLD'             => 5,
            'APPROVED'            => 10,
            'REJECTED'            => 10,
            'CANCELLED'           => 10,
            'TITLE_TERMINATED'    => 10,
            'AGING'               => 3,
        ];

        // REJECTED can come from any status
        if ($newStatus === 'REJECTED') return true;

        $current = $rank[$order->status] ?? 0;
        $next    = $rank[$newStatus]     ?? 0;

        return $next >= $current;
    }

    private function markLogProcessed(string $orderId): void
    {
        NdtcWebhookLog::where('ndtc_order_id', $orderId)
            ->where('processed', false)
            ->latest('received_at')
            ->first()
            ?->update([
                'processed'    => true,
                'processed_at' => now(),
            ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ProcessNdtcWebhook job failed', [
            'event'   => $this->payload['event'] ?? null,
            'orderId' => $this->payload['orderId'] ?? null,
            'error'   => $e->getMessage(),
        ]);
    }
}
