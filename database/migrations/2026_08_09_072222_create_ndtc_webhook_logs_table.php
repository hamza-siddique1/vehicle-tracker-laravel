<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ndtc_webhook_logs', function (Blueprint $table) {
            $table->id();

            // ── REFERENCES ────────────────────────────────────────
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->foreign('order_id')
                  ->references('id')
                  ->on('ndtc_orders')
                  ->nullOnDelete();
            // Nullable because webhook might arrive before
            // we match it to a local order (edge case)

            $table->string('ndtc_order_id', 64)->nullable()->index();
            // CHAMP order ID — stored separately so we can
            // log webhooks even if local order lookup fails

            // ── WEBHOOK DATA ──────────────────────────────────────
            $table->string('event', 60)->index();
            // READY_FOR_DOCUMENTS, READY_TO_FINALIZE, PROCESSING,
            // ORDER_APPROVED, ORDER_REJECTED, MANUAL_REVIEW,
            // ON_HOLD, AGING_ORDER, ORDER_CANCELLED, TITLE_TERMINATED

            $table->string('ndtc_status', 60)->nullable();
            // Raw status from payload e.g. AUTO_REJECTED, MANUALLY_APPROVED

            $table->json('payload');
            // Full raw webhook payload — never truncate this
            // You will need it when debugging rejections with CHAMP support

            // ── SECURITY ──────────────────────────────────────────
            $table->string('signature', 150)->nullable();
            // x-signature-256 header value for audit trail
            $table->boolean('signature_verified')->default(false);

            // ── PROCESSING ────────────────────────────────────────
            $table->boolean('processed')->default(false)->index();
            $table->text('process_error')->nullable();
            $table->unsignedTinyInteger('process_attempts')->default(0);
            $table->timestamp('processed_at')->nullable();

            $table->timestamp('received_at');
            // Separate from created_at — records exact moment
            // webhook hit your endpoint before any queue delay

            $table->timestamps();
        });
        // No softDeletes on webhook logs — these are an audit trail
        // and should never be deleted
    }

    public function down(): void
    {
        Schema::dropIfExists('ndtc_webhook_logs');
    }
};
