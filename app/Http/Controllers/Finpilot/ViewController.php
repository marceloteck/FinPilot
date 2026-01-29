<?php

namespace App\Http\Controllers\Finpilot;

use App\Http\Controllers\Controller;
use App\Models\SavedView;
use Illuminate\Http\Request;

class ViewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'entity' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'config_json' => ['required', 'array'],
        ]);

        SavedView::create([
            'user_id' => $request->user()?->id,
            'entity' => $data['entity'],
            'name' => $data['name'],
            'config_json' => $data['config_json'],
            'is_default' => false,
        ]);

        return redirect()->back()->with('success', 'Visão salva com sucesso.');
    }

    public function update(Request $request, SavedView $view)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'config_json' => ['required', 'array'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $view->update([
            'name' => $data['name'],
            'config_json' => $data['config_json'],
            'is_default' => $data['is_default'] ?? $view->is_default,
        ]);

        return redirect()->back()->with('success', 'Visão atualizada.');
    }

    public function destroy(SavedView $view)
    {
        if ($view->is_default) {
            return redirect()->back()->with('warning', 'A visão padrão não pode ser removida.');
        }

        $view->delete();

        return redirect()->back()->with('success', 'Visão removida.');
    }
}
