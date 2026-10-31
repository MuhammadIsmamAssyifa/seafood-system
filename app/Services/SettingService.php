<?php

namespace App\Services;

use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Default configurations.
     */
    protected array $defaults = [
        'pos.allow_discount' => ['value' => true, 'type' => 'boolean'],
        'pos.require_customer_name' => ['value' => true, 'type' => 'boolean'],
        'pos.default_payment' => ['value' => 'cash', 'type' => 'string'],
        'printer.cashier.enabled' => ['value' => true, 'type' => 'boolean'],
        'printer.cashier.printer_name' => ['value' => 'EPSON TM-T82', 'type' => 'string'],
        'printer.kitchen.enabled' => ['value' => true, 'type' => 'boolean'],
        'printer.kitchen.printer_name' => ['value' => 'Kitchen Printer', 'type' => 'string'],
        'payment.cash_enabled' => ['value' => true, 'type' => 'boolean'],
        'payment.transfer_enabled' => ['value' => true, 'type' => 'boolean'],
        'payment.qris_enabled' => ['value' => true, 'type' => 'boolean'],
        'inventory.low_stock_threshold' => ['value' => 10, 'type' => 'integer'],
    ];

    /**
     * Resolve effective restaurant and outlet IDs.
     */
    protected function resolveScope(?int $outletId = null, ?int $restaurantId = null): array
    {
        $user = auth()->user();
        $effOutletId = $outletId ?? $user?->outlets()->first()?->id;

        if ($effOutletId) {
            $outlet = Outlet::find($effOutletId);
            $effRestaurantId = $restaurantId ?? $outlet?->restaurant_id ?? $user?->restaurant_id;
        } else {
            $effRestaurantId = $restaurantId ?? $user?->restaurant_id ?? Restaurant::first()?->id ?? 1;
        }

        return [$effRestaurantId, $effOutletId];
    }

    /**
     * Get a setting by key.
     */
    public function get(string $key, mixed $default = null, ?int $outletId = null, ?int $restaurantId = null): mixed
    {
        [$resId, $outId] = $this->resolveScope($outletId, $restaurantId);

        // First check outlet-level override
        if ($outId) {
            $setting = Setting::where('restaurant_id', $resId)
                ->where('outlet_id', $outId)
                ->where('key', $key)
                ->first();

            if ($setting) {
                return $setting->typed_value;
            }
        }

        // Second check restaurant-level
        $setting = Setting::where('restaurant_id', $resId)
            ->whereNull('outlet_id')
            ->where('key', $key)
            ->first();

        if ($setting) {
            return $setting->typed_value;
        }

        // Third check fallback defaults
        if (isset($this->defaults[$key])) {
            return $this->defaults[$key]['value'];
        }

        return $default;
    }

    /**
     * Set a single setting.
     */
    public function set(string $key, mixed $value, ?string $type = null, ?int $outletId = null, ?int $restaurantId = null): Setting
    {
        [$resId, $outId] = $this->resolveScope($outletId, $restaurantId);

        $inferredType = $type;
        if (! $inferredType) {
            if (isset($this->defaults[$key])) {
                $inferredType = $this->defaults[$key]['type'];
            } elseif (is_bool($value)) {
                $inferredType = 'boolean';
            } elseif (is_int($value)) {
                $inferredType = 'integer';
            } elseif (is_array($value)) {
                $inferredType = 'json';
            } else {
                $inferredType = 'string';
            }
        }

        $serializedValue = Setting::serializeValue($value, $inferredType);

        return Setting::updateOrCreate(
            [
                'restaurant_id' => $resId,
                'outlet_id' => $outId,
                'key' => $key,
            ],
            [
                'value' => $serializedValue,
                'type' => $inferredType,
            ]
        );
    }

    /**
     * Get settings for a specific group prefix (e.g. 'pos', 'printer', 'payment').
     */
    public function getGroup(string $group, ?int $outletId = null, ?int $restaurantId = null): array
    {
        $all = $this->getAllSettings($outletId, $restaurantId);
        return $all[$group] ?? [];
    }

    /**
     * Get all settings grouped.
     */
    public function getAllSettings(?int $outletId = null, ?int $restaurantId = null): array
    {
        [$resId, $outId] = $this->resolveScope($outletId, $restaurantId);
        $restaurant = Restaurant::find($resId) ?? Restaurant::first();
        $outlet = $outId ? Outlet::find($outId) : null;

        // Load existing DB records for this restaurant/outlet
        $settingsQuery = Setting::where('restaurant_id', $resId);
        if ($outId) {
            $settingsQuery->where(function ($q) use ($outId) {
                $q->where('outlet_id', $outId)->orWhereNull('outlet_id');
            });
        } else {
            $settingsQuery->whereNull('outlet_id');
        }

        $savedSettings = $settingsQuery->get()->keyBy('key');

        $getValue = function (string $key) use ($savedSettings) {
            if (isset($savedSettings[$key])) {
                return $savedSettings[$key]->typed_value;
            }
            return $this->defaults[$key]['value'] ?? null;
        };

        return [
            'restaurant' => [
                'name' => $restaurant?->name ?? 'Seafood Bahari POS',
                'address' => $restaurant?->address ?? 'Jl. Pelabuhan Ikan No. 88',
                'phone' => $restaurant?->phone ?? '0812-3456-7890',
                'footer_message' => $restaurant?->footer_message ?? "*** TERIMA KASIH ***\nSelamat Menikmati Hidangan Kami",
                'code' => $restaurant?->code ?? 'SEAFOOD',
                'outlet_name' => $outlet?->name ?? 'Cabang Utama',
            ],
            'pos' => [
                'allow_discount' => (bool) $getValue('pos.allow_discount'),
                'require_customer_name' => (bool) $getValue('pos.require_customer_name'),
                'default_payment' => (string) $getValue('pos.default_payment'),
            ],
            'printer' => [
                'cashier' => [
                    'enabled' => (bool) $getValue('printer.cashier.enabled'),
                    'printer_name' => (string) $getValue('printer.cashier.printer_name'),
                ],
                'kitchen' => [
                    'enabled' => (bool) $getValue('printer.kitchen.enabled'),
                    'printer_name' => (string) $getValue('printer.kitchen.printer_name'),
                ],
            ],
            'payment' => [
                'cash_enabled' => (bool) $getValue('payment.cash_enabled'),
                'transfer_enabled' => (bool) $getValue('payment.transfer_enabled'),
                'qris_enabled' => (bool) $getValue('payment.qris_enabled'),
            ],
            'inventory' => [
                'low_stock_threshold' => (int) $getValue('inventory.low_stock_threshold'),
            ],
        ];
    }

    /**
     * Batch update system settings and sync restaurant profile.
     */
    public function updateSettings(array $data, ?int $outletId = null, ?int $restaurantId = null): array
    {
        [$resId, $outId] = $this->resolveScope($outletId, $restaurantId);
        $restaurant = Restaurant::find($resId) ?? Restaurant::first();

        $oldGrouped = $this->getAllSettings($outId, $resId);

        DB::transaction(function () use ($data, $resId, $outId, $restaurant) {
            // 1. Sync restaurant profile if provided
            if (isset($data['restaurant']) && is_array($data['restaurant'])) {
                $restoData = $data['restaurant'];
                if ($restaurant) {
                    $restaurant->update(array_filter([
                        'name' => $restoData['name'] ?? null,
                        'address' => $restoData['address'] ?? null,
                        'phone' => $restoData['phone'] ?? null,
                        'footer_message' => $restoData['footer_message'] ?? null,
                    ], fn ($v) => $v !== null));
                }
            }

            // 2. Direct key-value settings or nested arrays
            $flatPairs = [];

            // Flat keys like "pos.allow_discount" => true
            foreach ($data as $k => $v) {
                if (str_contains($k, '.')) {
                    $flatPairs[$k] = $v;
                }
            }

            // Grouped POS
            if (isset($data['pos']) && is_array($data['pos'])) {
                if (isset($data['pos']['allow_discount'])) {
                    $flatPairs['pos.allow_discount'] = $data['pos']['allow_discount'];
                }
                if (isset($data['pos']['require_customer_name'])) {
                    $flatPairs['pos.require_customer_name'] = $data['pos']['require_customer_name'];
                }
                if (isset($data['pos']['default_payment'])) {
                    $flatPairs['pos.default_payment'] = $data['pos']['default_payment'];
                }
            }

            // Grouped Printer
            if (isset($data['printer']) && is_array($data['printer'])) {
                if (isset($data['printer']['cashier']) && is_array($data['printer']['cashier'])) {
                    if (isset($data['printer']['cashier']['enabled'])) {
                        $flatPairs['printer.cashier.enabled'] = $data['printer']['cashier']['enabled'];
                    }
                    if (isset($data['printer']['cashier']['printer_name'])) {
                        $flatPairs['printer.cashier.printer_name'] = $data['printer']['cashier']['printer_name'];
                    }
                }
                if (isset($data['printer']['kitchen']) && is_array($data['printer']['kitchen'])) {
                    if (isset($data['printer']['kitchen']['enabled'])) {
                        $flatPairs['printer.kitchen.enabled'] = $data['printer']['kitchen']['enabled'];
                    }
                    if (isset($data['printer']['kitchen']['printer_name'])) {
                        $flatPairs['printer.kitchen.printer_name'] = $data['printer']['kitchen']['printer_name'];
                    }
                }
            }

            // Grouped Payment
            if (isset($data['payment']) && is_array($data['payment'])) {
                if (isset($data['payment']['cash_enabled'])) {
                    $flatPairs['payment.cash_enabled'] = $data['payment']['cash_enabled'];
                }
                if (isset($data['payment']['transfer_enabled'])) {
                    $flatPairs['payment.transfer_enabled'] = $data['payment']['transfer_enabled'];
                }
                if (isset($data['payment']['qris_enabled'])) {
                    $flatPairs['payment.qris_enabled'] = $data['payment']['qris_enabled'];
                }
            }

            // Grouped Inventory
            if (isset($data['inventory']) && is_array($data['inventory'])) {
                if (isset($data['inventory']['low_stock_threshold'])) {
                    $flatPairs['inventory.low_stock_threshold'] = $data['inventory']['low_stock_threshold'];
                }
            }

            // Save all resolved flat pairs
            foreach ($flatPairs as $key => $val) {
                $type = $this->defaults[$key]['type'] ?? (is_bool($val) ? 'boolean' : (is_int($val) ? 'integer' : 'string'));
                $this->set($key, $val, $type, $outId, $resId);
            }
        });

        $newGrouped = $this->getAllSettings($outId, $resId);

        // Audit Log entry
        $this->auditLogService->log(
            action: 'settings_updated',
            description: 'Owner/Admin memperbarui konfigurasi sistem dan preferensi aplikasi.',
            auditable: $restaurant,
            oldValues: $oldGrouped,
            newValues: $newGrouped,
            module: 'Settings'
        );

        return $newGrouped;
    }
}

