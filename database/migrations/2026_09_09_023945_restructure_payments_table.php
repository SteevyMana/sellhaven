<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['reference', 'type']);
            $table->foreignId('order_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_id')->nullable()->after('order_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
            $table->dropConstrainedForeignId('purchase_id');
            $table->string('reference')->nullable();
            $table->string('type')->nullable();
        });
    }
};