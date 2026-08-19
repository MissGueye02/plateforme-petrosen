<?php

namespace Tests\Feature;

use App\Models\BlocPetrolier;
use App\Models\Intervention;
use App\Models\Puits;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    use RefreshDatabase;

    private function role(string $code, string $label): Role
    {
        return Role::firstOrCreate(['code' => $code], ['libelle' => $label]);
    }

    public function test_partenaire_cannot_access_dashboard(): void
    {
        $role = $this->role('partenaire', 'Partenaire');
        $user = User::create([
            'nom' => 'Partenaire', 'prenom' => 'Test', 'mail' => 'partner@example.test',
            'motdepasse' => Hash::make('secret123'), 'role_id' => $role->id,
        ]);

        $this->actingAs($user, 'sanctum')->getJson('/api/tableau-bord')->assertStatus(403);
    }

    public function test_auditeur_itie_cannot_access_dashboard(): void
    {
        $role = $this->role('auditeur_itie', 'Auditeur ITIE');
        $user = User::create([
            'nom' => 'Auditeur', 'prenom' => 'Test', 'mail' => 'auditeur@example.test',
            'motdepasse' => Hash::make('secret123'), 'role_id' => $role->id,
        ]);

        $this->actingAs($user, 'sanctum')->getJson('/api/tableau-bord')->assertStatus(403);
    }

    public function test_only_engineer_can_create_production(): void
    {
        $bloc = BlocPetrolier::create(['nom' => 'Bloc Test', 'statut' => 'actif']);
        $puits = Puits::create([
            'nom' => 'Puits T-01', 'bloc_petrolier_id' => $bloc->id,
            'seuil_volume' => 800, 'seuil_pression' => 450, 'statut' => 'actif',
        ]);

        $engineerRole = $this->role('ingenieur_terrain', 'Ingénieur de terrain');
        $partnerRole = $this->role('partenaire', 'Partenaire');

        $engineer = User::create([
            'nom' => 'Ing', 'mail' => 'ing@example.test', 'motdepasse' => Hash::make('secret123'), 'role_id' => $engineerRole->id,
        ]);
        $partner = User::create([
            'nom' => 'Part', 'mail' => 'part@example.test', 'motdepasse' => Hash::make('secret123'), 'role_id' => $partnerRole->id,
        ]);

        $payload = ['puits_id' => $puits->id, 'date_production' => now()->toDateString(), 'volume' => 100, 'pression' => 100];
        $this->actingAs($partner, 'sanctum')->postJson('/api/productions', $payload)->assertStatus(403);
        $this->actingAs($engineer, 'sanctum')->postJson('/api/productions', $payload)->assertCreated();
    }

    public function test_admin_can_change_role_only_for_user_created_by_admin(): void
    {
        $adminRole = $this->role('administrateur', 'Administrateur');
        $engineerRole = $this->role('ingenieur_terrain', 'Ingénieur de terrain');
        $chefRole = $this->role('chef_projet', 'Chef de projet');

        $adminA = User::create(['nom' => 'AdminA', 'mail' => 'admina@test.local', 'motdepasse' => Hash::make('secret123'), 'role_id' => $adminRole->id]);
        $adminB = User::create(['nom' => 'AdminB', 'mail' => 'adminb@test.local', 'motdepasse' => Hash::make('secret123'), 'role_id' => $adminRole->id]);
        $owned = User::create(['nom' => 'Owned', 'mail' => 'owned@test.local', 'motdepasse' => Hash::make('secret123'), 'role_id' => $engineerRole->id, 'created_by' => $adminA->id]);
        $foreign = User::create(['nom' => 'Foreign', 'mail' => 'foreign@test.local', 'motdepasse' => Hash::make('secret123'), 'role_id' => $engineerRole->id, 'created_by' => $adminB->id]);

        $this->actingAs($adminA, 'sanctum')->putJson('/api/utilisateurs/' . $owned->id, ['role_id' => $chefRole->id])->assertOk();
        $this->actingAs($adminA, 'sanctum')->putJson('/api/utilisateurs/' . $foreign->id, ['role_id' => $chefRole->id])->assertForbidden();
    }

    public function test_admin_created_user_is_marked_with_creator(): void
    {
        $adminRole = $this->role('administrateur', 'Administrateur');
        $engineerRole = $this->role('ingenieur_terrain', 'Ingénieur de terrain');
        $admin = User::create(['nom' => 'Admin', 'mail' => 'creator@test.local', 'motdepasse' => Hash::make('secret123'), 'role_id' => $adminRole->id]);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/utilisateurs', [
            'nom' => 'Nouvel', 'prenom' => 'Utilisateur', 'mail' => 'new@test.local',
            'motdepasse' => 'secret123', 'role_id' => $engineerRole->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('utilisateurs', ['mail' => 'new@test.local', 'created_by' => $admin->id]);
    }
}
