<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductionRequest;
use App\Services\ProductionService;
use Illuminate\Http\JsonResponse;

class ProductionController extends Controller
{
    public function __construct(protected ProductionService $productionService)
    {
    }

    /**
     * POST /api/productions
     * Point d'entrée de la saisie de production par l'ingénieur terrain.
     */
    public function store(StoreProductionRequest $request): JsonResponse
    {
        $resultat = $this->productionService->enregistrerSaisie(
            $request->validated(),
            $request->user()
        );

        $production = $resultat['production'];
        $alerte = $resultat['alerte'];

        return response()->json([
            'success' => true,
            'message' => 'Saisie enregistrée avec succès.',
            'niveau' => $production->niveau, // 'normal' | 'anormal'
            'production' => $production,
            'alerte' => $alerte, // null si niveau normal
        ], 201);
    }
}
