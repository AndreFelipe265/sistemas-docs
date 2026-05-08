<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        return response()->json(Template::all());
    }

    public function show($id)
    {
        $template = Template::find($id);

        if (!$template) {
            return response()->json(['message' => 'Template não encontrado'], 404);
        }

        return response()->json($template);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'conteudo' => 'required|string',
            'background_image' => 'nullable|string',
        ]);

        $template = Template::create($request->all());

        return response()->json($template, 201);
    }

    public function update(Request $request, $id)
    {
        $template = Template::find($id);

        if (!$template) {
            return response()->json(['message' => 'Template não encontrado'], 404);
        }

        $template->update($request->all());

        return response()->json($template);
    }

    public function destroy($id)
    {
        $template = Template::find($id);

        if (!$template) {
            return response()->json(['message' => 'Template não encontrado'], 404);
        }

        $template->delete();

        return response()->json(['message' => 'Template deletado com sucesso']);
    }
}