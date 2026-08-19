<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Connexion : vérifie les identifiants et retourne un token
     * accompagné du rôle de l'utilisateur.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifiant' => 'required|string',
            'password'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Identifiants invalides.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // La table "utilisateurs" utilise "mail" comme identifiant de connexion.
        $user = User::with('role')
            ->where('mail', $request->identifiant)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->motdepasse)) {
            return response()->json([
                'message' => 'Identifiant ou mot de passe incorrect.',
            ], 401);
        }

        // Révoque les anciens tokens pour éviter l'accumulation
        $user->tokens()->delete();

        $token = $user->createToken('petrosen-auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'token'   => $token,
            'user'    => [
                'id'   => $user->id,
                'nom'  => $user->nom,
                'prenom' => $user->prenom,
                'mail' => $user->mail,
                'role_id' => $user->role_id,
                'role' => $user->roleCode(),
                'role_label' => $user->role?->libelle,
            ],
        ], 200);
    }

    /**
     * Déconnexion : révoque le token courant.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ], 200);
    }

    /**
     * Retourne l'utilisateur actuellement authentifié.
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('role');

        return response()->json([
            'id'   => $user->id,
            'nom'  => $user->nom,
            'mail' => $user->mail,
            'role' => $user->roleCode(),
            'role_label' => $user->role?->libelle,
        ]);
    }
}
