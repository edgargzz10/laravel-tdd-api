<?php

namespace Tests\Feature\Restaurants;

use App\Models\Plate;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PlatesSortTest extends TestCase
{
    Use RefreshDatabase;
    protected User $user;
    protected Restaurant $restaurant;

    public function test_can_sort_plates_by_name_ascending(): void
    {
        $data = [
            'sortBy' => 'name',
            'sortDirection' => 'asc',
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.plates.index', $this->restaurant), $data);

        $response->assertStatus(200);
        $response->assertJsonPath('data.plates.0.name', 'A name');
        $response->assertJsonPath('data.plates.1.name', 'B name');
        $response->assertJsonPath('data.plates.2.name', 'C name');
        $response->assertJsonPath('data.plates.3.name', 'D name');
    }

    public function test_can_sort_plates_by_name_descending(): void
    {
        $data = [
            'sortBy' => 'name',
            'sortDirection' => 'desc',
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.plates.index', $this->restaurant), $data);

        $response->assertStatus(200);
        $response->assertJsonPath('data.plates.0.name', 'D name');
        $response->assertJsonPath('data.plates.1.name', 'C name');
        $response->assertJsonPath('data.plates.2.name', 'B name');
        $response->assertJsonPath('data.plates.3.name', 'A name');
    }

    public function test_can_sort_by_description_ascending(): void
    {
        $data = [
            'sortBy' => 'description',
            'sortDirection' => 'asc',
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.plates.index', $this->restaurant), $data);

        $response->assertStatus(200);
        $response->assertJsonPath('data.plates.0.description', 'A description');
        $response->assertJsonPath('data.plates.1.description', 'B description');
        $response->assertJsonPath('data.plates.2.description', 'C description');
        $response->assertJsonPath('data.plates.3.description', 'D description');
    }

    public function test_can_sort_by_description_descending(): void
    {
        $data = [
            'sortBy' => 'description',
            'sortDirection' => 'desc',
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.plates.index', $this->restaurant), $data);

        $response->assertStatus(200);
        $response->assertJsonPath('data.plates.0.description', 'D description');
        $response->assertJsonPath('data.plates.1.description', 'C description');
        $response->assertJsonPath('data.plates.2.description', 'B description');
        $response->assertJsonPath('data.plates.3.description', 'A description');
    }


    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->restaurant = Restaurant::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Plate::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'C name',
            'description' => 'B description'
        ]);

        Plate::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'B name',
            'description' => 'C description'
        ]);

        Plate::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'A name',
            'description' => 'D description'
        ]);

        Plate::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'D name',
            'description' => 'A description'
        ]);
    }
}
