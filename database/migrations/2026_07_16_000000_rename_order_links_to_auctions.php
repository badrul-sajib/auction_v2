<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('order_links', 'auctions');

        Schema::table('auctions', function (Blueprint $table) {
            $table->string('title')->nullable()->after('product_id');
            $table->unsignedInteger('stock')->nullable()->after('title'); // null = unlimited
        });
    }

    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropColumn(['title', 'stock']);
        });

        Schema::rename('auctions', 'order_links');
    }
};
