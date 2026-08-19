<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puits extends Model
{
    use HasFactory;

    protected $table = 'puits';

    protected $fillable = [
        'nom',
        'bloc_petrolier_id',
        'pression',
        'profondeur',
        'seuil_volume',
        'seuil_pression',
        'statut',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'pression'       => 'decimal:2',
        'profondeur'     => 'decimal:2',
        'seuil_volume'   => 'decimal:2',
        'seuil_pression' => 'decimal:2',
    ];

    /* ------------------------------------------------------------------ */
    /* Relations                                                            */
    /* ------------------------------------------------------------------ */

    public function blocPetrolier()
    {
        return $this->belongsTo(BlocPetrolier::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }

    /* ------------------------------------------------------------------ */
    /* Logique métier                                                       */
    /* ------------------------------------------------------------------ */

    /**
     * Vérifie si une mesure donnée dépasse le(s) seuil(s) du puits.
     * Retourne true si anormal, false si normal.
     */
    public function depasseSeuil(float $volume, float $pression): bool
    {
        return $volume > (float) $this->seuil_volume
            || $pression > (float) $this->seuil_pression;
    }
}
