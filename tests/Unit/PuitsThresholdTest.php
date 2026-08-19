<?php

namespace Tests\Unit;

use App\Models\BlocPetrolier;
use App\Models\Puits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PuitsThresholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_detects_exceeding_thresholds(): void
    {
        $bloc = BlocPetrolier::create([
            'nom' => 'Bloc Test',
            'statut' => 'actif',
            'superficie' => 1000,
            'localisation' => 'Dakar',
        ]);

        $puits = Puits::create([
            'nom' => 'Puits T-01',
            'bloc_petrolier_id' => $bloc->id,
            'pression' => 320,
            'profondeur' => 4200,
            'seuil_volume' => 800,
            'seuil_pression' => 450,
            'statut' => 'actif',
        ]);

        $this->assertTrue($puits->depasseSeuil(850, 300));
        $this->assertTrue($puits->depasseSeuil(700, 500));
        $this->assertFalse($puits->depasseSeuil(700, 300));
    }
}
