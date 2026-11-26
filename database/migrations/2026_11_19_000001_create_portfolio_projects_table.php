<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->string('room_type');
            $table->string('location', 120);
            $table->unsignedSmallInteger('completion_year');
            $table->text('description');
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->index(['vendor_id', 'completion_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_projects');
    }
};
