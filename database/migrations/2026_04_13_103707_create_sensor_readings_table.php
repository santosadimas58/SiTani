<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('nodes')->onDelete('cascade');
            $table->float('kelembaban_tanah')->nullable();
            $table->float('suhu')->nullable();
            $table->float('ph_air')->nullable();
            $table->float('debit_air')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sensor_readings'); }
};
