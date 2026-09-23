<?php
// database/migrations/2026_08_26_000000_add_purchase_id_to_return_to_suppliers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_to_suppliers', function (Blueprint $table) {
            $table->foreignId('purchase_id')->nullable()->after('supplier_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('return_to_suppliers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_id');
        });
    }
};