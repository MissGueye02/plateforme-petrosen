<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commentaire;
use App\Models\Rapport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditeurRapportController extends Controller
{
    // GET /api/auditeur/rapports
    public function index(Request $request): JsonResponse
    {
        $query = Rapport::with('generePar');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $rapports = $query->latest()->paginate(20);
        return response()->json($rapports);
    }

    // GET /api/auditeur/rapports/{rapport}
    public function show(Rapport $rapport): JsonResponse
    {
        return response()->json($rapport->load('generePar','commentaires'));
    }

    // POST /api/auditeur/rapports/{rapport}/comment
    public function comment(Request $request, Rapport $rapport): JsonResponse
    {
        $data = $request->validate([
            'contenu' => 'required|string',
        ]);

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
                'message' => 'Nouveau commentaire auditeur ITIE sur le rapport #' . $rapport->id . '.',
                'lu' => false,
                'type' => 'commentaire_auditeur',
            ]);
        }
        return response()->json($comment->load('utilisateur'), 201);
    }

    // GET /api/auditeur/rapports/{rapport}/export?format=json|csv
    public function export(Request $request, Rapport $rapport)
    {
        $format = strtolower($request->input('format', 'json'));

        $rapport->load('generePar');
        $data = [
            'id' => $rapport->id,
            'titre' => $rapport->titre,
            'type' => $rapport->type,
            'periode_debut' => $rapport->periode_debut?->toDateString(),
            'periode_fin' => $rapport->periode_fin?->toDateString(),
            'genere_par' => $rapport->generePar?->mail ?? null,
            'statut' => $rapport->statut,
            'contenu' => $rapport->contenu,
            'created_at' => $rapport->created_at->toDateTimeString(),
        ];

        if ($format === 'json') {
            return response()->json($data);
        }

        if ($format === 'csv') {
            // Build a simple CSV by flattening sections
            $rows = [];
            $rows[] = ['id', $data['id']];
            $rows[] = ['titre', $data['titre']];
            $rows[] = ['type', $data['type']];
            $rows[] = ['periode_debut', $data['periode_debut']];
            $rows[] = ['periode_fin', $data['periode_fin']];
            $rows[] = ['genere_par', $data['genere_par']];
            $rows[] = ['statut', $data['statut']];
            $rows[] = ['created_at', $data['created_at']];

            $rows[] = [];
            $rows[] = ['---contenu---'];

            // If contenu is array/associative, try to serialize sections
            if (is_array($data['contenu'])) {
                foreach ($data['contenu'] as $section => $value) {
                    if (is_array($value)) {
                        $rows[] = ["section", $section];
                        // if sequential array of items
                        foreach ($value as $item) {
                            if (is_array($item)) {
                                // join item fields
                                $rows[] = array_map(function ($v) { return is_scalar($v) ? $v : json_encode($v); }, $item);
                            } else {
                                $rows[] = [$item];
                            }
                        }
                    } else {
                        $rows[] = [$section, is_scalar($value) ? $value : json_encode($value)];
                    }
                }
            } else {
                $rows[] = ['contenu', is_scalar($data['contenu']) ? $data['contenu'] : json_encode($data['contenu'])];
            }

            $filename = 'rapport_' . $rapport->id . '.csv';

            $callback = function() use ($rows) {
                $fh = fopen('php://output', 'w');
                foreach ($rows as $row) {
                    fputcsv($fh, $row);
                }
                fclose($fh);
            };

            return response()->streamDownload($callback, $filename, [
                'Content-Type' => 'text/csv',
            ]);
        }

        // PDF export not implemented server-side. Return structured HTML as fallback.
        if ($format === 'pdf') {
            $html = '<h1>' . e($rapport->titre) . '</h1>';
            $html .= '<p>Type: ' . e($rapport->type) . '</p>';
            $html .= '<p>Periode: ' . e($rapport->periode_debut?->toDateString()) . ' - ' . e($rapport->periode_fin?->toDateString()) . '</p>';
            $html .= '<pre>' . e(json_encode($rapport->contenu, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';

            $filename = 'rapport_' . $rapport->id . '.html';
            $callback = function() use ($html) {
                echo $html;
            };
            return response()->streamDownload($callback, $filename, [
                'Content-Type' => 'text/html',
            ]);
        }

        return response()->json(['message' => 'Format non supporte.'], 400);
    }
}
