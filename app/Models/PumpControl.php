<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PumpControl extends Model
{
    protected $fillable = ['node_id', 'status', 'triggered_by'];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }
}
