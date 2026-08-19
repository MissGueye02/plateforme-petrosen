<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlerteController extends Controller
{
    /** GET /api/alertes */
    public function index(Request $request): JsonResponse
    {
        $query = Alerte::with(['puits.blocPetrolier'])->latest();

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('puits_id')) {
            $query->where('puits_id', $request->puits_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $alertes = $query->paginate(20);
        return response()->json($alertes);
    }

    /** GET /api/alertes/actives */
    public function actives(): JsonResponse
    {
        $alertes = Alerte::with(['puits'])->where('statut', 'nouvelle')->latest()->get();
        return response()->json($alertes);
    }

    /** POST /api/alertes - signaler une alerte (ingénieur) */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'puits_id' => 'nullable|exists:puits,id',
            'production_id' => 'nullable|exists:productions,id',
            'type' => 'nullable|string',
            'message' => 'required|string',
        ]);

        $alerte = Alerte::create([
            'puits_id' => $data['puits_id'] ?? null,
            'production_id' => $data['production_id'] ?? null,
            'type' => $data['type'] ?? 'manuel',
            'message' => $data['message'],
            'statut' => 'nouvelle',
        ]);

        // Notify all chefs de projet
        $chefs = \App\Models\Role::where('code', 'chef_projet')->first()?->users()->get() ?? \App\Models\Role::where('libelle', 'chef de projet')->first()?->users()->get() ?? collect();
        foreach ($chefs as $chef) {
            \App\Models\Notification::create([
                'utilisateur_id' => $chef->id,
                'message' => 'Nouvelle alerte signalée #' . $alerte->id . ': ' . substr($alerte->message, 0, 200),
                'lu' => false,
                'type' => 'alerte_nouvelle',
            ]);
        }

        return response()->json($alerte, 201);
    }

    /** PATCH /api/alertes/{alerte}/traiter */
    public function traiter(Alerte $alerte): JsonResponse
    {
        $alerte->update(['statut' => 'traitee']);
        return response()->json(['message' => 'Alerte marquee comme traitee.', 'alerte' => $alerte]);
    }

    /** PATCH /api/alertes/{alerte}/ignorer */
    public function ignorer(Alerte $alerte): JsonResponse
    {
        $alerte->update(['statut' => 'ignoree']);
        return response()->json(['message' => 'Alerte ignoree.', 'alerte' => $alerte]);
    }

    /** GET /api/alertes/stats */
    public function stats(): JsonResponse
    {
        return response()->json([
            'nouvelle' => Alerte::where('statut', 'nouvelle')->count(),
            'traitee'  => Alerte::where('statut', 'traitee')->count(),
            'ignoree'  => Alerte::where('statut', 'ignoree')->count(),
            'total'    => Alerte::count(),
        ]);
    }
}
