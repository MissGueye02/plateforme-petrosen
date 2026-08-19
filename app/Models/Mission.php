<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['titre','description','statut','ingenieur_id','chef_projet_id','intervention_id','date'])]
class Mission extends Model
{
    use HasFactory;

    protected $dates = ['date'];

    public function ingenieur()
    {
        return $this->belongsTo(User::class, 'ingenieur_id');
    }

    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'intervention_id');
    }

    public function chefProjet()
    {
        return $this->belongsTo(User::class, 'chef_projet_id');
    }
}
