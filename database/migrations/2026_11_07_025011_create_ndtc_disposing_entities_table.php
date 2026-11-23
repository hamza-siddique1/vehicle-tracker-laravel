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
        Schema::create('ndtc_disposing_entities', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('address_line_1')->nullable();
            $t->string('address_line_2')->nullable();
            $t->string('city')->nullable();
            $t->string('state', 2)->nullable();
            $t->string('zip', 10)->nullable();
            $t->string('county')->nullable();
            $t->string('phone_number')->nullable();
            $t->string('phone_type')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ndtc_disposing_entities');
    }
};
