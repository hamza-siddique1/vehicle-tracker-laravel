<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ndtc_rejection_histories', function (Blueprint $table) {
            $table->id();

            // ── RELATIONSHIP ──────────────────────────────────────
            $table->unsignedBigInteger('ndtc_order_id')->index();
            $table->foreign('ndtc_order_id')
                  ->references('id')
                  ->on('ndtc_orders')
                  ->onDelete('cascade');

            // ── REJECTION DETAILS ─────────────────────────────────
            $table->unsignedTinyInteger('submission_number');
            // Which attempt this rejection was for
            // e.g. 1 = first submission rejected, 2 = second etc.

            $table->string('ndtc_status', 60)->nullable();
            // AUTO_REJECTED or MANUALLY_REJECTED

            $table->json('rejection_reasons');
            // Full rejections[] array from CHAMP webhook
            // [{ element, code, reasons: [] }]

            $table->json('webhook_payload')->nullable();
            // Full webhook at time of rejection
            // Useful when disputing a rejection with CHAMP support

            $table->timestamp('rejected_at');

            $table->timestamps();
            // No softDeletes — rejection history must never be deleted
            // It is a permanent audit trail of all DMV decisions
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ndtc_rejection_history');
    }
};
