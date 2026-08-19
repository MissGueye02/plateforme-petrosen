<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\BlocPetrolier;
use App\Models\Production;
use App\Models\Puits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TableauBordController extends Controller
{
    /** GET /api/tableau-bord */
    public function index(Request $request): JsonResponse
    {
        $debut = $request->get('debut', now()->startOfMonth()->toDateString());
        $fin   = $request->get('fin',   now()->toDateString());

        // KPIs globaux
        $volumeTotal = Production::whereBetween('date_production', [$debut, $fin])->sum('volume');
        $puitsActifs = Puits::where('statut', 'actif')->count();
        $alertesActives = Alerte::where('statut', 'nouvelle')->count();
        $productionsCount = Production::whereBetween('date_production', [$debut, $fin])->count();

        // Volume par jour (pour graphique)
        $volumeParJour = Production::select(
                DB::raw('date_production as date'),
                DB::raw('SUM(volume) as volume'),
                DB::raw('AVG(pression) as pression_moy')
            )
            ->whereBetween('date_production', [$debut, $fin])
            ->groupBy('date_production')
            ->orderBy('date_production')
            ->get();

        // Volume par bloc
        $volumeParBloc = BlocPetrolier::with(['puits.productions' => function ($q) use ($debut, $fin) {
            $q->whereBetween('date_production', [$debut, $fin]);
        }])
        ->get()
        ->map(function ($bloc) {
            $volume = $bloc->puits->sum(function ($puits) {
                return (float) $puits->productions->sum('volume');
            });

            return [
                'nom' => $bloc->nom,
                'volume' => round((float) $volume, 2),
            ];
        });

        // Dernieres alertes
        $dernieresAlertes = Alerte::with('puits')->latest()->take(5)->get();

        return response()->json([
            'periode'          => ['debut' => $debut, 'fin' => $fin],
            'kpis'             => [
                'volume_total'     => round((float) $volumeTotal, 2),
                'puits_actifs'     => $puitsActifs,
                'alertes_actives'  => $alertesActives,
                'nb_productions'   => $productionsCount,
            ],
            'volume_par_jour'  => $volumeParJour,
            'volume_par_bloc'  => $volumeParBloc,
            'dernieres_alertes'=> $dernieresAlertes,
        ]);
    }
}

