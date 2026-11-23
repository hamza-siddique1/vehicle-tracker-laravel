<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_titles', function (Blueprint $t) {
            $t->id();
            $t->integer('vehicle_id')->index();
            $t->string('title_number', 50)->nullable();
            $t->date('received_date')->nullable();
            $t->string('reassignment_number')->nullable();
            $t->date('sent_to_auction_date')->nullable();
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        // One history row per vehicle for the title data that used to live in vehicle_metas.
        // The old meta rows are left untouched.
        $keys = ['title_received_date', 'reassignment_number', 'title_sent_to_auction_date'];

        $byVehicle = DB::table('vehicle_metas')
            ->whereIn('meta_key', $keys)
            ->whereNull('deleted_at')
            ->whereNotNull('meta_value')
            ->where('meta_value', '!=', '')
            ->get()
            ->groupBy('vehicle_id');

        $asDate = function ($v) {
            $v = trim((string) $v);
            return preg_match('/^\d{4}-\d{2}-\d{2}/', $v) ? substr($v, 0, 10) : null;
        };

        foreach ($byVehicle as $vehicleId => $rows) {
            $m = $rows->pluck('meta_value', 'meta_key');

            DB::table('vehicle_titles')->insert([
                'vehicle_id'           => $vehicleId,
                'received_date'        => $asDate($m['title_received_date'] ?? null),
                'reassignment_number'  => $m['reassignment_number'] ?? null,
                'sent_to_auction_date' => $asDate($m['title_sent_to_auction_date'] ?? null),
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_titles');
    }
};
