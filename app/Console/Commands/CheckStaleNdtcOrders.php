<?php
// app/Console/Commands/CheckStaleNdtcOrders.php

namespace App\Console\Commands;

use App\Models\NdtcOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckStaleNdtcOrders extends Command
{
    protected $signature   = 'ndtc:check-stale';
    protected $description = 'Flag NDTC orders stuck in DRAFT with no RFD received';

    public function handle(): void
    {
        // Orders created on NDTC but no RFD received after 10 minutes
        $stale = NdtcOrder::where('status', NdtcOrder::STATUS_DRAFT)
            ->whereNotNull('ndtc_order_id')
            ->where('created_at', '<', now()->subMinutes(10))
            ->get();

        foreach ($stale as $order) {
            Log::warning('NDTC order stuck in DRAFT — no RFD received', [
                'order_id'      => $order->id,
                'ndtc_order_id' => $order->ndtc_order_id,
                'created_at'    => $order->created_at,
            ]);
        }

        $this->info("Found {$stale->count()} stale orders.");
    }
}
