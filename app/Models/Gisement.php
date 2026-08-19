<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nom','localisation','statut_licence','bloc_petrolier_id','latitude','longitude'])]
class Gisement extends Model
{
    use HasFactory;

    public function blocPetrolier()
    {
        return $this->belongsTo(BlocPetrolier::class, 'bloc_petrolier_id');
    }
}
