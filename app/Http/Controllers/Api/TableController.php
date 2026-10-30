<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TableResource;
use App\Models\OrderTable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TableController extends Controller
{
    /**
     * Get list of tables for user's outlet.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;

        $tables = OrderTable::query()
            ->where('outlet_id', $outletId)
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('search'), fn ($q, $search) => $q->where('number', 'like', "%{$search}%"))
            ->orderByRaw('LENGTH(number) ASC, number ASC')
            ->get();

        return TableResource::collection($tables);
    }

    /**
     * Show single table details.
     */
    public function show(OrderTable $table): TableResource
    {
        return new TableResource($table);
    }
}

