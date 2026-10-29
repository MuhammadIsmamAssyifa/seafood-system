<?php

namespace Tests\Feature;

use App\Enums\OrderBatchStatus;
use App\Enums\OrderItemChangeType;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Events\Concerns\ResolvesKdsChannel;
use App\Events\KdsBatchSubmitted;
use App\Events\KdsBatchUpdated;
use App\Events\KdsItemChanged;
use App\Events\KdsItemUpdated;
use App\Models\CookingMethod;
use App\Models\Ingredient;
use App\Models\KitchenStation;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Models\OrderItemChange;
use App\Models\OrderTable;
use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\KitchenService;
use App\Services\OrderBatchService;
use App\Services\OrderItemChangeService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeKdsTest extends TestCase
{
    protected User $user;

    protected Outlet $outlet;

    protected OrderTable $table;

    protected MenuVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();

        $restaurant = Restaurant::firstOrCreate(
            ['code' => 'SEAFOOD-RESTO'],
            ['name' => 'Seafood Resto', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUTLET-01'],
            ['restaurant_id' => $restaurant->id, 'name' => 'Main Outlet', 'status' => 'active']
        );

        $this->user = User::firstOrCreate(
            ['email' => 'kds_tester@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'KDS Tester',
                'status' => 'active',
                'password' => bcrypt('password123'),
            ]
        );
        $this->user->syncRoles(['owner']);
        $this->user->outlets()->sync([$this->outlet->id]);

        $this->table = OrderTable::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'number' => 'KDS-01'],
            ['status' => 'available']
        );

        $station = KitchenStation::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'code' => 'FRY'],
            ['name' => 'Fry Station', 'status' => 'active']
        );

        $cooking = CookingMethod::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Goreng Tepung'],
            ['status' => 'active']
        );

        $ingredient = Ingredient::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Udang Windu KDS'],
            ['unit' => 'kg', 'status' => 'active']
        );

        $menu = Menu::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Udang Goreng Tepung'],
            ['type' => 'seafood', 'status' => 'active']
        );

        $this->variant = MenuVariant::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Udang Goreng Porsi'],
            [
                'menu_id' => $menu->id,
                'cooking_method_id' => $cooking->id,
                'main_ingredient_id' => $ingredient->id,
                'kitchen_station_id' => $station->id,
                'price' => 45000,
                'sales_unit' => 'portion',
                'conversion_rate' => 0.3,
                'status' => 'active',
            ]
        );
    }

    public function test_channel_resolution_matches_pusher_channels(): void
    {
        $mock = new class
        {
            use ResolvesKdsChannel;
        };

        config(['broadcasting.connections.pusher.kds_channel' => 'pos-seafood-kds-development']);
        $this->assertEquals('pos-seafood-kds-development', $mock::channelName());

        config(['broadcasting.connections.pusher.kds_channel' => 'pos-seafood-kds-staging']);
        $this->assertEquals('pos-seafood-kds-staging', $mock::channelName());

        config(['broadcasting.connections.pusher.kds_channel' => 'pos-seafood-kds-production']);
        $this->assertEquals('pos-seafood-kds-production', $mock::channelName());
    }

    public function test_submitting_batch_dispatches_realtime_event_to_kds_channel(): void
    {
        Event::fake([KdsBatchSubmitted::class]);

        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table->id,
            'order_number' => 'ORD-'.date('YmdHis').'-KDS1',
            'customer_name' => 'Budi Realtime',
            'guest_count' => 2,
            'status' => OrderStatus::Draft,
            'created_by' => $this->user->id,
        ]);

        $batch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Waiting,
        ]);

        OrderItem::create([
            'order_batch_id' => $batch->id,
            'menu_variant_id' => $this->variant->id,
            'menu_name_snapshot' => $this->variant->name,
            'quantity' => 2,
            'sales_unit' => 'portion',
            'price_snapshot' => 45000,
            'subtotal' => 90000,
            'status' => OrderItemStatus::Waiting,
        ]);

        app(OrderBatchService::class)->submit($batch);

        Event::assertDispatched(KdsBatchSubmitted::class, function (KdsBatchSubmitted $event) use ($batch) {
            $channels = $event->broadcastOn();
            $this->assertNotEmpty($channels);
            $this->assertEquals(config('broadcasting.connections.pusher.kds_channel', 'pos-seafood-kds-development'), $channels[0]->name);
            $this->assertEquals('kds.batch.submitted', $event->broadcastAs());

            return $event->batch->id === $batch->id;
        });
    }

    public function test_kitchen_workflow_dispatches_realtime_events(): void
    {
        Event::fake([KdsBatchUpdated::class, KdsItemUpdated::class]);

        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table->id,
            'order_number' => 'ORD-'.date('YmdHis').'-KDS2',
            'customer_name' => 'Siti Realtime',
            'guest_count' => 2,
            'status' => OrderStatus::Submitted,
            'created_by' => $this->user->id,
        ]);

        $batch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Sent,
        ]);

        $item = OrderItem::create([
            'order_batch_id' => $batch->id,
            'menu_variant_id' => $this->variant->id,
            'menu_name_snapshot' => $this->variant->name,
            'quantity' => 1,
            'sales_unit' => 'portion',
            'price_snapshot' => 45000,
            'subtotal' => 45000,
            'status' => OrderItemStatus::Waiting,
        ]);

        app(InventoryService::class)->addStock([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => (int) $this->variant->main_ingredient_id,
            'quantity' => 10,
            'cost_per_unit' => 25000,
        ]);

        $kitchenService = app(KitchenService::class);

        $kitchenService->acceptBatch($batch);
        Event::assertDispatched(KdsBatchUpdated::class, function ($e) use ($batch) {
            return $e->batch->id === $batch->id;
        });

        $kitchenService->prepareItem($item);
        Event::assertDispatched(KdsItemUpdated::class, function ($e) use ($item) {
            return $e->item->id === $item->id;
        });

        $kitchenService->readyItem($item);
        $kitchenService->serveItem($item);
        Event::assertDispatchedTimes(KdsItemUpdated::class, 3);
    }

    public function test_order_item_change_approval_dispatches_kds_event(): void
    {
        Event::fake([KdsItemChanged::class]);

        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table->id,
            'order_number' => 'ORD-'.date('YmdHis').'-KDS3',
            'customer_name' => 'Pak Joko',
            'guest_count' => 1,
            'status' => OrderStatus::Draft,
            'created_by' => $this->user->id,
        ]);

        $batch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Waiting,
        ]);

        $item = OrderItem::create([
            'order_batch_id' => $batch->id,
            'menu_variant_id' => $this->variant->id,
            'menu_name_snapshot' => $this->variant->name,
            'quantity' => 1,
            'sales_unit' => 'portion',
            'price_snapshot' => 45000,
            'subtotal' => 45000,
            'status' => OrderItemStatus::Waiting,
        ]);

        $change = OrderItemChange::create([
            'order_item_id' => $item->id,
            'type' => OrderItemChangeType::QuantityChange,
            'old_quantity' => 1.0,
            'new_quantity' => 1.3,
            'old_price' => 45000,
            'new_price' => 55000,
            'status' => 'pending_customer',
            'reason' => 'Timbangan ikan lebih berat dari pasar',
            'created_by' => $this->user->id,
        ]);

        $changeService = app(OrderItemChangeService::class);
        $changeService->approve($change);

        Event::assertDispatched(KdsItemChanged::class, function ($e) use ($change) {
            return $e->change->id === $change->id;
        });
    }
}
