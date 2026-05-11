<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentoController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Documento::with(['cliente', 'template'])
                ->where('user_id', $request->user()->id)
                ->latest()
                ->get()
        );
    }

    public function show(Request $request, $id)
    {
        $documento = Documento::with(['cliente', 'template'])
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$documento) {
            return response()->json(['message' => 'Documento nao encontrado'], 404);
        }

        return response()->json($documento);
    }

    public function store(Request $request)
    {
        $userId = $request->user()->id;
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'conteudo_final' => 'required|string',
            'cliente_id' => [
                'required',
                Rule::exists('clientes', 'id')->where('user_id', $userId),
            ],
            'template_id' => [
                'required',
                Rule::exists('templates', 'id')->where('user_id', $userId),
            ],
            'status' => 'nullable|string|max:255',
        ]);

        $documento = Documento::create([
            ...$data,
            'user_id' => $userId,
            'status' => $data['status'] ?? 'rascunho',
        ]);

        return response()->json($documento->load(['cliente', 'template']), 201);
    }

    public function update(Request $request, $id)
    {
        $userId = $request->user()->id;
        $documento = Documento::where('user_id', $userId)->find($id);

        if (!$documento) {
            return response()->json(['message' => 'Documento nao encontrado'], 404);
        }

        $data = $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'conteudo_final' => 'sometimes|required|string',
            'cliente_id' => [
                'sometimes',
                'required',
                Rule::exists('clientes', 'id')->where('user_id', $userId),
            ],
            'template_id' => [
                'sometimes',
                'required',
                Rule::exists('templates', 'id')->where('user_id', $userId),
            ],
            'status' => 'nullable|string|max:255',
        ]);

        $documento->update($data);

        return response()->json($documento->load(['cliente', 'template']));
    }

    public function destroy(Request $request, $id)
    {
        $documento = Documento::where('user_id', $request->user()->id)->find($id);

        if (!$documento) {
            return response()->json(['message' => 'Documento nao encontrado'], 404);
        }

        $documento->delete();

        return response()->json(['message' => 'Documento deletado com sucesso']);
    }
}
