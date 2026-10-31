<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Get system settings.
     */
    public function index(Request $request): JsonResponse
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $request->query('outlet_id') ?? $outlet?->id;

        $settings = $this->settingService->getAllSettings(
            outletId: $outletId ? (int) $outletId : null
        );

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Update system settings.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant.name' => ['nullable', 'string', 'max:100'],
            'restaurant.address' => ['nullable', 'string', 'max:255'],
            'restaurant.phone' => ['nullable', 'string', 'max:30'],
            'restaurant.footer_message' => ['nullable', 'string', 'max:500'],

            'pos.allow_discount' => ['nullable', 'boolean'],
            'pos.require_customer_name' => ['nullable', 'boolean'],
            'pos.default_payment' => ['nullable', 'string', 'in:cash,transfer,qris'],

            'printer.cashier.enabled' => ['nullable', 'boolean'],
            'printer.cashier.printer_name' => ['nullable', 'string', 'max:100'],
            'printer.kitchen.enabled' => ['nullable', 'boolean'],
            'printer.kitchen.printer_name' => ['nullable', 'string', 'max:100'],

            'payment.cash_enabled' => ['nullable', 'boolean'],
            'payment.transfer_enabled' => ['nullable', 'boolean'],
            'payment.qris_enabled' => ['nullable', 'boolean'],

            'inventory.low_stock_threshold' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'outlet_id' => ['nullable', 'integer', 'exists:outlets,id'],

            // Also allow flat keys
            'pos.allow_discount' => ['nullable', 'boolean'],
            'pos.default_payment' => ['nullable', 'string', 'in:cash,transfer,qris'],
        ]);

        // Validation rule from spec: At least one payment method must remain active
        $paymentData = $request->input('payment', []);
        if (isset($paymentData['cash_enabled']) && ! $paymentData['cash_enabled'] &&
            isset($paymentData['transfer_enabled']) && ! $paymentData['transfer_enabled'] &&
            isset($paymentData['qris_enabled']) && ! $paymentData['qris_enabled']) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal satu metode pembayaran harus tetap aktif.',
            ], 422);
        }

        // Validation rule: Printer name required if printer is enabled
        $printerData = $request->input('printer', []);
        if (isset($printerData['cashier']['enabled']) && $printerData['cashier']['enabled']) {
            if (empty($printerData['cashier']['printer_name'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nama printer kasir wajib diisi jika printer kasir diaktifkan.',
                ], 422);
            }
        }
        if (isset($printerData['kitchen']['enabled']) && $printerData['kitchen']['enabled']) {
            if (empty($printerData['kitchen']['printer_name'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nama printer kitchen wajib diisi jika printer kitchen diaktifkan.',
                ], 422);
            }
        }

        $outlet = $request->user()->outlets()->first();
        $outletId = $request->input('outlet_id') ?? $outlet?->id;

        $updated = $this->settingService->updateSettings(
            data: $request->all(),
            outletId: $outletId ? (int) $outletId : null
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan sistem berhasil disimpan.',
            'data' => $updated,
        ]);
    }
}

