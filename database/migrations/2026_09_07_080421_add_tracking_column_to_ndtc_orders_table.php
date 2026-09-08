<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ndtc_orders', function (Blueprint $table) {
            $table->boolean('is_aging')->default(false)->after('ready_to_finalize');
            $table->timestamp('aging_since')->nullable()->after('is_aging');

            $table->timestamp('ready_for_documents_at')->nullable()->after('aging_since');
            $table->timestamp('ready_to_finalize_at')->nullable()->after('ready_for_documents_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ndtc_orders', function (Blueprint $table) {
            //
        });
    }
};
