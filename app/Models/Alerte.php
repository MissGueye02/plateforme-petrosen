<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerte extends Model
{
    use HasFactory;

    protected $fillable = [
        'puits_id',
        'production_id',
        'type',
        'message',
        'statut',
    ];

    public function puits()
    {
        return $this->belongsTo(Puits::class);
    }

    public function production()
    {
        return $this->belongsTo(Production::class);
    }
}
