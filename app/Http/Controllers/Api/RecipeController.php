<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\Ingredient;
use App\Models\MenuVariant;
use App\Models\Recipe;
use App\Services\RecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecipeController extends Controller
{
    public function __construct(
        protected RecipeService $recipeService
    ) {}

    /**
     * Get paginated list of recipes with cost estimations.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $request->query('outlet_id') ?? $outlet?->id;

        $recipes = $this->recipeService->getRecipes(
            outletId: $outletId ? (int) $outletId : null,
            search: $request->query('search'),
            perPage: (int) ($request->query('per_page', 15))
        );

        return RecipeResource::collection($recipes);
    }

    /**
     * Get single recipe detail with ingredient breakdown and cost calculation.
     */
    public function show(int $id): JsonResponse
    {
        $recipe = $this->recipeService->getDetail($id);

        if (! $recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Resep menu tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new RecipeResource($recipe),
        ]);
    }

    /**
     * Store new recipe.
     */
    public function store(RecipeRequest $request): JsonResponse
    {
        $recipe = $this->recipeService->create($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Resep berhasil dibuat.',
            'data' => new RecipeResource($recipe),
        ], 201);
    }

    /**
     * Update existing recipe.
     */
    public function update(RecipeRequest $request, int $id): JsonResponse
    {
        $recipe = Recipe::findOrFail($id);

        $updated = $this->recipeService->update($recipe, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Resep berhasil diperbarui.',
            'data' => new RecipeResource($updated),
        ]);
    }

    /**
     * Delete recipe.
     */
    public function destroy(int $id): JsonResponse
    {
        $recipe = Recipe::findOrFail($id);

        $this->recipeService->delete($recipe, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Resep berhasil dihapus.',
        ]);
    }

    /**
     * Recalculate cost for specific recipe.
     */
    public function calculate(int $id): JsonResponse
    {
        $recipe = Recipe::with(['menuVariant', 'items.ingredient'])->findOrFail($id);
        $cost = $this->recipeService->calculateCost($recipe);

        return response()->json([
            'success' => true,
            'data' => $cost,
        ]);
    }

    /**
     * Form options for creating/editing recipe (menu variants & ingredients).
     */
    public function options(Request $request): JsonResponse
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $request->query('outlet_id') ?? $outlet?->id;

        // Variants query
        $variantsQuery = MenuVariant::query()->with('menu')
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId));

        $ingredientsQuery = Ingredient::query()
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId));

        $variants = $variantsQuery->get()->map(fn ($v) => [
            'id' => $v->id,
            'name' => ($v->menu?->name ?? 'Menu') . ' - ' . $v->name,
            'price' => (float) $v->price,
            'sales_unit' => $v->sales_unit instanceof \BackedEnum ? $v->sales_unit->value : $v->sales_unit,
            'has_recipe' => Recipe::where('menu_variant_id', $v->id)->exists(),
        ]);

        $ingredients = $ingredientsQuery->get()->map(fn ($i) => [
            'id' => $i->id,
            'name' => $i->name,
            'unit' => $i->unit instanceof \BackedEnum ? $i->unit->value : $i->unit,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'variants' => $variants,
                'ingredients' => $ingredients,
            ],
        ]);
    }
}

