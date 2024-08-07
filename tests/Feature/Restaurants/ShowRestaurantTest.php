<?php

namespace Tests\Feature\Restaurants;

use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowRestaurantTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;

    public function test_an_authenticated_user_must_see_one_of_their_restaurants(): void
    {
        $response = $this->apiAs(User::find(1), 'get', "{$this->apiBase}/restaurants/{$this->restaurant->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['restaurant' => ['id', 'name', 'description', 'slug', 'links']],
            'message', 'status', 'errors'
        ]);

        $response->assertJsonFragment([
            'data' => [
                'restaurant' => [
                    'id' => $this->restaurant->id,
                    'name' => $this->restaurant->name,
                    'description' => $this->restaurant->description,
                    'slug' => $this->restaurant->slug,
                    'links' => [
                        'plates' => route('restaurants.plates.index', $this->restaurant),
                        'menus' => route('restaurants.menus.index', $this->restaurant)
                    ]
                ]
            ],
        ]);

    }

    public function test_an_authenticated_user_must_see_only_their_restaurants(): void
    {
        $user = User::factory()->create();
        $response = $this->apiAs($user, 'get', "{$this->apiBase}/restaurants/{$this->restaurant->id}");

        $response->assertStatus(403);

    }

    public function test_a_unauthenticated_user_cannot_see_any_restaurant(): void
    {
        // $this->withoutExceptionHandling();
        # teniendo



        #haciendo
        $response = $this->getJson("{$this->apiBase}/restaurants/{$this->restaurant->id}");

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
