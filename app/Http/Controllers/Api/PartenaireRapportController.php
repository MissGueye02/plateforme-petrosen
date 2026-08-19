<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rapport;
use App\Models\Commentaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartenaireRapportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rapports = Rapport::with(['projet','commentaires.utilisateur'])
            ->where('statut', 'valide')
            ->whereHas('projet', function ($query) use ($request) {
                $query->where('partenaire_id', $request->user()->id)
                    ->where('statut', 'valide');
            })
            ->latest()
            ->paginate(20);

        return response()->json($rapports);
    }

    public function show(Request $request, Rapport $rapport): JsonResponse
    {
        $accessible = $rapport->statut === 'valide'
            && $rapport->projet()
                ->where('partenaire_id', $request->user()->id)
                ->where('statut', 'valide')
                ->exists();

        if (! $accessible) {
            return response()->json(['message' => 'Rapport non accessible.'], 403);
        }

        return response()->json($rapport->load('generePar', 'commentaires', 'projet'));
    }

    public function comment(Request $request, Rapport $rapport): JsonResponse
    {
        $accessible = $rapport->statut === 'valide'
            && $rapport->projet()->where('partenaire_id', $request->user()->id)->where('statut', 'valide')->exists();
        if (! $accessible) {
            return response()->json(['message' => 'Rapport non accessible.'], 403);
        }
        $data = $request->validate(['contenu' => 'required|string|max:5000']);
        $comment = Commentaire::create([
            'contenu' => $data['contenu'],
            'commentable_type' => Rapport::class,
            'commentable_id' => $rapport->id,
            'utilisateur_id' => $request->user()->id,
        ]);
        $chefs = \App\Models\Role::where('code', 'chef_projet')->first()?->users()->get() ?? collect();
        foreach ($chefs as $chef) {
            \App\Models\Notification::create([
                'utilisateur_id' => $chef->id,
                'message' => 'Nouveau commentaire partenaire sur le rapport #' . $rapport->id . '.',
                'lu' => false,
                'type' => 'commentaire_partenaire',
            ]);
        }
        return response()->json($comment->load('utilisateur'), 201);
    }

}
