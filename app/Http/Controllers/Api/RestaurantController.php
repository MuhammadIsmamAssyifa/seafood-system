<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    /**
     * Get restaurant receipt settings.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $restaurant = $user?->restaurant ?? Restaurant::find(1) ?? Restaurant::first();

        if (! $restaurant) {
            $restaurant = Restaurant::firstOrCreate(
                ['code' => 'MAIN'],
                [
                    'name' => 'SEAFOOD RESTO POS',
                    'phone' => '0812-3456-7890',
                    'address' => 'Jl. Pelabuhan Ikan No. 88',
                    'footer_message' => "*** TERIMA KASIH ***\nSelamat Menikmati Hidangan Kami",
                    'status' => 'active',
                ]
            );
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'code' => $restaurant->code,
                'phone' => $restaurant->phone ?? '0812-3456-7890',
                'address' => $restaurant->address ?? 'Jl. Pelabuhan Ikan No. 88',
                'footer_message' => $restaurant->footer_message ?? "*** TERIMA KASIH ***\nSelamat Menikmati Hidangan Kami",
                'status' => $restaurant->status,
            ],
        ]);
    }

    /**
     * Update restaurant profile & receipt settings (Header & Footer).
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'footer_message' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $restaurant = $user?->restaurant ?? Restaurant::find(1) ?? Restaurant::first();

        if (! $restaurant) {
            $restaurant = Restaurant::create(array_merge([
                'name' => 'SEAFOOD RESTO POS',
                'code' => 'MAIN',
                'status' => 'active',
            ], $validated));
        } else {
            $restaurant->update($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan struk restoran berhasil diperbarui',
            'data' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'code' => $restaurant->code,
                'phone' => $restaurant->phone,
                'address' => $restaurant->address,
                'footer_message' => $restaurant->footer_message,
                'status' => $restaurant->status,
            ],
        ]);
    }
}
