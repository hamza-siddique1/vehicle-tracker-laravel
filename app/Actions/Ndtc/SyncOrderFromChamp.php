<?php

namespace App\Actions\Ndtc;

use App\Models\NdtcOrder;
use App\Models\NdtcOrderDocument;
use App\Services\Ndtc\NdtcApiService;

class SyncOrderFromChamp
{
    public function __construct(private NdtcApiService $api) {}

    /**
     * @throws \Exception if the API call itself fails
     */
    public function execute(NdtcOrder $order): void
    {
        if (!$order->ndtc_order_id) {
            throw new \Exception('Order has not been submitted to NDTC yet.');
        }

        $response = $this->api->getOrder($order->ndtc_order_id);
        $mappedStatus = $this->mapOrderStatusToAppStatus($response['orderStatus'] ?? $order->ndtc_status);
        $order->applyStatusIfAdvanced($mappedStatus);

        $updates = [
            'ndtc_status'     => $response['orderStatus'] ?? $order->ndtc_status,
            'champ_snapshot'  => $response,
            'last_synced_at'  => now(),
        ];

        if ($response['orderStatus'] === 'MANUAL_REVIEW_REQUIRED') {
            $updates['status'] = 'MANUAL_REVIEW_REQUIRED';
        }

        $order->update($updates);

        $remoteDocs = collect($response['evidenceDetail']['attachedDocuments'] ?? []);
        $remoteIds  = $remoteDocs->pluck('id')->filter()->all();

        foreach ($remoteDocs as $remoteDoc) {
            //dump($remoteDoc['id']);
            $localDoc = $order->documents()
                ->where('ndtc_document_id', $remoteDoc['id'])
                ->first();

            if ($localDoc) {
                $localDoc->update([
                    'status'            => NdtcOrderDocument::STATUS_UPLOADED,
                    'file_display_name' => $remoteDoc['fileDisplayName'] ?? $localDoc->file_display_name,
                    'file_mime_type'    => $remoteDoc['fileType'] ?? $localDoc->file_mime_type,
                    'file_size_bytes'   => $remoteDoc['byteSize'] ?? $localDoc->file_size_bytes,
                    'upload_error'      => null,
                ]);
            } else {
                $order->documents()->create([
                    'ndtc_document_id'    => $remoteDoc['id'],
                    'document_content'    => $remoteDoc['documentContent'] ?? 'OTHER_EVIDENCE',
                    'file_display_name'   => $remoteDoc['fileDisplayName'] ?? null,
                    'file_mime_type'      => $remoteDoc['fileType'] ?? null,
                    'file_size_bytes'     => $remoteDoc['byteSize'] ?? null,
                    'status'              => NdtcOrderDocument::STATUS_UPLOADED,
                    'is_system_generated' => true,
                    'uploaded_at'         => now(),
                ]);
            }
        }

        $order->documents()
            ->whereNotNull('ndtc_document_id')
            ->whereNotIn('ndtc_document_id', $remoteIds)
            ->get()
            ->each(function ($doc) {
                $doc->update([
                    'status'           => NdtcOrderDocument::STATUS_FAILED,
                    'ndtc_document_id' => null,
                    'upload_error'     => 'No longer attached on NDTC — link was stale and has been cleared.',
                ]);
            });
    }

    private function mapOrderStatusToAppStatus(string $ndtcOrderStatus): string
    {
        return match ($ndtcOrderStatus) {
            'DRAFT'                          => NdtcOrder::STATUS_DRAFT,
            'PROCESSING'                      => NdtcOrder::STATUS_PROCESSING,
            'MANUAL_REVIEW_REQUIRED'         => NdtcOrder::STATUS_MANUAL_REVIEW,
            'AUTO_APPROVED',
            'AUTO_APPROVED_WITH_CORRECTIONS',
            'MANUALLY_APPROVED',
            'COMPLETED'                      => NdtcOrder::STATUS_APPROVED,
            'AUTO_REJECTED',
            'MANUALLY_REJECTED'              => NdtcOrder::STATUS_REJECTED,
            'CANCELLED'                      => NdtcOrder::STATUS_CANCELLED,
            'TITLE_TERMINATED'               => NdtcOrder::STATUS_TITLE_TERMINATED,
            'TITLE_REACTIVATED'              => NdtcOrder::STATUS_APPROVED,
            default                          => $ndtcOrderStatus,
        };
    }
}
