<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('accounts', function (Blueprint $table) {
            $table->date('dormant_since')->nullable()->after('pnd_placed_at');
            $table->date('closed_at')->nullable()->after('dormant_since');
            $table->string('closure_reason', 500)->nullable()->after('closed_at');
            $table->unsignedBigInteger('closed_by')->nullable()->after('closure_reason');
            $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['dormant_since','closed_at','closure_reason','closed_by']);
        });
    }
};
