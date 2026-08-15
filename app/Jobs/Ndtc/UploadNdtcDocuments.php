<?php
// app/Jobs/Ndtc/UploadNdtcDocuments.php

namespace App\Jobs\Ndtc;

use App\Models\NdtcOrder;
use App\Models\NdtcOrderDocument;
use App\Services\Ndtc\NdtcApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadNdtcDocuments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        private NdtcOrder $order,
        private NdtcOrderDocument $document,
        private string $filePath,
    ) {}

    public function handle(NdtcApiService $api): void
    {
        $this->document->update([
            'status'          => NdtcOrderDocument::STATUS_UPLOADING,
            'upload_attempts' => $this->document->upload_attempts + 1,
        ]);

        try {
            // Step 1 — create document record on NDTC → get presigned URL
            $response = $api->createDocument($this->order->ndtc_order_id, [
                'documentContent' => $this->document->document_content,
                'fileType'        => $this->document->file_mime_type,
                'fileDisplayName' => $this->document->file_display_name,
            ]);

            $presignedUrl  = $response['url'];
            $fields        = $response['fields'];
            $ndtcDocumentId = $response['fields']['x-amz-meta-documentId']
                           ?? $response['documentId']
                           ?? null;

            // Step 2 — upload file directly to S3
            if (!file_exists($this->filePath)) {
                throw new \Exception("Temp file not found: {$this->filePath}");
            }

            $api->uploadToPresignedUrl($presignedUrl, $fields, $this->filePath);

            // Step 3 — mark as uploaded
            $this->document->update([
                'status'           => NdtcOrderDocument::STATUS_UPLOADED,
                'ndtc_document_id' => $ndtcDocumentId,
                'uploaded_at'      => now(),
                'upload_error'     => null,
            ]);

            // Step 4 — delete temp file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

        } catch (\Exception $e) {
            $this->document->update([
                'status'       => NdtcOrderDocument::STATUS_FAILED,
                'upload_error' => $e->getMessage(),
            ]);

            Log::error('NDTC document upload failed', [
                'order_id'    => $this->order->id,
                'document_id' => $this->document->id,
                'error'       => $e->getMessage(),
            ]);

            throw $e; // Re-throw so queue retries
        }
    }

    public function failed(\Throwable $e): void
    {
        $this->document->update([
            'status'       => NdtcOrderDocument::STATUS_FAILED,
            'upload_error' => 'All retry attempts failed: ' . $e->getMessage(),
        ]);

        Log::error('UploadNdtcDocuments job permanently failed', [
            'order_id'    => $this->order->id,
            'document_id' => $this->document->id,
            'error'       => $e->getMessage(),
        ]);
    }
}
