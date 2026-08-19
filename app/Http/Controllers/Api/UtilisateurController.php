<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{
    /** GET /api/utilisateurs */
    public function index(Request $request): JsonResponse
    {
        $users = User::with(['role', 'createur:id,nom,prenom,mail'])
            ->where('created_by', $request->user()->id)
            ->orWhere('id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($users);
    }

    /** POST /api/utilisateurs */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'mail' => 'required|email|unique:utilisateurs,mail',
            'motdepasse' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        $data['motdepasse'] = Hash::make($data['motdepasse']);
        $data['created_by'] = $request->user()->id;

        $user = User::create($data);

        return response()->json($user->load('role'), 201);
    }

    /** GET /api/utilisateurs/{utilisateur} */
    public function show(Request $request, User $utilisateur): JsonResponse
    {
        $this->ensureManagedByCurrentAdmin($request, $utilisateur);
        return response()->json($utilisateur->load('role'));
    }

    /** PUT /api/utilisateurs/me */
    public function updateCurrentUser(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'mail' => 'sometimes|email|unique:utilisateurs,mail,' . $user->id,
            'motdepasse' => 'sometimes|string|min:8',
        ]);

        if (isset($data['motdepasse'])) {
            $data['motdepasse'] = Hash::make($data['motdepasse']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => $user->fresh()->load('role'),
        ]);
    }

    /**
     * PUT /api/utilisateurs/{utilisateur}
     * L'administrateur ne modifie que le rôle d'un utilisateur qu'il a créé.
     */
    public function update(Request $request, User $utilisateur): JsonResponse
    {
        $this->ensureManagedByCurrentAdmin($request, $utilisateur);

        $data = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $utilisateur->update(['role_id' => $data['role_id']]);

        return response()->json($utilisateur->fresh()->load('role'));
    }

    /** DELETE /api/utilisateurs/{utilisateur} */
    public function destroy(Request $request, User $utilisateur): JsonResponse
    {
        $this->ensureManagedByCurrentAdmin($request, $utilisateur);

        if ($utilisateur->id === $request->user()->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 403);
        }

        $utilisateur->delete();

        return response()->json(['message' => 'Utilisateur désactivé avec succès.']);
    }

    /** GET /api/roles */
    public function roles(): JsonResponse
    {
        return response()->json(Role::orderBy('libelle')->get());
    }

    private function ensureManagedByCurrentAdmin(Request $request, User $utilisateur): void
    {
        if ($utilisateur->created_by !== $request->user()->id) {
            abort(response()->json([
                'message' => "Vous ne pouvez gérer que les utilisateurs que vous avez créés.",
            ], 403));
        }
    }
}
