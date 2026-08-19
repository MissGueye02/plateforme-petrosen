<?php

namespace Database\Seeders;

use App\Models\BlocPetrolier;
use App\Models\Permission;
use App\Models\Puits;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roleDefinitions = [
            'administrateur' => 'Administrateur',
            'ingenieur_terrain' => 'Ingénieur de terrain',
            'chef_projet' => 'Chef de projet',
            'partenaire' => 'Partenaire',
            'auditeur_itie' => 'Auditeur ITIE',
        ];

        $roles = [];
        foreach ($roleDefinitions as $code => $libelle) {
            $roles[$code] = Role::updateOrCreate(
                ['code' => $code],
                ['libelle' => $libelle]
            );
        }

        $permissions = [
            'manage-users' => 'Gérer les utilisateurs',
            'manage-roles' => 'Gérer les rôles et droits',
            'configure-alerts' => 'Configurer les seuils d’alerte',
            'view-dashboard' => 'Voir le tableau de bord',
            'view-stats' => 'Voir les statistiques',
            'generate-reports' => 'Générer des rapports',
            'manage-blocs' => 'Gérer les blocs pétroliers',
            'manage-puits' => 'Gérer les puits',
            'enter-production' => 'Saisir la production',
            'provide-technical-report' => 'Fournir un rapport technique',
            'view-projects' => 'Consulter les projets',
            'comment-reports' => 'Commenter les rapports',
            'review-reports' => 'Vérifier les rapports',
            'publish-projects' => 'Publier les projets',
        ];

        foreach ($permissions as $name => $label) {
            Permission::updateOrCreate(['name' => $name], ['label' => $label]);
        }

        $permissionsByRole = [
            'administrateur' => array_keys($permissions),
            'chef_projet' => [
                'view-dashboard', 'view-stats', 'generate-reports',
                'manage-blocs', 'manage-puits', 'configure-alerts',
                'review-reports', 'publish-projects',
            ],
            'ingenieur_terrain' => [
                'enter-production', 'provide-technical-report', 'view-dashboard',
            ],
            'partenaire' => ['view-projects'],
            'auditeur_itie' => ['view-projects', 'comment-reports'],
        ];

        foreach ($permissionsByRole as $code => $names) {
            $roles[$code]->permissions()->sync(
                Permission::whereIn('name', $names)->pluck('id')->all()
            );
        }

        // Données de démonstration uniquement : firstOrCreate ne remplace
        // jamais un utilisateur déjà présent.
        $bloc1 = BlocPetrolier::firstOrCreate(['nom' => 'Bloc Sangomar'], [
            'statut' => 'actif', 'superficie' => 7490.00, 'localisation' => 'Offshore, Fatick',
        ]);
        $bloc2 = BlocPetrolier::firstOrCreate(['nom' => 'Bloc Grand-Cote Offshore'], [
            'statut' => 'en_exploration', 'superficie' => 4200.00, 'localisation' => 'Offshore, Saint-Louis',
        ]);

        Puits::firstOrCreate(['nom' => 'Puits FAN-1', 'bloc_petrolier_id' => $bloc1->id], [
            'pression' => 320.5, 'profondeur' => 4500.0, 'seuil_volume' => 800.0, 'seuil_pression' => 450.0, 'statut' => 'actif',
        ]);
        Puits::firstOrCreate(['nom' => 'Puits SNE-1', 'bloc_petrolier_id' => $bloc1->id], [
            'pression' => 280.0, 'profondeur' => 4100.0, 'seuil_volume' => 900.0, 'seuil_pression' => 400.0, 'statut' => 'actif',
        ]);
        Puits::firstOrCreate(['nom' => 'Puits GCO-A1', 'bloc_petrolier_id' => $bloc2->id], [
            'pression' => 190.0, 'profondeur' => 3200.0, 'seuil_volume' => 600.0, 'seuil_pression' => 300.0, 'statut' => 'actif',
        ]);
        Puits::firstOrCreate(['nom' => 'Puits GCO-B2', 'bloc_petrolier_id' => $bloc2->id], [
            'pression' => 150.0, 'profondeur' => 2800.0, 'seuil_volume' => 500.0, 'seuil_pression' => 280.0, 'statut' => 'actif',
        ]);
    }
}
