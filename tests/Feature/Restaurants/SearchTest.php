<?php

namespace Tests\Feature\Restaurants;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SearchTest extends TestCase
{

    use RefreshDatabase;

    protected User $user;
    protected Restaurant $restaurant;

    public function test_an_authenticated_user_can_search_restaurants(): void
    {
        $data = [
            'search' => 'name two'
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.index'), $data);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.restaurants');
        $response->assertJsonPath('data.restaurants.0.id', $this->restaurant->id);
        $response->assertJsonPath('data.restaurants.0.name',  $this->restaurant->name);
    }


    public function test_an_authenticated_user_can_search_restaurants_by_description(): void
    {
        $data = [
            'search' => 'description two'
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.index'), $data);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.restaurants');
        $response->assertJsonPath('data.restaurants.0.id', $this->restaurant->id);
        $response->assertJsonPath('data.restaurants.0.description',  $this->restaurant->description);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Restaurant::factory()->create([
            'user_id' => $this->user,
            'name' => 'name one'
        ]);

        $this->restaurant = Restaurant::factory()->create([
            'user_id' => $this->user,
            'name' => 'name two',
            'description' => 'description two'
        ]);

        Restaurant::factory()->create([
            'user_id' => $this->user,
            'name' => 'name three'
        ]);
    }
}
