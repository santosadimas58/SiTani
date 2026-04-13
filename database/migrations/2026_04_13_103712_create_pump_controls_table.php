<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pump_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('nodes')->onDelete('cascade');
            $table->enum('status', ['ON', 'OFF'])->default('OFF');
            $table->string('triggered_by')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pump_controls'); }
};
