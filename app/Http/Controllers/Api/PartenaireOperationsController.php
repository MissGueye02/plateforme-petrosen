<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartenaireOperationsController extends Controller
{
    // GET /api/partenaire/operations
    public function index(Request $request): JsonResponse
    {
        // Aggregate production by bloc, anonymized (no puits names)
        $byBloc = DB::table('productions')
            ->join('puits', 'productions.puits_id', '=', 'puits.id')
            ->join('blocs_petroliers', 'puits.bloc_petrolier_id', '=', 'blocs_petroliers.id')
            ->select('blocs_petroliers.id as bloc_id', 'blocs_petroliers.nom as bloc', DB::raw('SUM(productions.volume) as volume_total'), DB::raw('AVG(productions.pression) as pression_moyenne'), DB::raw('COUNT(productions.id) as nb_productions'))
            ->groupBy('blocs_petroliers.id', 'blocs_petroliers.nom')
            ->get();

        // Overall totals
        $totals = DB::table('productions')->select(DB::raw('SUM(volume) as volume_total'), DB::raw('AVG(pression) as pression_moyenne'), DB::raw('COUNT(id) as nb_productions'))->first();

        return response()->json(['by_bloc' => $byBloc, 'totals' => $totals]);
    }
}
