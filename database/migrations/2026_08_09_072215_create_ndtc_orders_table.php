// database/migrations/xxxx_create_ndtc_orders_table.php

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

            // ── RELATIONSHIPS ─────────────────────────────
            $table->unsignedBigInteger('vehicle_id')->index();
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('restrict'); // never delete vehicle if order exists

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            // ── NDTC REFERENCES ───────────────────────────
            $table->string('ndtc_order_id', 64)->nullable()->unique()->index();
            $table->string('correlation_id', 64)->unique()->index();

            // ── FILTERABLE / SORTABLE COLUMNS ─────────────
            $table->string('vin', 20)->index();
            $table->string('vehicle_description', 150)->nullable()->index();
            $table->string('transaction_type', 10)->default('TNL')->index();
            $table->string('status', 50)->default('DRAFT')->index();
            $table->string('ndtc_status', 60)->nullable();
            $table->date('transfer_date')->nullable()->index();
            $table->string('new_title_number', 50)->nullable()->index();
            $table->unsignedTinyInteger('submission_count')->default(0);
            $table->unsignedTinyInteger('rejection_count')->default(0)->index();
            $table->boolean('finalized')->default(false)->index();
            $table->boolean('ready_to_finalize')->default(false);

            // ── TIMESTAMPS FOR SORTING ────────────────────
            $table->timestamp('finalized_at')->nullable()->index();
            $table->timestamp('approved_at')->nullable()->index();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // ── JSON PAYLOADS ─────────────────────────────
            $table->json('order_payload')->nullable();
            $table->json('champ_snapshot')->nullable();
            $table->json('rejection_reasons')->nullable();
            $table->json('last_webhook')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ndtc_orders');
    }
};
