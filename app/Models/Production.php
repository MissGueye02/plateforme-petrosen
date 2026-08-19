<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'puits_id',
        'user_id',
        'date_production',
        'volume',
        'pression',
        'niveau',
    ];

    protected $casts = [
        'date_production' => 'date',
        'volume' => 'decimal:2',
        'pression' => 'decimal:2',
    ];

    public function puits()
    {
        return $this->belongsTo(Puits::class);
    }

    public function ingenieur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function alerte()
    {
        return $this->hasOne(Alerte::class);
    }
}
