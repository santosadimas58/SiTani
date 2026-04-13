<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = ['node_id', 'kelembaban_tanah', 'suhu', 'ph_air', 'debit_air'];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }
}
