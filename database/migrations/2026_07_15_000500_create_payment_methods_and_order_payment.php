<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('instructions')->nullable();
            $table->boolean('requires_transaction')->default(false);
            $table->boolean('requires_agent')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('payment_method_id')->nullable()->after('status')->constrained()->nullOnDelete();
            $table->string('transaction_number')->nullable()->after('payment_method_id');
            $table->string('agent_id')->nullable()->after('transaction_number');
        });

        $now = now();
        DB::table('payment_methods')->insert([
            ['name' => 'bKash', 'requires_transaction' => true, 'requires_agent' => false, 'is_active' => true, 'sort_order' => 1, 'instructions' => 'Send money to 01XXXXXXXXX, then enter the Transaction ID.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Nagad', 'requires_transaction' => true, 'requires_agent' => false, 'is_active' => true, 'sort_order' => 2, 'instructions' => 'Send money to 01XXXXXXXXX, then enter the Transaction ID.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Rocket', 'requires_transaction' => true, 'requires_agent' => false, 'is_active' => true, 'sort_order' => 3, 'instructions' => 'Send money to 01XXXXXXXXX, then enter the Transaction ID.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Due', 'requires_transaction' => false, 'requires_agent' => true, 'is_active' => true, 'sort_order' => 4, 'instructions' => 'Pay on delivery. Enter the Admin/Rider ID collecting the payment.', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_method_id');
            $table->dropColumn(['transaction_number', 'agent_id']);
        });

        Schema::dropIfExists('payment_methods');
    }
};
