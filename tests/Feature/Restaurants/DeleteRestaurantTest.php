<?php

namespace Tests\Feature\Restaurants;

use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteRestaurantTest extends TestCase
{

    use RefreshDatabase;

    public function test_an_authenticated_user_must_delete_their_restaurants(): void
    {
        $response = $this->apiAs(User::find(1), 'delete', "{$this->apiBase}/restaurants/{$this->restaurant->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'OK']);
        $this->assertDatabaseCount('restaurants', 0);

    }

    public function test_an_authenticated_user_must_delete_only_their_restaurants(): void
    {
        $user = User::factory()->create();
        $response = $this->apiAs($user, 'delete', "{$this->apiBase}/restaurants/{$this->restaurant->id}");

        $response->assertStatus(403);
        // $response->assertJsonCount(0, 'data.restaurants');

    }

    public function test_a_unauthenticated_user_can_delete_a_restaurant(): void
    {
        // $this->withoutExceptionHandling();
        # teniendo



        #haciendo
        $response = $this->deleteJson("{$this->apiBase}/restaurants/{$this->restaurant->id}");

        //dd(User::all());

        #esperando
        $response->assertStatus(401);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);
        $this->restaurant = Restaurant::factory()->create([
            'user_id' => 1,

        ]);
    }
}
