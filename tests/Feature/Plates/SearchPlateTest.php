<?php

namespace Tests\Feature\Restaurants;

use App\Models\Plate;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SearchPlateTest extends TestCase
{

    use RefreshDatabase;

    protected User $user;
    protected Restaurant $restaurant;
    protected Plate $plate;

    public function test_an_authenticated_user_can_search_plates(): void
    {
        $data = [
            'search' => 'name two'
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.plates.index', $this->restaurant), $data);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.plates');
        $response->assertJsonPath('data.plates.0.id', $this->plate->id);
        $response->assertJsonPath('data.plates.0.name',  $this->plate->name);
    }


    public function test_an_authenticated_user_can_search_plates_by_description(): void
    {
        $data = [
            'search' => 'description two'
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.plates.index', $this->restaurant), $data);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.plates');
        $response->assertJsonPath('data.plates.0.id', $this->plate->id);
        $response->assertJsonPath('data.plates.0.description',  $this->plate->description);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->restaurant = Restaurant::factory()->create(['user_id' => $this->user]);
        Plate::factory()->create([
            'restaurant_id' => $this->restaurant,
            'name' => 'name one'
        ]);

        $this->plate = Plate::factory()->create([
            'restaurant_id' => $this->restaurant,
            'name' => 'name two',
            'description' => 'description two'
        ]);

        Plate::factory()->create([
            'restaurant_id' => $this->restaurant,
            'name' => 'name three'
        ]);
    }
}
