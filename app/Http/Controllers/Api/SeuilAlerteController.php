<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SeuilAlerte;
use App\Models\Puits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeuilAlerteController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(SeuilAlerte::with(['puits.blocPetrolier'])->latest()->get());
    }

    public function options(): JsonResponse
    {
        return response()->json(Puits::with('blocPetrolier')->where('statut', 'actif')->orderBy('nom')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'puits_id' => 'required|exists:puits,id',
            'valeur_seuil' => 'required|numeric|min:0',
            'valeur_pression' => 'nullable|numeric|min:0',
        ]);
        $data['configure_par_id'] = $request->user()->id;
        $seuil = SeuilAlerte::updateOrCreate(['puits_id' => $data['puits_id']], $data);
        $puits = Puits::findOrFail($data['puits_id']);
        // Garder le seuil utilisé par la détection de production synchronisé.
        $puits->update(['seuil_volume' => $data['valeur_seuil'], 'seuil_pression' => $data['valeur_pression'] ?? $puits->seuil_pression]);
        return response()->json($seuil->fresh()->load('puits'), 201);
    }

    public function show(SeuilAlerte $seuil): JsonResponse
    {
        return response()->json($seuil->load('puits'));
    }

    public function update(Request $request, SeuilAlerte $seuil): JsonResponse
    {
        $data = $request->validate(['valeur_seuil' => 'required|numeric|min:0', 'valeur_pression' => 'nullable|numeric|min:0']);
        $data['configure_par_id'] = $request->user()->id;
        $seuil->update($data);
        $seuil->puits()->update(['seuil_volume' => $data['valeur_seuil'], 'seuil_pression' => $data['valeur_pression'] ?? $seuil->puits->seuil_pression]);
        return response()->json($seuil->fresh()->load('puits'));
    }

    public function destroy(SeuilAlerte $seuil): JsonResponse
    {
        $seuil->delete();
        return response()->json(['message' => 'Seuil supprimé.']);
    }
}
