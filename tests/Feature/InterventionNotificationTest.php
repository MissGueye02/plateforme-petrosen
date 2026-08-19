<?php

namespace Tests\Feature;

use App\Models\Alerte;
use App\Models\BlocPetrolier;
use App\Models\Intervention;
use App\Models\Role;
use App\Models\User;
use App\Models\Puits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InterventionNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_chef_validation_creates_notification_for_engineer(): void
    {
        $chefRole = Role::create(['code' => 'chef_projet', 'libelle' => 'Chef de projet']);
        $engineerRole = Role::create(['code' => 'ingenieur_terrain', 'libelle' => 'Ingénieur de terrain']);
        $chef = User::create(['nom' => 'Chef', 'mail' => 'chef@test.local', 'motdepasse' => Hash::make('secret123'), 'role_id' => $chefRole->id]);
        $engineer = User::create(['nom' => 'Ing', 'mail' => 'ing2@test.local', 'motdepasse' => Hash::make('secret123'), 'role_id' => $engineerRole->id]);
        $bloc = BlocPetrolier::create(['nom' => 'Bloc Test', 'statut' => 'actif']);
        $puits = Puits::create(['nom' => 'Puits Test', 'bloc_petrolier_id' => $bloc->id, 'statut' => 'actif']);
        $alerte = Alerte::create(['puits_id' => $puits->id, 'type' => 'test', 'message' => 'Test', 'statut' => 'nouvelle']);
        $intervention = Intervention::create(['description' => 'Intervention test', 'date_planifiee' => now(), 'statut' => 'planifiee', 'alerte_id' => $alerte->id, 'ingenieur_affecte_id' => $engineer->id]);

        $this->actingAs($chef, 'sanctum')->patchJson('/api/interventions/' . $intervention->id . '/valider')->assertOk();
        $this->assertDatabaseHas('notifications', ['utilisateur_id' => $engineer->id, 'type' => 'intervention_validee', 'lu' => false]);
    }
}
