<?php

namespace Tests\Feature;

use App\Enums\OrderBatchStatus;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Models\OrderTable;
use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KitchenOrdersTest extends TestCase
{
    protected Restaurant $restaurant;
    protected Outlet $outlet;
    protected User $kitchenUser;
    protected User $cashierUser;
    protected OrderTable $table1;
    protected OrderTable $table2;
    protected MenuVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();

        $this->restaurant = Restaurant::firstOrCreate(
            ['code' => 'SEAFOOD-RESTO-KDS'],
            ['name' => 'Seafood Resto KDS', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUTLET-KDS-DASHBOARD'],
            ['restaurant_id' => $this->restaurant->id, 'name' => 'KDS Outlet Dashboard', 'status' => 'active']
        );

        $this->kitchenUser = User::firstOrCreate(
            ['username' => 'chef_dashboard'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Chef Dashboard',
                'email' => 'chef.dash@example.com',
                'password' => Hash::make('password123'),
                'pin_hash' => Hash::make('123456'),
                'status' => 'active',
            ]
        );
        $this->kitchenUser->syncRoles(['kitchen']);
        $this->kitchenUser->outlets()->sync([$this->outlet->id]);

        $this->cashierUser = User::firstOrCreate(
            ['username' => 'cashier_dash'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Cashier Dash',
                'email' => 'cashier.dash@example.com',
                'password' => Hash::make('password123'),
                'pin_hash' => Hash::make('1234'),
                'status' => 'active',
            ]
        );
        $this->cashierUser->syncRoles(['cashier']);
        $this->cashierUser->outlets()->sync([$this->outlet->id]);

        $this->table1 = OrderTable::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'number' => 'KD-01'],
            ['status' => 'available']
        );

        $this->table2 = OrderTable::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'number' => 'KD-02'],
            ['status' => 'available']
        );

        $menu = Menu::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Ikan Etong Bakar'],
            ['type' => 'seafood', 'status' => 'active']
        );

        $station = \App\Models\KitchenStation::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'code' => 'GRILL-TEST'],
            ['name' => 'Grill Station', 'status' => 'active']
        );

        $this->variant = MenuVariant::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Etong Bakar Kecap'],
            [
                'menu_id' => $menu->id,
                'kitchen_station_id' => $station->id,
                'price' => 65000,
                'sales_unit' => 'portion',
                'status' => 'active',
            ]
        );

        Order::where('outlet_id', $this->outlet->id)->delete();
    }

    public function test_kitchen_staff_can_fetch_orders_queue_with_expected_structure(): void
    {
        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table1->id,
            'order_number' => 'ORD-KD-'.uniqid(),
            'customer_name' => 'Budi Santoso',
            'guest_count' => 4,
            'status' => OrderStatus::Submitted,
            'created_by' => $this->kitchenUser->id,
        ]);

        $batch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Sent,
            'sent_to_kitchen_at' => now(),
        ]);

        OrderItem::create([
            'order_batch_id' => $batch->id,
            'menu_variant_id' => $this->variant->id,
            'menu_name_snapshot' => 'Ikan Etong Bakar',
            'quantity' => 2,
            'sales_unit' => 'portion',
            'price_snapshot' => 65000,
            'subtotal' => 130000,
            'status' => OrderItemStatus::Waiting,
        ]);

        $token = $this->kitchenUser->createToken('kds-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/kitchen/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'batch_number',
                        'status',
                        'order_number',
                        'customer_name',
                        'guest_count',
                        'table_number',
                        'table' => ['number'],
                        'items' => [
                            '*' => [
                                'id',
                                'menu_name',
                                'quantity',
                                'unit',
                                'status',
                            ],
                        ],
                        'created_at',
                    ],
                ],
            ]);

        $data = $response->json('data');
        $item = collect($data)->firstWhere('id', $batch->id);
        $this->assertNotNull($item);
        $this->assertEquals('Budi Santoso', $item['customer_name']);
        $this->assertEquals(4, $item['guest_count']);
        $this->assertEquals('KD-01', $item['table_number']);
        $this->assertEquals('sent', $item['status']);
        $this->assertCount(1, $item['items']);
        $this->assertEquals('Ikan Etong Bakar', $item['items'][0]['menu_name']);
    }

    public function test_kitchen_orders_are_sorted_fifo_by_created_at(): void
    {
        $order1 = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table1->id,
            'order_number' => 'ORD-FIFO-'.uniqid(),
            'customer_name' => 'Pelanggan 1',
            'status' => OrderStatus::Submitted,
            'created_by' => $this->kitchenUser->id,
        ]);
        $batch1 = OrderBatch::create([
            'order_id' => $order1->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Sent,
        ]);
        $batch1->created_at = now()->subMinutes(15);
        $batch1->save();

        $order2 = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table2->id,
            'order_number' => 'ORD-FIFO-'.uniqid(),
            'customer_name' => 'Pelanggan 2',
            'status' => OrderStatus::Submitted,
            'created_by' => $this->kitchenUser->id,
        ]);
        $batch2 = OrderBatch::create([
            'order_id' => $order2->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Cooking,
        ]);
        $batch2->created_at = now()->subMinutes(5);
        $batch2->save();

        $token = $this->kitchenUser->createToken('kds-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/kitchen/orders');

        $response->assertStatus(200);
        $data = collect($response->json('data'));

        $pos1 = $data->search(fn ($b) => $b['id'] === $batch1->id);
        $pos2 = $data->search(fn ($b) => $b['id'] === $batch2->id);

        $this->assertNotFalse($pos1);
        $this->assertNotFalse($pos2);
        $this->assertLessThan($pos2, $pos1, 'Batch 1 (older) must appear before Batch 2 (newer) FIFO');
    }

    public function test_kitchen_orders_filter_by_status(): void
    {
        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table1->id,
            'order_number' => 'ORD-FLTR-'.uniqid(),
            'customer_name' => 'Pelanggan Filter',
            'status' => OrderStatus::Submitted,
            'created_by' => $this->kitchenUser->id,
        ]);

        $batchWaiting = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Sent,
        ]);

        $batchCooking = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 2,
            'status' => OrderBatchStatus::Cooking,
        ]);

        $token = $this->kitchenUser->createToken('kds-test')->plainTextToken;

        // Filter: cooking
        $resCooking = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/kitchen/orders?status=cooking');

        $resCooking->assertStatus(200);
        $idsCooking = collect($resCooking->json('data'))->pluck('id')->all();
        $this->assertContains($batchCooking->id, $idsCooking);
        $this->assertNotContains($batchWaiting->id, $idsCooking);

        // Filter: waiting
        $resWaiting = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/kitchen/orders?status=waiting');

        $resWaiting->assertStatus(200);
        $idsWaiting = collect($resWaiting->json('data'))->pluck('id')->all();
        $this->assertContains($batchWaiting->id, $idsWaiting);
        $this->assertNotContains($batchCooking->id, $idsWaiting);
    }

    public function test_kitchen_staff_can_fetch_order_detail_with_cooking_method_and_station(): void
    {
        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table1->id,
            'order_number' => 'ORD-DETAIL-'.uniqid(),
            'customer_name' => 'Pak Budi',
            'guest_count' => 3,
            'status' => OrderStatus::Submitted,
            'created_by' => $this->kitchenUser->id,
        ]);

        $batch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Sent,
        ]);

        $item = OrderItem::create([
            'order_batch_id' => $batch->id,
            'menu_variant_id' => $this->variant->id,
            'menu_name_snapshot' => 'Ikan Etong Bakar',
            'quantity' => 2.5,
            'sales_unit' => 'kg',
            'price_snapshot' => 65000,
            'subtotal' => 162500,
            'status' => OrderItemStatus::Waiting,
        ]);

        $token = $this->kitchenUser->createToken('kds-test')->plainTextToken;

        // Fetch using Order ID
        $responseOrder = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/kitchen/orders/{$order->id}");

        $responseOrder->assertStatus(200)
            ->assertJsonPath('data.customer_name', 'Pak Budi')
            ->assertJsonPath('data.guest_count', 3)
            ->assertJsonPath('data.table_number', 'KD-01')
            ->assertJsonPath('data.items.0.name', 'Ikan Etong Bakar')
            ->assertJsonPath('data.items.0.quantity', 2.5)
            ->assertJsonPath('data.items.0.sales_unit', 'kg')
            ->assertJsonPath('data.items.0.station', 'Grill Station');

        // Fetch using Batch ID
        $responseBatch = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/kitchen/orders/{$batch->id}");

        $responseBatch->assertStatus(200)
            ->assertJsonPath('data.customer_name', 'Pak Budi')
            ->assertJsonPath('data.table_number', 'KD-01');
    }

    public function test_kitchen_order_detail_returns_404_when_not_found(): void
    {
        $token = $this->kitchenUser->createToken('kds-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/kitchen/orders/999999');

        $response->assertStatus(404);
    }

    public function test_draft_orders_and_unsubmitted_batches_do_not_appear_in_kitchen(): void
    {
        // 1. Order in Draft status (cashier still inputting)
        $draftOrder = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table1->id,
            'order_number' => 'ORD-DRAFT-'.uniqid(),
            'customer_name' => 'Pelanggan Draft',
            'status' => OrderStatus::Draft,
            'created_by' => $this->kitchenUser->id,
        ]);

        $batchInDraft = OrderBatch::create([
            'order_id' => $draftOrder->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Waiting,
        ]);

        // 2. Order Submitted, but batch is still Waiting (not sent to kitchen yet)
        $submittedOrder = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table2->id,
            'order_number' => 'ORD-SUB-'.uniqid(),
            'customer_name' => 'Pelanggan Submitted Unsent',
            'status' => OrderStatus::Submitted,
            'created_by' => $this->kitchenUser->id,
        ]);

        $unsentBatch = OrderBatch::create([
            'order_id' => $submittedOrder->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Waiting,
        ]);

        // 3. Legitimate batch that HAS been sent to kitchen
        $sentBatch = OrderBatch::create([
            'order_id' => $submittedOrder->id,
            'batch_number' => 2,
            'status' => OrderBatchStatus::Sent,
            'sent_to_kitchen_at' => now(),
        ]);

        $token = $this->kitchenUser->createToken('kds-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/kitchen/orders');

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertNotContains($batchInDraft->id, $ids, 'Draft order batch must NOT appear in kitchen queue');
        $this->assertNotContains($unsentBatch->id, $ids, 'Unsubmitted batch must NOT appear in kitchen queue');
        $this->assertContains($sentBatch->id, $ids, 'Sent batch MUST appear in kitchen queue');

        // Cannot view draft order detail in kitchen
        $resDetailDraft = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/kitchen/orders/{$draftOrder->id}");
        $resDetailDraft->assertStatus(404);
    }
}
