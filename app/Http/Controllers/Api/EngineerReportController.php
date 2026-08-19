<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rapport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EngineerReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rapports = Rapport::with(['mission', 'production'])
            ->where('user_id', $request->user()->id)
            ->where('type', 'technique')
            ->latest()
            ->get();

        return response()->json($rapports);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string',
            'mission_id' => 'nullable|exists:missions,id',
            'production_id' => 'nullable|exists:productions,id',
            'periode_debut' => 'nullable|date',
            'periode_fin' => 'nullable|date|after_or_equal:periode_debut',
        ]);

        $rapport = Rapport::create([
            'titre' => $data['titre'],
            'type' => 'technique',
            'contenu' => ['texte' => $data['contenu']],
            'periode_debut' => $data['periode_debut'] ?? null,
            'periode_fin' => $data['periode_fin'] ?? null,
            'user_id' => $request->user()->id,
            'mission_id' => $data['mission_id'] ?? null,
            'production_id' => $data['production_id'] ?? null,
            'statut' => 'soumis',
        ]);

        return response()->json($rapport->load(['mission', 'production']), 201);
    }
}
