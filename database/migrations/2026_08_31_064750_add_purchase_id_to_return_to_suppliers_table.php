<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('return_to_suppliers', 'purchase_id')) {
            Schema::table('return_to_suppliers', function (Blueprint $table) {
                $table->foreignId('purchase_id')
                      ->nullable()
                      ->after('supplier_id')
                      ->constrained()
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('return_to_suppliers', 'purchase_id')) {
            Schema::table('return_to_suppliers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('purchase_id');
            });
        }
    }
};
