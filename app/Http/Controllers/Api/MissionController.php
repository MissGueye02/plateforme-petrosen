<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Models\Mission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MissionController extends Controller
{
    // GET /api/mes-missions
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $missions = Mission::with(['ingenieur','chefProjet'])
            ->where('ingenieur_id', $user->id)
            ->orWhere(function ($q) use ($user) {
                $q->where('chef_projet_id', $user->id);
            })
            ->get();

        $interventions = Intervention::with(['alerte','ingenieurAffecte','validePar'])
            ->where('ingenieur_affecte_id', $user->id)
            ->get();

        return response()->json(['missions' => $missions, 'interventions' => $interventions]);
    }

    // PATCH /api/missions/{mission}/accepter
    public function accepter(Request $request, Mission $mission): JsonResponse
    {
        $user = $request->user();
        if ($mission->ingenieur_id !== $user->id) {
            return response()->json(['message' => 'Non autorise.'], 403);
        }

        $mission->statut = 'acceptee';
        $mission->save();

        return response()->json(['message' => 'Mission acceptee.', 'mission' => $mission]);
    }

    // PATCH /api/missions/{mission}/refuser
    public function refuser(Request $request, Mission $mission): JsonResponse
    {
        $user = $request->user();
        if ($mission->ingenieur_id !== $user->id) {
            return response()->json(['message' => 'Non autorise.'], 403);
        }

        $mission->statut = 'refusee';
        $mission->save();

        return response()->json(['message' => 'Mission refusee.', 'mission' => $mission]);
    }
}
