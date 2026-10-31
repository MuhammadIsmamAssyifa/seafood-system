<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __construct(
        protected GlobalSearchService $searchService
    ) {}

    /**
     * Search across system models.
     */
    public function search(Request $request): JsonResponse
    {
        $query = (string) $request->query('q', '');
        $outletId = $request->query('outlet_id');

        if ($outletId !== null) {
            $outletId = (int) $outletId;
        } else {
            $userOutlet = $request->user()?->outlets()->first();
            $outletId = $userOutlet?->id;
        }

        $results = $this->searchService->search($query, $outletId);

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }
}

