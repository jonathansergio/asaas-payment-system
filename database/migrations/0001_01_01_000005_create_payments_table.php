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
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->decimal('value', 10, 2);
            $table->enum('payment_method', ['BOLETO', 'CREDIT_CARD', 'PIX']);
            $table->string('status')->default('PENDING');
            $table->string('invoice_url')->nullable();
            $table->string('pix_qr_code')->nullable();
            $table->string('pix_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};