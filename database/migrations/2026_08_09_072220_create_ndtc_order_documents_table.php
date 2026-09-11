<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ndtc_order_documents', function (Blueprint $table) {
            $table->id();

            // ── RELATIONSHIP ──────────────────────────────────────
            $table->unsignedBigInteger('ndtc_order_id')->index();
            $table->foreign('ndtc_order_id')
                  ->references('id')
                  ->on('ndtc_orders')
                  ->onDelete('cascade');

            // ── NDTC REFERENCE ────────────────────────────────────
            $table->unique('ndtc_document_id')->nullable()->index();
            // Assigned by CHAMP after create document API call
            // Needed for GET, DELETE operations on the document

            $table->string('document_content', 80)->index();
            // TITLE_FRONT_AND_BACK, POWER_OF_ATTORNEY,
            // CERTIFICATE_OF_COMPLETION, ODOMETER_DISCLOSURE,
            // SECURE_ELECTRONIC_POWER_OF_ATTORNEY, OTHER_EVIDENCE etc.

            // ── FILE INFO ─────────────────────────────────────────
            $table->string('file_display_name', 150)->nullable();
            $table->string('file_mime_type', 50)->nullable();
            // application/pdf, image/jpeg, image/png
            $table->unsignedBigInteger('file_size_bytes')->nullable();

            // ── STATUS ────────────────────────────────────────────
            $table->string('status', 30)->default('PENDING')->index();
            // PENDING → UPLOADING → UPLOADED → FAILED → REPLACED

            $table->boolean('is_system_generated')->default(true);
            // CHAMP-generated docs e.g. CLEARINGHOUSE_TITLE_APPLICATION
            // Agent must never be allowed to delete these

            $table->unsignedTinyInteger('upload_attempts')->default(0);
            $table->timestamp('uploaded_at')->nullable();
            $table->text('upload_error')->nullable();
            // Store error message if upload fails — useful for debugging

            // NOTE: Never store presigned URLs here
            // Upload URL expires in 5 min
            // Download URL expires in 10 min
            // Always fetch fresh from CHAMP API on demand

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ndtc_order_documents');
    }
};
