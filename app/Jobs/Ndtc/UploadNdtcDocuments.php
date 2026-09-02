<?php
// app/Jobs/Ndtc/UploadNdtcDocuments.php

namespace App\Jobs\Ndtc;

use App\Exceptions\Ndtc\NdtcApiException;
use App\Models\NdtcOrder;
use App\Models\NdtcOrderDocument;
use App\Services\Ndtc\NdtcApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
            // Step 1 — call NDTC create document → get presigned S3 details
            $response = $api->createDocument($this->order->ndtc_order_id, [
                'documentContent' => $this->document->document_content,
                'fileReference'   => [
                    'mimeType' => $this->document->file_mime_type,
                ],
            ]);

            // Parse the actual response structure
            $presignedPostDetails = $response['preSignedPostDetails']
                ?? throw new \Exception('NDTC response missing preSignedPostDetails');

            $presignedUrl   = $presignedPostDetails['url']
                ?? throw new \Exception('NDTC response missing presigned URL');

            $fields         = $presignedPostDetails['fields']
                ?? throw new \Exception('NDTC response missing presigned fields');

            $ndtcDocumentId = $fields['x-amz-meta-documentId'] ?? null;

            Log::info('NDTC document presigned URL received', [
                'order_id'        => $this->order->id,
                'document_id'     => $this->document->id,
                'ndtc_request_id' => $response['requestId'] ?? null,
                'ndtc_doc_id'     => $ndtcDocumentId,
            ]);

            // Step 2 — upload file directly to S3 presigned URL
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

            Log::info('NDTC document uploaded successfully', [
                'order_id'    => $this->order->id,
                'document_id' => $this->document->id,
                'ndtc_doc_id' => $ndtcDocumentId,
            ]);

        } catch (NdtcApiException $e) {
        if ($e->isOrderNotFound()) {
            $this->document->update([
                'status'       => NdtcOrderDocument::STATUS_FAILED,
                'upload_error' => 'NDTC order not found (404): ' . $e->errorDetails,
            ]);

            $this->order->update([
                'status' => NdtcOrder::STATUS_NOT_FOUND,
            ]);

            Log::warning('NDTC order not found — marking order as NOT_FOUND, no retry', [
                'order_id'    => $this->order->id,
                'document_id' => $this->document->id,
                'ndtc_order_id' => $this->order->ndtc_order_id,
            ]);

            // Don't rethrow: retrying is pointless if NDTC doesn't recognize the order.
            // This lets the job finish "normally" rather than exhausting retries into failed().
            return;
        }

        // Any other NdtcApiException falls through to the generic handling below.
        $this->handleGenericFailure($e);
        throw $e;

    } catch (\Exception $e) {
        $this->handleGenericFailure($e);
        throw $e;
    }
}

    private function handleGenericFailure(\Exception $e): void
    {
        $this->document->update([
            'status'       => NdtcOrderDocument::STATUS_FAILED,
            'upload_error' => $e->getMessage(),
        ]);

        Log::error('NDTC document upload failed', [
            'order_id'    => $this->order->id,
            'document_id' => $this->document->id,
            'error'       => $e->getMessage(),
        ]);
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
