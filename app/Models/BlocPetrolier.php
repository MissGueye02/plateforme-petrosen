<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlocPetrolier extends Model
{
    use HasFactory;

    protected $table = 'blocs_petroliers';

    protected $fillable = [
        'nom',
        'statut',
        'superficie',
        'localisation',
        'latitude',
        'longitude',
    ];

    public function puits()
    {
        return $this->hasMany(Puits::class);
    }
}
