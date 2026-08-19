<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartenaireProjetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projets = Projet::with('rapport')
            ->where('partenaire_id', $request->user()->id)
            ->where('statut', 'valide')
            ->latest()
            ->get();

        return response()->json($projets);
    }

    public function show(Request $request, Projet $projet): JsonResponse
    {
        if ($projet->partenaire_id !== $request->user()->id || $projet->statut !== 'valide') {
            return response()->json(['message' => 'Projet non accessible.'], 403);
        }

        return response()->json($projet->load(['rapport', 'commentaires']));
    }
}
