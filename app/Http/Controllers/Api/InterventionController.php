<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Intervention;
use App\Models\Notification;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    // POST /api/alertes/{alerte}/planifier
    public function planifier(Request $request, Alerte $alerte): JsonResponse
    {
        $data = $request->validate([
            'ingenieur_id' => 'required|exists:utilisateurs,id',
            'date_planifiee' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        if ($alerte->statut !== 'nouvelle') {
            return response()->json(['message' => 'Cette alerte n’est plus disponible pour une intervention.'], 422);
        }

        $engineer = User::find($data['ingenieur_id']);
        if (! $engineer || $engineer->roleCode() !== 'ingenieur_terrain') {
            return response()->json(['message' => 'L’intervention doit être affectée à un ingénieur de terrain.'], 422);
        }

        // create intervention
        $intervention = Intervention::create([
            'description' => $data['description'] ?? null,
            'date_planifiee' => $data['date_planifiee'] ?? null,
            'statut' => 'planifiee',
            'alerte_id' => $alerte->id,
            'ingenieur_affecte_id' => $data['ingenieur_id'],
        ]);

        $alerte->update(['statut' => 'traitee']);

        $mission = Mission::create([
            'titre' => 'Intervention sur alerte #' . $alerte->id,
            'description' => $data['description'] ?? ('Intervention planifiée pour l’alerte #' . $alerte->id),
            'statut' => 'proposee',
            'ingenieur_id' => $engineer->id,
            'chef_projet_id' => $request->user()->id,
            'intervention_id' => $intervention->id,
            'date' => isset($data['date_planifiee']) ? substr($data['date_planifiee'], 0, 10) : null,
        ]);

        // create notification for the assigned engineer
        if ($engineer) {
            Notification::create([
                'utilisateur_id' => $engineer->id,
                'message' => 'Vous avez été affecté(e) à une intervention pour l\'alerte #' . $alerte->id . '.',
                'lu' => false,
                'type' => 'intervention_planifiee',
            ]);
        }

        return response()->json($intervention->load('alerte','ingenieurAffecte','mission'), 201);
    }

    // PATCH /api/interventions/{intervention}/valider
    public function valider(Request $request, Intervention $intervention): JsonResponse
    {
        $intervention->statut = 'validee';
        $intervention->valide_par_id = $request->user()->id;
        $intervention->save();

        // notify the assigned engineer that intervention is validated
        if ($intervention->ingenieur_affecte_id) {
            Notification::create([
                'utilisateur_id' => $intervention->ingenieur_affecte_id,
                'message' => 'Votre intervention #' . $intervention->id . ' a été validée par le chef de projet.',
                'lu' => false,
                'type' => 'intervention_validee',
            ]);
        }

        return response()->json($intervention->fresh()->load('alerte','ingenieurAffecte','validePar'));
    }
}
