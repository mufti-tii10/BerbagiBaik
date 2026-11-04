<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->text('receiver_note')->nullable()->after('status');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('fund_request_id')->nullable()->after('item_id')->constrained('fund_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fund_request_id');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn('receiver_note');
        });
    }
};
