<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nom', 'description', 'statut', 'partenaire_id', 'rapport_id'])]
class Projet extends Model
{
    use HasFactory;

    public function partenaire()
    {
        return $this->belongsTo(User::class, 'partenaire_id');
    }

    public function rapport()
    {
        return $this->belongsTo(Rapport::class, 'rapport_id');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }
}
