<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    protected $fillable = ['kode_node', 'nama_node', 'lokasi', 'status'];

    public function sensorReadings()
    {
        return $this->hasMany(SensorReading::class);
    }

    public function latestReading()
    {
        return $this->hasOne(SensorReading::class)->latestOfMany();
    }

    public function pumpControls()
    {
        return $this->hasMany(PumpControl::class);
    }

    public function latestPump()
    {
        return $this->hasOne(PumpControl::class)->latestOfMany();
    }
}
