<?php

namespace Tests\Feature;

use App\Enums\ActiveStatus;
use App\Enums\IngredientUnit;
use App\Enums\StockMovementType;
use App\Enums\WasteReason;
use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\WasteRecord;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class WasteManagementTest extends TestCase
{
    protected Restaurant $restaurant;
    protected Outlet $outlet;
    protected User $owner;
    protected User $kitchenUser;
    protected Ingredient $ingredient;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();

        $this->restaurant = Restaurant::firstOrCreate(
            ['code' => 'SEAFOOD-WASTE-TEST'],
            ['name' => 'Seafood Waste Resto', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUTLET-WASTE-01'],
            ['restaurant_id' => $this->restaurant->id, 'name' => 'Main Outlet Waste', 'status' => 'active']
        );

        $this->owner = User::firstOrCreate(
            ['email' => 'owner_waste@seafood.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Owner Waste Test',
                'username' => 'owner_waste',
                'password' => bcrypt('password123'),
                'status' => 'active',
            ]
        );
        $this->owner->syncRoles(['owner']);
        $this->owner->outlets()->sync([$this->outlet->id]);

        $this->kitchenUser = User::firstOrCreate(
            ['email' => 'kitchen_waste@seafood.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Kitchen Staff Waste',
                'username' => 'kitchen_waste',
                'password' => bcrypt('password123'),
                'status' => 'active',
            ]
        );
        $this->kitchenUser->syncRoles(['kitchen']);
        $this->kitchenUser->outlets()->sync([$this->outlet->id]);

        $this->ingredient = Ingredient::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Ikan Etong Waste Test'],
            [
                'unit' => IngredientUnit::Kg,
                'type' => 'raw_material',
                'status' => ActiveStatus::Active,
            ]
        );

        // Ensure clean state for test ingredient
        InventoryBatch::where('ingredient_id', $this->ingredient->id)->delete();
        WasteRecord::where('ingredient_id', $this->ingredient->id)->delete();
        StockMovement::where('ingredient_id', $this->ingredient->id)->delete();
    }

    public function test_can_create_waste_record_with_fifo_deduction(): void
    {
        // Setup 2 batches: batch 1 has 3 kg @ Rp 80.000, batch 2 has 5 kg @ Rp 90.000
        $batch1 = InventoryBatch::create([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $this->ingredient->id,
            'quantity' => 3.000,
            'remaining_quantity' => 3.000,
            'cost_per_unit' => 80000,
            'received_at' => now()->subDays(2),
        ]);

        $batch2 = InventoryBatch::create([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $this->ingredient->id,
            'quantity' => 5.000,
            'remaining_quantity' => 5.000,
            'cost_per_unit' => 90000,
            'received_at' => now()->subDay(),
        ]);

        $token = $this->owner->createToken('waste-test')->plainTextToken;

        // Record waste of 4 kg (3 kg from batch 1 @ 80.000 = 240.000; 1 kg from batch 2 @ 90.000 = 90.000. Total = 330.000)
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/waste', [
                'ingredient_id' => $this->ingredient->id,
                'quantity' => 4.000,
                'reason' => 'damaged',
                'note' => 'Ikan berbau tidak sedap saat pengecekan freezer',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.ingredient_id', $this->ingredient->id)
            ->assertJsonPath('data.quantity', 4)
            ->assertJsonPath('data.total_cost', 330000)
            ->assertJsonPath('data.reason', 'damaged');

        $wasteId = $response->json('data.id');

        // Assert database record
        $this->assertDatabaseHas('waste_records', [
            'id' => $wasteId,
            'ingredient_id' => $this->ingredient->id,
            'quantity' => 4.000,
            'total_cost' => 330000,
            'reason' => 'damaged',
        ]);

        // Assert FIFO deduction in batches
        $this->assertEquals(0, (float) $batch1->fresh()->remaining_quantity);
        $this->assertEquals(4.000, (float) $batch2->fresh()->remaining_quantity);

        // Assert stock movements created
        $this->assertDatabaseHas('stock_movements', [
            'ingredient_id' => $this->ingredient->id,
            'inventory_batch_id' => $batch1->id,
            'type' => StockMovementType::Waste->value,
            'quantity' => -3.000,
            'total_cost' => 240000,
            'reference_type' => WasteRecord::class,
            'reference_id' => $wasteId,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'ingredient_id' => $this->ingredient->id,
            'inventory_batch_id' => $batch2->id,
            'type' => StockMovementType::Waste->value,
            'quantity' => -1.000,
            'total_cost' => 90000,
            'reference_type' => WasteRecord::class,
            'reference_id' => $wasteId,
        ]);

        // Assert AuditLog created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'waste_created',
            'auditable_type' => WasteRecord::class,
            'auditable_id' => $wasteId,
        ]);
    }

    public function test_rejects_waste_when_stock_is_insufficient(): void
    {
        $batch = InventoryBatch::create([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $this->ingredient->id,
            'quantity' => 2.000,
            'remaining_quantity' => 2.000,
            'cost_per_unit' => 50000,
            'received_at' => now(),
        ]);

        $token = $this->owner->createToken('waste-test')->plainTextToken;

        // Try to record 10 kg waste when only 2 kg available
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/waste', [
                'ingredient_id' => $this->ingredient->id,
                'quantity' => 10.000,
                'reason' => 'expired',
                'note' => 'Melebihi stok',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity'])
            ->assertJsonPath('message', 'Stok bahan tidak mencukupi.');

        // Stock must remain untouched
        $this->assertEquals(2.000, (float) $batch->fresh()->remaining_quantity);
    }

    public function test_waste_summary_endpoint(): void
    {
        WasteRecord::create([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $this->ingredient->id,
            'quantity' => 2.000,
            'unit_cost' => 80000,
            'total_cost' => 160000,
            'reason' => 'damaged',
            'note' => 'Rusak',
            'created_by' => $this->owner->id,
        ]);

        WasteRecord::create([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $this->ingredient->id,
            'quantity' => 1.500,
            'unit_cost' => 80000,
            'total_cost' => 120000,
            'reason' => 'expired',
            'note' => 'Kadaluarsa',
            'created_by' => $this->owner->id,
        ]);

        $token = $this->owner->createToken('waste-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/waste/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_cost',
                'total_quantity',
                'total_records',
                'waste_percentage',
                'most_wasted_ingredient',
                'reason_breakdown',
            ]);

        $this->assertGreaterThanOrEqual(280000, (float) $response->json('total_cost'));
        $this->assertGreaterThanOrEqual(3.5, (float) $response->json('total_quantity'));
    }

    public function test_waste_show_detail_endpoint(): void
    {
        $waste = WasteRecord::create([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $this->ingredient->id,
            'quantity' => 1.250,
            'unit_cost' => 75000,
            'total_cost' => 93750,
            'reason' => 'cooking_loss',
            'note' => 'Penyusutan saat proses pembersihan',
            'created_by' => $this->owner->id,
        ]);

        $token = $this->owner->createToken('waste-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/waste/{$waste->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $waste->id)
            ->assertJsonPath('data.ingredient_name', 'Ikan Etong Waste Test')
            ->assertJsonPath('data.quantity', 1.25)
            ->assertJsonPath('data.total_cost', 93750)
            ->assertJsonPath('data.reason', 'cooking_loss')
            ->assertJsonPath('data.reason_label', 'Susut Masak / Handling');
    }

    public function test_waste_index_with_filters(): void
    {
        $token = $this->owner->createToken('waste-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/waste?reason=damaged');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'total'],
            ]);
    }

    public function test_unauthorized_user_cannot_access_waste(): void
    {
        // Unauthenticated
        $responseGuest = $this->getJson('/api/v1/waste');
        $responseGuest->assertStatus(401);

        // Kitchen staff lacks inventory.view / inventory.manage
        $tokenKitchen = $this->kitchenUser->createToken('kitchen-test')->plainTextToken;
        $responseKitchen = $this->withHeader('Authorization', "Bearer {$tokenKitchen}")
            ->getJson('/api/v1/waste');
        $responseKitchen->assertStatus(403);
    }
}
