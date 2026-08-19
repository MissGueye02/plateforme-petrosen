<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\Puits;
use App\Models\Rapport;
use App\Models\User;
use App\Models\BlocPetrolier;
use App\Models\Alerte;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    // GET /api/stats/global
    public function global(): JsonResponse
    {
        $nbUsers = User::count();
        $nbPuitsActifs = Puits::where('statut', 'actif')->count();
        $nbRapports = Rapport::count();
        $nbBlocs = BlocPetrolier::count();
        $nbProductions = Production::count();
        $nbAlertesActives = Alerte::where('statut', 'active')->count();

        return response()->json([
            'users' => $nbUsers,
            'puits_actifs' => $nbPuitsActifs,
            'rapports' => $nbRapports,
            'blocs' => $nbBlocs,
            'productions' => $nbProductions,
            'alertes_actives' => $nbAlertesActives,
        ]);
    }
}
