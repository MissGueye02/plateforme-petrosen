<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gisement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GisementController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Gisement::with('blocPetrolier')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'localisation' => 'nullable|string|max:255',
            'statut_licence' => 'sometimes|string|max:255',
            'bloc_petrolier_id' => 'required|exists:blocs_petroliers,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $gisement = Gisement::create($data);
        return response()->json($gisement->load('blocPetrolier'), 201);
    }

    public function show(Gisement $gisement): JsonResponse
    {
        return response()->json($gisement->load('blocPetrolier'));
    }

    public function update(Request $request, Gisement $gisement): JsonResponse
    {
        $data = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'localisation' => 'nullable|string|max:255',
            'statut_licence' => 'sometimes|string|max:255',
            'bloc_petrolier_id' => 'sometimes|exists:blocs_petroliers,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $gisement->update($data);
        return response()->json($gisement->fresh()->load('blocPetrolier'));
    }

    public function destroy(Gisement $gisement): JsonResponse
    {
        $gisement->delete();
        return response()->json(['message' => 'Gisement supprimé']);
    }
}
