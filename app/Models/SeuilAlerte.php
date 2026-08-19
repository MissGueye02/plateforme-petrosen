<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['puits_id','valeur_seuil','valeur_pression','configure_par_id'])]
class SeuilAlerte extends Model
{
    use HasFactory;

    public function puits()
    {
        return $this->belongsTo(Puits::class, 'puits_id');
    }

    public function configurePar()
    {
        return $this->belongsTo(User::class, 'configure_par_id');
    }
}
