<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlocPetrolier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlocPetrolierController extends Controller
{
    /** GET /api/blocs */
    public function index(): JsonResponse
    {
        $blocs = BlocPetrolier::withCount('puits')->get();
        return response()->json($blocs);
    }

    /** POST /api/blocs */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom'         => 'required|string|max:255',
            'statut'      => 'in:actif,inactif,en_exploration',
            'superficie'  => 'nullable|numeric',
            'localisation'=> 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $bloc = BlocPetrolier::create($data);
        return response()->json($bloc, 201);
    }

    /** GET /api/blocs/{bloc} */
    public function show(BlocPetrolier $bloc): JsonResponse
    {
        return response()->json($bloc->load('puits'));
    }

    /** PUT /api/blocs/{bloc} */
    public function update(Request $request, BlocPetrolier $bloc): JsonResponse
    {
        $data = $request->validate([
            'nom'         => 'sometimes|string|max:255',
            'statut'      => 'sometimes|in:actif,inactif,en_exploration',
            'superficie'  => 'nullable|numeric',
            'localisation'=> 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $bloc->update($data);
        return response()->json($bloc);
    }

    /** DELETE /api/blocs/{bloc} */
    public function destroy(BlocPetrolier $bloc): JsonResponse
    {
        $bloc->delete();
        return response()->json(['message' => 'Bloc supprime.']);
    }

    /** GET /api/blocs/{bloc}/puits */
    public function puits(BlocPetrolier $bloc): JsonResponse
    {
        return response()->json($bloc->puits);
    }

    /** POST /api/blocs/{bloc}/puits */
    public function storePuits(Request $request, BlocPetrolier $bloc): JsonResponse
    {
        $data = $request->validate([
            'nom'            => 'required|string|max:255',
            'pression'       => 'nullable|numeric',
            'profondeur'     => 'nullable|numeric',
            'seuil_volume'   => 'nullable|numeric',
            'seuil_pression' => 'nullable|numeric',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'statut'         => 'in:actif,inactif,ferme',
        ]);

        $puits = $bloc->puits()->create($data);
        return response()->json($puits, 201);
    }
}
