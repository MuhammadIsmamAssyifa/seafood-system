<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantSettingsTest extends TestCase
{
    public function test_can_fetch_restaurant_receipt_info(): void
    {
        $restaurant = Restaurant::firstOrCreate(
            ['code' => 'SBH'],
            [
                'name' => 'SEAFOOD RESTO POS',
                'phone' => '0812-3456-7890',
                'address' => 'Jl. Pelabuhan Ikan No. 88',
                'footer_message' => "*** TERIMA KASIH ***\nSelamat Menikmati Hidangan Kami",
                'status' => 'active',
            ]
        );

        $response = $this->getJson('/api/v1/restaurant-info');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'SEAFOOD RESTO POS')
            ->assertJsonPath('data.phone', '0812-3456-7890');
    }

    public function test_authenticated_user_can_update_receipt_settings(): void
    {
        $restaurant = Restaurant::first();
        $user = User::factory()->create([
            'restaurant_id' => $restaurant->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->patchJson('/api/v1/restaurant', [
            'name' => 'SEAFOOD RESTO CABANG UTAMA',
            'phone' => '0819-9999-8888',
            'address' => 'Jl. Dermaga Baru No. 12',
            'footer_message' => 'Terima kasih telah berkunjung!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'SEAFOOD RESTO CABANG UTAMA')
            ->assertJsonPath('data.phone', '0819-9999-8888')
            ->assertJsonPath('data.address', 'Jl. Dermaga Baru No. 12')
            ->assertJsonPath('data.footer_message', 'Terima kasih telah berkunjung!');

        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'name' => 'SEAFOOD RESTO CABANG UTAMA',
            'phone' => '0819-9999-8888',
        ]);
    }
}

