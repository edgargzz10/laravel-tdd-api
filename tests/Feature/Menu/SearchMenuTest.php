<?php

namespace Tests\Feature\Restaurants;

use App\Models\Menu;
use App\Models\Plate;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SearchMenuTest extends TestCase
{

    use RefreshDatabase;

    protected User $user;
    protected Restaurant $restaurant;
    protected Menu $menu;

    public function test_an_authenticated_user_can_search_menus(): void
    {
        $this->withoutExceptionHandling();
        $data = [
            'search' => 'name two'
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.menus.index', $this->restaurant), $data);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.menus');
        $response->assertJsonPath('data.menus.0.id', $this->menu->id);
        $response->assertJsonPath('data.menus.0.name',  $this->menu->name);
    }


    public function test_an_authenticated_user_can_search_menus_by_description(): void
    {
        $data = [
            'search' => 'description two'
        ];

        $response = $this->apiAs($this->user, 'get', route('restaurants.menus.index', $this->restaurant), $data);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.menus');
        $response->assertJsonPath('data.menus.0.id', $this->menu->id);
        $response->assertJsonPath('data.menus.0.description',  $this->menu->description);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->restaurant = Restaurant::factory()->create(['user_id' => $this->user]);
        Menu::factory()->create([
            'restaurant_id' => $this->restaurant,
            'name' => 'name one'
        ]);

        $this->menu = Menu::factory()->create([
            'restaurant_id' => $this->restaurant,
            'name' => 'name two',
            'description' => 'description two'
        ]);

        Menu::factory()->create([
            'restaurant_id' => $this->restaurant,
            'name' => 'name three'
        ]);
    }
}
