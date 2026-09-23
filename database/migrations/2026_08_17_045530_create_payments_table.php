<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference');                          // ej. SALE-001, ORDER-002
            $table->enum('type', ['Sale', 'Order', 'Purchase']);
            $table->string('method');                              // Cash, Credit Card, Transfer...
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['Paid', 'Pending', 'Failed'])->default('Pending');
            $table->date('date');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
