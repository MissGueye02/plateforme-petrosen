<?php

namespace App\Services;

use App\Models\Alerte;
use App\Models\Notification;
use App\Models\Production;
use App\Models\Puits;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProductionService
{
    /**
     * Saisie -> enregistrement -> vérification du seuil -> alerte éventuelle.
     */
    public function enregistrerSaisie(array $data, User $ingenieur): array
    {
        return DB::transaction(function () use ($data, $ingenieur) {
            $puits = Puits::findOrFail($data['puits_id']);

            $production = Production::create([
                'puits_id' => $puits->id,
                'user_id' => $ingenieur->id,
                'date_production' => $data['date_production'],
                'volume' => $data['volume'],
                'pression' => $data['pression'],
                'niveau' => 'normal',
            ]);

            $estAnormal = $puits->depasseSeuil(
                (float) $data['volume'],
                (float) $data['pression']
            );

            $production->update([
                'niveau' => $estAnormal ? 'anormal' : 'normal',
            ]);

            $alerte = $estAnormal
                ? $this->declencherAlerte($puits, $production, $ingenieur)
                : null;

            return [
                'production' => $production->fresh(['puits']),
                'alerte' => $alerte?->load('puits'),
            ];
        });
    }

    protected function declencherAlerte(Puits $puits, Production $production, User $ingenieur): Alerte
    {
        $message = sprintf(
            'Seuil dépassé sur le puits %s le %s : volume=%s (seuil %s), pression=%s (seuil %s).',
            $puits->nom,
            $production->date_production->format('d/m/Y'),
            $production->volume,
            $puits->seuil_volume,
            $production->pression,
            $puits->seuil_pression
        );

        $alerte = Alerte::create([
            'puits_id' => $puits->id,
            'production_id' => $production->id,
            'type' => 'depassement_seuil',
            'message' => $message,
            'statut' => 'nouvelle',
        ]);

        $destinataires = User::whereHas('role', function ($query) {
            $query->whereIn('code', ['administrateur', 'chef_projet'])
                ->orWhereIn('libelle', ['administrateur', 'chef de projet']);
        })->get();

        foreach ($destinataires->push($ingenieur)->unique('id') as $destinataire) {
            Notification::create([
                'utilisateur_id' => $destinataire->id,
                'message' => $message,
                'lu' => false,
                'type' => 'alerte_production',
            ]);
        }

        return $alerte;
    }
}
