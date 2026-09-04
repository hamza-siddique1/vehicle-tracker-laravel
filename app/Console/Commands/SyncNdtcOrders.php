<?php

namespace App\Console\Commands;

use App\Actions\Ndtc\SyncOrderFromChamp;
use App\Models\NdtcOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncNdtcOrders extends Command
{
    protected $signature = 'ndtc:sync-orders {--delay=500 : Milliseconds to wait between API calls}';

    protected $description = 'Sync all non-terminal NDTC orders against the CHAMP API';

    public function handle(SyncOrderFromChamp $sync): int
    {
        $terminalStatuses = ['COMPLETED', 'CANCELLED', 'APPROVED', 'TITLE_TERMINATED'];

        $orders = NdtcOrder::whereNotNull('ndtc_order_id')
            ->whereNotIn('status', $terminalStatuses)
            ->get();

        $this->info("Syncing {$orders->count()} orders...");

        $succeeded = 0;
        $failed    = 0;
        $delayMs   = (int) $this->option('delay');

        foreach ($orders as $order) {
            try {
                $sync->execute($order);
                $succeeded++;
                $this->line("✓ Synced order {$order->id} (VIN: {$order->vin})");
            } catch (\Exception $e) {
                $failed++;
                $this->error("✗ Failed order {$order->id} (VIN: {$order->vin}): {$e->getMessage()}");
                Log::error('NDTC daily sync failed for order', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }

            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        $this->info("Done. {$succeeded} succeeded, {$failed} failed.");

        return self::SUCCESS;
    }
}
