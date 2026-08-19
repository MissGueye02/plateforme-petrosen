<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\Rapport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionSuiviController extends Controller
{
    // GET /api/chef/productions?puits_id=&bloc_petrolier_id=&from=&to=
    public function index(Request $request): JsonResponse
    {
        $query = Production::with(['puits', 'puits.blocPetrolier']);

        if ($request->filled('puits_id')) {
            $query->where('puits_id', $request->input('puits_id'));
        }
        if ($request->filled('bloc_petrolier_id')) {
            $query->whereHas('puits', function ($q) use ($request) {
                $q->where('bloc_petrolier_id', $request->input('bloc_petrolier_id'));
            });
        }
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date_production', [$request->input('from'), $request->input('to')]);
        }

        $results = $query->latest()->paginate(50);
        return response()->json($results);
    }

    // GET /api/chef/productions/stats?from=&to=
    public function stats(Request $request): JsonResponse
    {
        $query = Production::query();
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date_production', [$request->input('from'), $request->input('to')]);
        }

        $totalVolume = $query->sum('volume');
        $moyPression = $query->avg('pression');
        $count = $query->count();

        return response()->json([
            'nb_productions' => $count,
            'volume_total' => round((float)$totalVolume, 2),
            'pression_moyenne' => $moyPression ? round((float)$moyPression, 2) : null,
        ]);
    }

    // POST /api/chef/productions/rapport - génère un rapport (titre, periode_debut, periode_fin)
    public function genererRapport(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
        ]);

        $productions = Production::with(['puits.blocPetrolier'])
            ->whereBetween('date_production', [$data['periode_debut'], $data['periode_fin']])
            ->get();

        $volumeTotal = $productions->sum('volume');
        $pressionMoy = $productions->avg('pression');

        $parBloc = $productions->groupBy('puits.bloc_petrolier_id')->map(function ($prods) {
            $bloc = $prods->first()->puits->blocPetrolier;
            return [
                'bloc' => $bloc ? $bloc->nom : 'Inconnu',
                'volume' => round($prods->sum('volume'), 2),
                'nb' => $prods->count(),
            ];
        })->values();

        $contenu = [
            'resume' => [
                'nb_productions' => $productions->count(),
                'volume_total' => round($volumeTotal, 2),
                'pression_moyenne' => round((float)$pressionMoy, 2),
            ],
            'par_bloc' => $parBloc,
        ];

        $rapport = Rapport::create([
            'titre' => $data['titre'],
            'periode_debut' => $data['periode_debut'],
            'periode_fin' => $data['periode_fin'],
            'user_id' => $request->user()->id,
            'contenu' => $contenu,
            'statut' => 'genere',
        ]);

        return response()->json($rapport, 201);
    }
}
