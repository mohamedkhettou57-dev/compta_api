<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('student_id')->constrained()->cascadeOnDelete();

        $table->string('reference')->unique();
        $table->date('invoice_date');

        $table->unsignedBigInteger('total_amount');
        $table->unsignedBigInteger('paid_amount')->default(0);
        $table->unsignedBigInteger('due_amount')->default(0);

        $table->string('status')->default('unpaid'); // unpaid|partial|paid
        $table->text('note')->nullable();

        // $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
