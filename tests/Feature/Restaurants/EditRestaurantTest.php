<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EditRestaurantTest extends TestCase
{

    use RefreshDatabase;



    public function test_an_authenticated_user_can_edit_a_restaurant(): void
    {
        $this->withoutExceptionHandling();
        # teniendo

        $data = [
            'name' => 'New restaurant',
            'description' => 'New restaurant description',
        ];

        #haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}", $data);

        //dd(User::all());

        #esperando
        $response->assertStatus(200);

        $response->assertJsonStructure(['message', 'data' => [
            'restaurant' => ['id', 'name', 'slug', 'description']
        ], 'errors', 'status']);

        $this->assertDatabaseCount('restaurants', 1);
        $restaurant = Restaurant::first();
        $this->assertStringContainsString('new-restaurant', $restaurant->slug);
        $this->assertDatabaseMissing('restaurants', [
            'name' => 'Restaurant',
            'description' => 'Restaurant description',
        ]);
    }


    public function test_the_slug_must_not_change_if_the_name_is_the_same(): void
    {
        $this->withoutExceptionHandling();
        # teniendo

        $data = [
            'name' => 'Restaurant',
            'description' => 'New restaurant description',
        ];

        #haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}", $data);

        //dd(User::all());

        #esperando
        $response->assertStatus(200);

        $response->assertJsonStructure(['message', 'data' => [
            'restaurant' => ['id', 'name', 'slug', 'description']
        ], 'errors', 'status']);

        $this->assertDatabaseCount('restaurants', 1);
        $restaurant = Restaurant::find(1);
        $this->assertTrue($restaurant->slug === $this->restaurant->slug);
        $this->assertDatabaseMissing('restaurants', [
            'name' => 'Restaurant',
            'description' => 'Restaurant description',
        ]);
    }

    public function test_a_unauthenticated_user_can_edit_a_restaurant(): void
    {
        // $this->withoutExceptionHandling();
        # teniendo

        $data = [
            'name' => 'New restaurant',
            'description' => 'New restaurant description',
        ];

        #haciendo
        $response = $this->putJson("{$this->apiBase}/restaurants/{$this->restaurant->id}", $data);

        //dd(User::all());

        #esperando
        $response->assertStatus(401);
    }

    public function test_a_user_should_only_update_their_restaurants(): void
    {
        // $this->withoutExceptionHandling();
        # teniendo
        $restaurant = Restaurant::factory()->create();
        $data = [
            'name' => 'New restaurant',
            'description' => 'New restaurant description',
        ];

        #haciendo
        $response = $this->apiAs(User::find(1), 'put',"{$this->apiBase}/restaurants/{$restaurant->id}", $data);

        //dd(User::all());

        #esperando
        $response->assertStatus(403);
    }

    public function test_name_must_be_required(): void
    {
        # teniendo
        $data = [
            'name' => '',
            'description' => 'New restaurant description',
        ];

        #haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}", $data);

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
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['description']]);

    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);
        $this->restaurant = Restaurant::factory()->create([
            'user_id' => 1,
            'name' => 'Restaurant',
            'slug' => 'restaurant',
            'description' => 'Restaurant description',
        ]);
    }

}
