<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Template::where('user_id', $request->user()->id)->latest()->get()
        );
    }

    public function show(Request $request, $id)
    {
        $template = Template::where('user_id', $request->user()->id)->find($id);

        if (!$template) {
            return response()->json(['message' => 'Template nao encontrado'], 404);
        }

        return response()->json($template);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'conteudo' => 'required|string',
            'background_image' => 'nullable|string',
        ]);

        $template = Template::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($template, 201);
    }

    public function update(Request $request, $id)
    {
        $template = Template::where('user_id', $request->user()->id)->find($id);

        if (!$template) {
            return response()->json(['message' => 'Template nao encontrado'], 404);
        }

        $data = $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'conteudo' => 'sometimes|required|string',
            'background_image' => 'nullable|string',
        ]);

        $template->update($data);

        return response()->json($template);
    }

    public function destroy(Request $request, $id)
    {
        $template = Template::where('user_id', $request->user()->id)->find($id);

        if (!$template) {
            return response()->json(['message' => 'Template nao encontrado'], 404);
        }

        $template->delete();

        return response()->json(['message' => 'Template deletado com sucesso']);
    }
}
