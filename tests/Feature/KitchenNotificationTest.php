<?php

namespace Tests\Feature;

use App\Enums\OrderBatchStatus;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\KitchenStation;
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

class KitchenNotificationTest extends TestCase
{
    protected Restaurant $restaurant;
    protected Outlet $outlet;
    protected User $kitchenUser;
    protected OrderTable $table;
    protected MenuVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();

        $this->restaurant = Restaurant::firstOrCreate(
            ['code' => 'SEAFOOD-RESTO-NOTIF'],
            ['name' => 'Seafood Resto Notif', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUTLET-NOTIF'],
            ['restaurant_id' => $this->restaurant->id, 'name' => 'Outlet Notif', 'status' => 'active']
        );

        $this->kitchenUser = User::firstOrCreate(
            ['username' => 'chef_notif'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Chef Notif',
                'email' => 'chef.notif@example.com',
                'password' => Hash::make('password123'),
                'pin_hash' => Hash::make('123456'),
                'status' => 'active',
            ]
        );
        $this->kitchenUser->syncRoles(['kitchen']);
        $this->kitchenUser->outlets()->sync([$this->outlet->id]);

        $this->table = OrderTable::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'number' => 'N-01'],
            ['status' => 'available']
        );

        $station = KitchenStation::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'code' => 'ST-NOTIF'],
            ['name' => 'Station Notif', 'status' => 'active']
        );

        $menu = Menu::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Kepiting Saus Padang Notif'],
            ['type' => 'seafood', 'status' => 'active']
        );

        $this->variant = MenuVariant::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Kepiting Super Notif'],
            [
                'menu_id' => $menu->id,
                'kitchen_station_id' => $station->id,
                'price' => 120000,
                'sales_unit' => 'portion',
                'status' => 'active',
            ]
        );
    }

    public function test_kitchen_queue_includes_sent_to_kitchen_timestamp_for_timers(): void
    {
        $sentTime = now()->subMinutes(8);

        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table->id,
            'order_number' => 'ORD-NTF-'.uniqid(),
            'customer_name' => 'Pak Notif',
            'status' => OrderStatus::Submitted,
            'created_by' => $this->kitchenUser->id,
        ]);

        $batch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Sent,
            'sent_to_kitchen_at' => $sentTime,
        ]);

        OrderItem::create([
            'order_batch_id' => $batch->id,
            'menu_variant_id' => $this->variant->id,
            'menu_name_snapshot' => 'Kepiting Super Notif',
            'quantity' => 1,
            'sales_unit' => 'portion',
            'price_snapshot' => 120000,
            'subtotal' => 120000,
            'status' => OrderItemStatus::Waiting,
        ]);

        $token = $this->kitchenUser->createToken('kds-notif')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/kitchen/orders');

        $response->assertStatus(200);
        $data = collect($response->json('data'));

        $found = $data->firstWhere('id', $batch->id);
        $this->assertNotNull($found);
        $this->assertNotNull($found['sent_to_kitchen_at']);
        $this->assertEquals($batch->id, $found['id']);
        $this->assertEquals('sent', $found['status']);
    }

    public function test_kitchen_order_detail_includes_sent_to_kitchen_timestamp(): void
    {
        $sentTime = now()->subMinutes(12);

        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table->id,
            'order_number' => 'ORD-NTF-'.uniqid(),
            'customer_name' => 'Ibu Notif',
            'status' => OrderStatus::Submitted,
            'created_by' => $this->kitchenUser->id,
        ]);

        $batch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Sent,
            'sent_to_kitchen_at' => $sentTime,
        ]);

        OrderItem::create([
            'order_batch_id' => $batch->id,
            'menu_variant_id' => $this->variant->id,
            'menu_name_snapshot' => 'Kepiting Super Notif',
            'quantity' => 2,
            'sales_unit' => 'portion',
            'price_snapshot' => 120000,
            'subtotal' => 240000,
            'status' => OrderItemStatus::Waiting,
        ]);

        $token = $this->kitchenUser->createToken('kds-notif')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/kitchen/orders/{$batch->id}");

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.sent_to_kitchen_at'));
        $this->assertEquals('Ibu Notif', $response->json('data.customer_name'));
    }
}
