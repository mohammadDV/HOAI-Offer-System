<?php

declare(strict_types=1);

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
        Schema::create('hoai_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_group_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->decimal('costs', 14, 2);
            $table->string('zone');
            $table->string('rate');
            $table->json('phases')->default('[]');
            $table->decimal('construction_markup', 5, 2)->default(0);
            $table->decimal('additional_costs', 5, 2)->default(0);
            $table->decimal('vat', 5, 2)->default(19);
            $table->decimal('total', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoai_positions');
    }
};