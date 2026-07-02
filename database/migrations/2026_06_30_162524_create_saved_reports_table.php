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
        Schema::create('saved_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('metric', ['sales', 'orders', 'customers', 'meals'])->index();
            $table->string('date_range');
            $table->enum('export_format', ['pdf', 'csv', 'xlsx'])->default('pdf');
            $table->json('included_sections')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('active')->index();
            $table->timestamp('generated_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_reports');
    }
};
