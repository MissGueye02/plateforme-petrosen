<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['utilisateur_id', 'message', 'lu', 'type'])]
class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $casts = [
        'lu' => 'boolean',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
