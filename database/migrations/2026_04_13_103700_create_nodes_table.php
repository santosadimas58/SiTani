<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->string('kode_node')->unique();
            $table->string('nama_node');
            $table->string('lokasi')->nullable();
            $table->enum('status', ['Aktif', 'Nonaktif'])->default('Aktif');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('nodes'); }
};
