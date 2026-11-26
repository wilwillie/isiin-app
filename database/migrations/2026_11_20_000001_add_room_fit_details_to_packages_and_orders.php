<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('minimum_room_length', 8, 2)->nullable();
            $table->decimal('minimum_room_width', 8, 2)->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('room_length', 8, 2)->nullable();
            $table->decimal('room_width', 8, 2)->nullable();
            $table->decimal('ceiling_height', 8, 2)->nullable();
            $table->json('room_photos')->nullable();
            $table->text('vendor_request_note')->nullable();
            $table->text('customer_response')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'room_length',
                'room_width',
                'ceiling_height',
                'room_photos',
                'vendor_request_note',
                'customer_response',
            ]);
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['minimum_room_length', 'minimum_room_width']);
        });
    }
};
