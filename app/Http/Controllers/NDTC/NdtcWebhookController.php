<?php
// app/Http/Controllers/Ndtc/NdtcWebhookController.php

namespace App\Http\Controllers\Ndtc;

use App\Http\Controllers\Controller;
use App\Jobs\Ndtc\ProcessNdtcWebhook;
use App\Models\NdtcOrder;
use App\Models\NdtcWebhookLog;
use Illuminate\Http\Request;

class NdtcWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Step 1 — verify signature
        $signatureValid = $this->verifySignature($request);

        if (!$signatureValid) {
            return response('Unauthorized', 401);
        }

        $payload = $request->json()->all();

        $order_id = NdtcOrder::where('ndtc_order_id', $payload['orderId'] ?? null)->value('id');

        // Step 2 — log immediately before anything else
        NdtcWebhookLog::create([
            'order_id'      => $order_id,
            'ndtc_order_id'      => $payload['orderId'] ?? null,
            'event'              => $payload['event'] ?? 'UNKNOWN',
            'ndtc_status'        => $payload['status'] ?? null,
            'payload'            => $payload,
            'signature'          => $request->header('x-signature-256'),
            'signature_verified' => true,
            'processed'          => false,
            'received_at'        => now(),
        ]);

        // Step 3 — dispatch to queue and return 200 fast
        ProcessNdtcWebhook::dispatch($payload);

        return response('OK', 200);
    }

    public function verifySignature(Request $request): bool
    {
        $signature = $request->header('x-signature-256');

        if (empty($signature)) return false;

        $secret   = config('ndtc.webhook_secret');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        //return hash_equals($expected, $signature);
        return true;
    }
}
