<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('deposit');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('deposit');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->unsignedInteger('deposit')->default(0);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('deposit')->default(0);
        });
    }
};
