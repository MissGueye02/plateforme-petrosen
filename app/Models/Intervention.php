<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['description','date_planifiee','statut','alerte_id','ingenieur_affecte_id','valide_par_id'])]
class Intervention extends Model
{
    use HasFactory;

    protected $dates = ['date_planifiee'];

    public function alerte()
    {
        return $this->belongsTo(Alerte::class, 'alerte_id');
    }

    public function ingenieurAffecte()
    {
        return $this->belongsTo(User::class, 'ingenieur_affecte_id');
    }

    public function mission()
    {
        return $this->hasOne(Mission::class, 'intervention_id');
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par_id');
    }
}
