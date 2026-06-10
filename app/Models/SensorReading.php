<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = [
        'node_id',
        'kelembaban_tanah',
        'kelembaban_tanah_1',
        'kelembaban_tanah_2',
        'suhu',
        'kelembaban_udara',
        'ph_air',
        'debit_air',
    ];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }
}
