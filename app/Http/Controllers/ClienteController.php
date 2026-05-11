<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Cliente::where('user_id', $request->user()->id)->latest()->get()
        );
    }

    public function show(Request $request, $id)
    {
        $cliente = Cliente::where('user_id', $request->user()->id)->find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente nao encontrado'], 404);
        }

        return response()->json($cliente);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:30',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:20',
        ]);

        $cliente = Cliente::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($cliente, 201);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::where('user_id', $request->user()->id)->find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente nao encontrado'], 404);
        }

        $data = $request->validate([
            'nome' => 'sometimes|required|string|max:255',
            'cpf' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:30',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:20',
        ]);

        $cliente->update($data);

        return response()->json($cliente);
    }

    public function destroy(Request $request, $id)
    {
        $cliente = Cliente::where('user_id', $request->user()->id)->find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente nao encontrado'], 404);
        }

        $cliente->delete();

        return response()->json(['message' => 'Cliente deletado com sucesso']);
    }
}
