<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description');
            $table->unsignedBigInteger('target_amount');
            $table->date('deadline');
            $table->string('bank_name', 60);
            $table->string('account_name', 100);
            $table->string('account_number', 30);
            $table->string('photo_url')->nullable();      // foto ilustrasi (publik, opsional)
            $table->string('proof_doc_url');               // dokumen pendukung (privat)
            $table->string('status')->default('pending');  // pending, published, rejected, closed, completed
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_request_id')->constrained('fund_requests')->cascadeOnDelete();
            $table->foreignId('donor_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('proof_photo_url');              // bukti transfer
            $table->string('message', 300)->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('status')->default('pending');   // pending, confirmed, rejected, expired
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
        Schema::dropIfExists('fund_requests');
    }
};
