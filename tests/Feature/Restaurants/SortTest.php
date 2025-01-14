<?php

namespace Tests\Feature\Restaurants;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SortTest extends TestCase
{
    Use RefreshDatabase;
    protected User $user;

    public function test_can_sort_by_name_ascending(): void
    {
        $data = [
            'sortBy' => 'name',
            'sortDirection' => 'asc',
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.index'), $data);

        $response->assertStatus(200);
        $response->assertJsonPath('data.restaurants.0.name', 'A name');
        $response->assertJsonPath('data.restaurants.1.name', 'B name');
        $response->assertJsonPath('data.restaurants.2.name', 'C name');
        $response->assertJsonPath('data.restaurants.3.name', 'D name');
    }

    public function test_can_sort_by_name_descending(): void
    {
        $data = [
            'sortBy' => 'name',
            'sortDirection' => 'desc',
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.index'), $data);

        $response->assertStatus(200);
        $response->assertJsonPath('data.restaurants.0.name', 'D name');
        $response->assertJsonPath('data.restaurants.1.name', 'C name');
        $response->assertJsonPath('data.restaurants.2.name', 'B name');
        $response->assertJsonPath('data.restaurants.3.name', 'A name');
    }

    public function test_can_sort_by_description_ascending(): void
    {
        $data = [
            'sortBy' => 'description',
            'sortDirection' => 'asc',
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.index'), $data);

        $response->assertStatus(200);
        $response->assertJsonPath('data.restaurants.0.description', 'A description');
        $response->assertJsonPath('data.restaurants.1.description', 'B description');
        $response->assertJsonPath('data.restaurants.2.description', 'C description');
        $response->assertJsonPath('data.restaurants.3.description', 'D description');
    }

    public function test_can_sort_by_description_descending(): void
    {
        $data = [
            'sortBy' => 'description',
            'sortDirection' => 'desc',
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.index'), $data);

        $response->assertStatus(200);
        $response->assertJsonPath('data.restaurants.0.description', 'D description');
        $response->assertJsonPath('data.restaurants.1.description', 'C description');
        $response->assertJsonPath('data.restaurants.2.description', 'B description');
        $response->assertJsonPath('data.restaurants.3.description', 'A description');
    }


    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        Restaurant::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'C name',
            'description' => 'B description'
        ]);

        Restaurant::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'B name',
            'description' => 'C description'
        ]);

        Restaurant::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'A name',
            'description' => 'D description'
        ]);

        Restaurant::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'D name',
            'description' => 'A description'
        ]);
    }
}
