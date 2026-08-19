<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use App\Models\Rapport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChefProjetController extends Controller
{

    public function partenaires(): JsonResponse
    {
        return response()->json(\App\Models\User::with('role')
            ->whereHas('role', function ($query) {
                $query->where('code', 'partenaire')->orWhere('libelle', 'partenaire');
            })
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom', 'mail', 'role_id']));
    }

    public function ingenieurs(): JsonResponse
    {
        return response()->json(\App\Models\User::with('role')->whereHas('role', function ($query) {
            $query->where('code', 'ingenieur_terrain');
        })->orderBy('nom')->get(['id','nom','prenom','mail','role_id']));
    }

    public function rapports(): JsonResponse
    {
        return response()->json(
            Rapport::with(['generePar', 'mission', 'production', 'projet', 'commentaires.utilisateur'])
                ->whereIn('statut', ['soumis', 'a_corriger', 'valide', 'rejete'])
                ->latest()
                ->get()
        );
    }

    public function validerRapport(Request $request, Rapport $rapport): JsonResponse
    {
        if ($rapport->statut === 'valide') {
            return response()->json(['message' => 'Le rapport est déjà validé.'], 422);
        }

        $rapport->update(['statut' => 'valide']);

        return response()->json([
            'message' => 'Rapport validé avec succès.',
            'rapport' => $rapport->fresh()->load('generePar'),
        ]);
    }

    public function rejeterRapport(Request $request, Rapport $rapport): JsonResponse
    {
        $data = $request->validate([
            'commentaire' => 'required|string|max:2000',
        ]);

        $rapport->update(['statut' => 'a_corriger']);

        $rapport->commentaires()->create([
            'contenu' => $data['commentaire'],
            'utilisateur_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Une correction a été demandée à l’ingénieur.',
            'rapport' => $rapport->fresh()->load('commentaires'),
        ]);
    }

    public function creerProjet(Request $request, Rapport $rapport): JsonResponse
    {
        if ($rapport->statut !== 'valide') {
            return response()->json([
                'message' => 'Seul un rapport validé peut générer un projet.',
            ], 422);
        }

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'partenaire_id' => 'nullable|exists:utilisateurs,id',
        ]);

        $projet = DB::transaction(function () use ($data, $rapport) {
            return Projet::updateOrCreate(
                ['rapport_id' => $rapport->id],
                [
                    'nom' => $data['nom'],
                    'description' => $data['description'] ?? null,
                    'statut' => 'en_attente',
                    'partenaire_id' => $data['partenaire_id'] ?? null,
                ]
            );
        });

        return response()->json([
            'message' => 'Projet créé à partir du rapport validé.',
            'projet' => $projet->load('rapport', 'partenaire'),
        ], 201);
    }

    public function publierProjet(Request $request, Projet $projet): JsonResponse
    {
        if (! $projet->partenaire_id) {
            return response()->json([
                'message' => 'Associez un partenaire avant de publier le projet.',
            ], 422);
        }

        if (! $projet->rapport_id) {
            return response()->json([
                'message' => 'Le projet doit provenir d’un rapport.',
            ], 422);
        }

        $projet->update(['statut' => 'valide']);

        return response()->json([
            'message' => 'Projet publié. Il est maintenant visible par le partenaire.',
            'projet' => $projet->fresh()->load('rapport', 'partenaire'),
        ]);
    }
}
