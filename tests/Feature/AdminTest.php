<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;
    protected Restaurant $restaurant;

    public function test_admin_user_can_delete_any_restaurant(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN->value);
        $response = $this->apiAs($admin, 'delete', "{$this->apiBase}/restaurants/{$this->restaurant->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('restaurants', [
            'id' => $this->restaurant->id,
        ]);
        // $response->assertJsonCount(0, 'data.restaurants');

    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->restaurant = Restaurant::factory()->create();
    }
}
