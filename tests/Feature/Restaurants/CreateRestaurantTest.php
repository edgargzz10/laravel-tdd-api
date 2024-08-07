<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateRestaurantTest extends TestCase
{

    use RefreshDatabase;




    public function test_a_user_can_create_a_restaurant(): void
    {
        $this->withoutExceptionHandling();
        # teniendo

        $data = [
            'name' => 'New restaurant',
            'description' => 'New restaurant description',
        ];

        #haciendo
        $response = $this->apiAs(User::find(1), 'post', "{$this->apiBase}/restaurants", $data);

        //dd(User::all());

        #esperando
        $response->assertStatus(200);

        $response->assertJsonStructure(['message', 'data' => [
            'restaurant' => ['id', 'name', 'slug', 'description']
        ], 'errors', 'status']);

        $this->assertDatabaseCount('restaurants', 1);
        $restaurant = Restaurant::first();
        $this->assertStringContainsString('new-restaurant', $restaurant->slug);
        $this->assertDatabaseHas('restaurants', [
            'id' => 1,
            'user_id' => 1,
            'name' => 'New restaurant',
            'description' => 'New restaurant description',
        ]);
    }

    public function test_a_unauthenticated_user_can_create_a_restaurant(): void
    {
        // $this->withoutExceptionHandling();
        # teniendo



        #haciendo
        $response = $this->postJson("{$this->apiBase}/restaurants");

        //dd(User::all());

        #esperando
        $response->assertStatus(401);
    }

    public function test_name_must_be_required(): void
    {
        # teniendo
        $data = [
            'name' => '',
            'description' => 'New restaurant description',
        ];

        #haciendo
        $response = $this->apiAs(User::find(1), 'post', "{$this->apiBase}/restaurants", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['name']]);

    }

    public function test_description_must_be_required(): void
    {
        # teniendo
        $data = [
            'name' => 'New restaurant',
            'description' => '',
        ];

        #haciendo
        $response = $this->apiAs(User::find(1), 'post', "{$this->apiBase}/restaurants", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['description']]);

    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);
    }
}
