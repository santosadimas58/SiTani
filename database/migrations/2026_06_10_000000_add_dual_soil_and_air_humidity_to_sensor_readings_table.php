<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table) {
            $table->float('kelembaban_tanah_1')->nullable()->after('kelembaban_tanah');
            $table->float('kelembaban_tanah_2')->nullable()->after('kelembaban_tanah_1');
            $table->float('kelembaban_udara')->nullable()->after('suhu');
        });
    }

    public function down(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table) {
            $table->dropColumn([
                'kelembaban_tanah_1',
                'kelembaban_tanah_2',
                'kelembaban_udara',
            ]);
        });
    }
};
