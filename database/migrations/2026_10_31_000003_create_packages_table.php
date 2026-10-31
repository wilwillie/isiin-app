<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('room_type');
            $table->string('style');
            $table->unsignedInteger('price');                          // harga sewa per periode
            $table->unsignedSmallInteger('duration_days')->default(7); // panjang 1 periode (hari)
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('installation_fee')->default(0);
            $table->unsignedInteger('deposit')->default(0);            // dapat dikembalikan
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_items');
        Schema::dropIfExists('packages');
    }
};
