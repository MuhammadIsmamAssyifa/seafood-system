<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OutletRequest;
use App\Http\Resources\OutletResource;
use App\Models\Outlet;
use App\Services\OutletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function __construct(
        protected OutletService $outletService
    ) {}

    /**
     * Get outlets list.
     */
    public function index(Request $request): JsonResponse
    {
        $paginate = ! $request->boolean('all');
        $outlets = $this->outletService->getOutlets(
            status: $request->query('status'),
            search: $request->query('search'),
            paginate: $paginate,
            perPage: (int) ($request->query('per_page', 15))
        );

        if ($paginate) {
            return response()->json([
                'success' => true,
                'data' => OutletResource::collection($outlets),
                'meta' => [
                    'current_page' => $outlets->currentPage(),
                    'last_page' => $outlets->lastPage(),
                    'per_page' => $outlets->perPage(),
                    'total' => $outlets->total(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => OutletResource::collection($outlets),
        ]);
    }

    /**
     * Get single outlet detail.
     */
    public function show(int $id): JsonResponse
    {
        $outlet = $this->outletService->getDetail($id);

        if (! $outlet) {
            return response()->json([
                'success' => false,
                'message' => 'Outlet tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new OutletResource($outlet),
        ]);
    }

    /**
     * Store new outlet.
     */
    public function store(OutletRequest $request): JsonResponse
    {
        $outlet = $this->outletService->create($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Outlet cabang baru berhasil ditambahkan.',
            'data' => new OutletResource($outlet),
        ], 201);
    }

    /**
     * Update outlet details.
     */
    public function update(OutletRequest $request, int $id): JsonResponse
    {
        $outlet = Outlet::findOrFail($id);

        $updated = $this->outletService->update($outlet, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Informasi outlet berhasil diperbarui.',
            'data' => new OutletResource($updated),
        ]);
    }

    /**
     * Delete outlet.
     */
    public function destroy(int $id): JsonResponse
    {
        $outlet = Outlet::findOrFail($id);

        $this->outletService->delete($outlet, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Outlet berhasil dihapus.',
        ]);
    }

    /**
     * Assign employees to outlet.
     */
    public function assignUsers(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $outlet = Outlet::findOrFail($id);
        $updated = $this->outletService->assignUsers($outlet, $validated['user_ids'], $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Daftar karyawan cabang berhasil diperbarui.',
            'data' => new OutletResource($updated),
        ]);
    }
}

