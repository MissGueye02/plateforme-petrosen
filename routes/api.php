<?php

use App\Http\Controllers\Api\AlerteController;
use App\Http\Controllers\Api\AuditeurRapportController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlocPetrolierController;
use App\Http\Controllers\Api\ChefProjetController;
use App\Http\Controllers\Api\EngineerReportController;
use App\Http\Controllers\Api\GisementController;
use App\Http\Controllers\Api\InterventionController;
use App\Http\Controllers\Api\MissionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProductionController;
use App\Http\Controllers\Api\ProductionSuiviController;
use App\Http\Controllers\Api\RapportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\SeuilAlerteController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\TableauBordController;
use App\Http\Controllers\Api\UtilisateurController;
use App\Http\Controllers\Api\PartenaireProjetController;
use App\Http\Controllers\Api\PartenaireRapportController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Notifications : tous les utilisateurs authentifiés.
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/{notification}/lu', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/mark-all-lu', [NotificationController::class, 'markAllAsRead']);

    // Tableau de bord : Admin, Ingénieur terrain et Chef de projet.
    Route::middleware('exclude_role:partenaire,auditeur_itie')
        ->get('/tableau-bord', [TableauBordController::class, 'index']);

    // Administration.
    Route::middleware('role:administrateur')->group(function () {
        Route::get('/utilisateurs', [UtilisateurController::class, 'index']);
        Route::post('/utilisateurs', [UtilisateurController::class, 'store']);
        Route::get('/utilisateurs/{utilisateur}', [UtilisateurController::class, 'show']);
        Route::put('/utilisateurs/{utilisateur}', [UtilisateurController::class, 'update']);
        Route::delete('/utilisateurs/{utilisateur}', [UtilisateurController::class, 'destroy']);
        Route::get('/roles', [UtilisateurController::class, 'roles']);

        Route::get('/roles/detail', [RoleController::class, 'index']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::get('/roles/{role}', [RoleController::class, 'show']);
        Route::put('/roles/{role}', [RoleController::class, 'update']);
        Route::delete('/roles/{role}', [RoleController::class, 'destroy']);

        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::post('/permissions', [PermissionController::class, 'store']);
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy']);

        Route::get('/roles/{role}/permissions', [RolePermissionController::class, 'index']);
        Route::post('/roles/{role}/permissions', [RolePermissionController::class, 'attach']);
        Route::delete('/roles/{role}/permissions', [RolePermissionController::class, 'detach']);

        Route::get('/seuils', [SeuilAlerteController::class, 'index']);
        Route::get('/seuils/options', [SeuilAlerteController::class, 'options']);
        Route::post('/seuils', [SeuilAlerteController::class, 'store']);
        Route::get('/seuils/{seuil}', [SeuilAlerteController::class, 'show']);
        Route::put('/seuils/{seuil}', [SeuilAlerteController::class, 'update']);
        Route::delete('/seuils/{seuil}', [SeuilAlerteController::class, 'destroy']);

        Route::get('/stats/global', [StatsController::class, 'global']);

        Route::get('/rapports', [RapportController::class, 'index']);
        Route::get('/rapports/{rapport}', [RapportController::class, 'show']);
        Route::post('/rapports', [RapportController::class, 'generer']);
    });

    Route::put('/utilisateurs/me', [UtilisateurController::class, 'updateCurrentUser']);

    // Gestion des blocs, gisements et puits : Chef de projet uniquement.
    Route::middleware('role:chef_projet')->group(function () {
        Route::get('/blocs', [BlocPetrolierController::class, 'index']);
        Route::post('/blocs', [BlocPetrolierController::class, 'store']);
        Route::get('/blocs/{bloc}', [BlocPetrolierController::class, 'show']);
        Route::put('/blocs/{bloc}', [BlocPetrolierController::class, 'update']);
        Route::delete('/blocs/{bloc}', [BlocPetrolierController::class, 'destroy']);
        Route::get('/blocs/{bloc}/puits', [BlocPetrolierController::class, 'puits']);
        Route::post('/blocs/{bloc}/puits', [BlocPetrolierController::class, 'storePuits']);

        Route::get('/gisements', [GisementController::class, 'index']);
        Route::post('/gisements', [GisementController::class, 'store']);
        Route::get('/gisements/{gisement}', [GisementController::class, 'show']);
        Route::put('/gisements/{gisement}', [GisementController::class, 'update']);
        Route::delete('/gisements/{gisement}', [GisementController::class, 'destroy']);
    });

    // Chef de projet : contrôle des rapports et génération/publication des projets.
    Route::middleware('role:chef_projet')->group(function () {
        Route::get('/chef/productions', [ProductionSuiviController::class, 'index']);
        Route::get('/chef/productions/stats', [ProductionSuiviController::class, 'stats']);
        Route::post('/chef/productions/rapport', [ProductionSuiviController::class, 'genererRapport']);

        Route::get('/chef/partenaires', [ChefProjetController::class, 'partenaires']);
        Route::get('/chef/ingenieurs', [ChefProjetController::class, 'ingenieurs']);
        Route::get('/chef/rapports', [ChefProjetController::class, 'rapports']);
        Route::patch('/chef/rapports/{rapport}/valider', [ChefProjetController::class, 'validerRapport']);
        Route::patch('/chef/rapports/{rapport}/demander-correction', [ChefProjetController::class, 'rejeterRapport']);
        Route::post('/chef/rapports/{rapport}/projet', [ChefProjetController::class, 'creerProjet']);
        Route::patch('/chef/projets/{projet}/publier', [ChefProjetController::class, 'publierProjet']);

        Route::post('/alertes/{alerte}/planifier', [InterventionController::class, 'planifier']);
        Route::patch('/interventions/{intervention}/valider', [InterventionController::class, 'valider']);
    });

    // Ingénieur de terrain : seul rôle autorisé à saisir la production.
    Route::middleware('role:ingenieur_terrain')->group(function () {
        Route::get('/puits', function () {
            return response()->json(\App\Models\Puits::with('blocPetrolier')->where('statut', 'actif')->get());
        });

        Route::post('/productions', [ProductionController::class, 'store']);
        Route::get('/productions', function () {
            return response()->json(
                \App\Models\Production::with('puits')->where('user_id', request()->user()->id)->latest()->paginate(20)
            );
        });

        Route::get('/mes-missions', [MissionController::class, 'index']);
        Route::patch('/missions/{mission}/accepter', [MissionController::class, 'accepter']);
        Route::patch('/missions/{mission}/refuser', [MissionController::class, 'refuser']);

        Route::get('/ingenieur/rapports', [EngineerReportController::class, 'index']);
        Route::post('/ingenieur/rapports', [EngineerReportController::class, 'store']);

        Route::post('/alertes', [AlerteController::class, 'store']);

        Route::get('/consult/blocs', [BlocPetrolierController::class, 'index']);
        Route::get('/consult/blocs/{bloc}', [BlocPetrolierController::class, 'show']);
        Route::get('/consult/gisements', [GisementController::class, 'index']);
        Route::get('/consult/gisements/{gisement}', [GisementController::class, 'show']);
    });

    // Alertes opérationnelles.
    Route::middleware('role:administrateur,chef_projet,ingenieur_terrain')->group(function () {
        Route::get('/alertes', [AlerteController::class, 'index']);
        Route::get('/alertes/actives', [AlerteController::class, 'actives']);
        Route::get('/alertes/stats', [AlerteController::class, 'stats']);
        Route::patch('/alertes/{alerte}/traiter', [AlerteController::class, 'traiter']);
        Route::patch('/alertes/{alerte}/ignorer', [AlerteController::class, 'ignorer']);
    });

    // Partenaire : consultation uniquement des projets publiés.
    Route::middleware('role:partenaire')->group(function () {
        Route::get('/partenaire/projets', [PartenaireProjetController::class, 'index']);
        Route::get('/partenaire/projets/{projet}', [PartenaireProjetController::class, 'show']);
        Route::get('/partenaire/rapports', [PartenaireRapportController::class, 'index']);
        Route::get('/partenaire/rapports/{rapport}', [PartenaireRapportController::class, 'show']);
        Route::post('/partenaire/rapports/{rapport}/comment', [PartenaireRapportController::class, 'comment']);
    });

    // Auditeur ITIE : consultation/export des rapports.
    Route::middleware('role:auditeur_itie')->group(function () {
        Route::get('/auditeur/rapports', [AuditeurRapportController::class, 'index']);
        Route::get('/auditeur/rapports/{rapport}', [AuditeurRapportController::class, 'show']);
        Route::post('/auditeur/rapports/{rapport}/comment', [AuditeurRapportController::class, 'comment']);
        Route::get('/auditeur/rapports/{rapport}/export', [AuditeurRapportController::class, 'export']);
    });
});
