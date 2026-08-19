<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['contenu','commentable_type','commentable_id','utilisateur_id'])]
class Commentaire extends Model
{
    use HasFactory;

    public function commentable()
    {
        return $this->morphTo();
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
