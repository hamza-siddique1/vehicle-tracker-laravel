<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ndtc_orders', function (Blueprint $table) {
            $table->id();

            // ── RELATIONSHIPS ─────────────────────────────────────
            $table->unsignedBigInteger('vehicle_id')->index();
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('restrict');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            // ── NDTC REFERENCES ───────────────────────────────────
            $table->string('ndtc_order_id', 64)->nullable()->unique()->index();
            $table->string('correlation_id', 64)->unique()->index();

            // ── FILTERABLE / SEARCHABLE / SORTABLE ────────────────
            $table->string('vin', 20)->index();
            $table->string('vehicle_description', 150)->nullable()->index();
            $table->string('transaction_type', 10)->default('TNL')->index();
            $table->string('status', 50)->default('DRAFT')->index();
            $table->string('ndtc_status', 60)->nullable();
            $table->date('transfer_date')->nullable()->index();
            $table->string('new_title_number', 50)->nullable()->index();

            // ── ORDER STATE FLAGS ─────────────────────────────────
            $table->boolean('finalized')->default(false)->index();
            $table->boolean('ready_to_finalize')->default(false);
            $table->unsignedTinyInteger('submission_count')->default(0);
            $table->unsignedTinyInteger('rejection_count')->default(0)->index();

            // ── TIMESTAMPS FOR SORTING / REPORTING ────────────────
            $table->timestamp('finalized_at')->nullable()->index();
            $table->timestamp('approved_at')->nullable()->index();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // ── JSON ──────────────────────────────────────────────
            $table->json('order_payload')->nullable();      // full payload sent to NDTC
            $table->json('rejection_reasons')->nullable();  // latest rejection from CHAMP
            $table->json('last_webhook')->nullable();       // last webhook received

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ndtc_orders');
    }
};
