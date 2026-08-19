<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\Rapport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    /** GET /api/rapports */
    public function index(): JsonResponse
    {
        $rapports = Rapport::with('generePar')->latest()->get();
        return response()->json($rapports);
    }

    /** POST /api/rapports - genere un nouveau rapport */
    public function generer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titre'        => 'required|string|max:255',
            'periode_debut'=> 'required|date',
            'periode_fin'  => 'required|date|after_or_equal:periode_debut',
        ]);

        // Agregation des donnees de production sur la periode
        $productions = Production::with(['puits.blocPetrolier'])
            ->whereBetween('date_production', [$data['periode_debut'], $data['periode_fin']])
            ->get();

        $volumeTotal = $productions->sum('volume');
        $pressionMoy = $productions->avg('pression');

        $parBloc = $productions->groupBy('puits.bloc_petrolier_id')->map(function ($prods) {
            $bloc = $prods->first()->puits->blocPetrolier;
            return [
                'bloc'   => $bloc ? $bloc->nom : 'Inconnu',
                'volume' => round($prods->sum('volume'), 2),
                'nb'     => $prods->count(),
            ];
        })->values();

        $parPuits = $productions->groupBy('puits_id')->map(function ($prods) {
            $puits = $prods->first()->puits;
            return [
                'puits'  => $puits ? $puits->nom : 'Inconnu',
                'volume' => round($prods->sum('volume'), 2),
                'nb'     => $prods->count(),
            ];
        })->values();

        $contenu = [
            'resume' => [
                'nb_productions'  => $productions->count(),
                'volume_total'    => round($volumeTotal, 2),
                'pression_moyenne'=> round($pressionMoy, 2),
            ],
            'par_bloc'  => $parBloc,
            'par_puits' => $parPuits,
            'nb_alertes'=> $productions->where('niveau', 'anormal')->count(),
        ];

        $rapport = Rapport::create([
            'titre'        => $data['titre'],
            'periode_debut'=> $data['periode_debut'],
            'periode_fin'  => $data['periode_fin'],
            'user_id'      => $request->user()->id,
            'contenu'      => $contenu,
            'statut'       => 'genere',
        ]);

        return response()->json($rapport, 201);
    }

    /** GET /api/rapports/{rapport} */
    public function show(Rapport $rapport): JsonResponse
    {
        return response()->json($rapport->load('generePar'));
    }
}
