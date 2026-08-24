<?php

namespace App\Features\AI\Http\Controllers;

use App\Features\AI\Models\AiProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiProviderController extends Controller
{
    public function getModels(Request $request): JsonResponse
    {
        $providerSlug = $request->query('provider');
        if (!$providerSlug) {
            return response()->json(['success' => false, 'message' => 'Provider slug required'], 400);
        }

        $provider = AiProvider::where('slug', $providerSlug)->first();
        if (!$provider) {
            return response()->json(['success' => false, 'message' => 'Provider not found'], 404);
        }

        $models = $provider->models()
            ->where('is_active', true)
            ->get(['id', 'name', 'model_id'])
            ->map(fn($model) => [
                'id' => $model->model_id, // Frontend uses model_id as identifier
                'name' => $model->name
            ]);

        return response()->json(['success' => true, 'models' => $models]);
    }
}
