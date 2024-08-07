<?php

namespace Tests\Feature\Menu;

use App\Models\Menu;
use App\Models\Plate;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowMenuTest extends TestCase
{

    use RefreshDatabase;
    protected User $user;
    protected Restaurant $restaurant;
    protected Collection $plates;
    protected Menu $menu;



    public function test_an_authenticated_user_can_see_a_menu(): void
    {
        $this->withoutExceptionHandling();

        $response = $this->apiAs($this->user, 'get', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus/{$this->menu->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [ 'menu' => [
                'id', 'name', 'description', 'links', 'plates' => [
                    '*' => ['name', 'description', 'price']
                ]
            ]],
            'message', 'errors', 'status'
        ]);


        $response->assertJsonPath('data.menu.links.parent', route('restaurants.show', $this->restaurant));
        $response->assertJsonPath('data.menu.links.public', route('public.menu.show', $this->menu));
        $response->assertJsonPath('data.menu.links.qr', $this->menu->qr);
        $response->assertJsonPath('data.menu.name', $this->menu->name);
        $response->assertJsonPath('data.menu.description', $this->menu->description);

        $firstPlate = $this->plates->first();

        $response->assertJsonPath('data.menu.plates.0', [
            'name' =>$firstPlate->name,
            'description' =>$firstPlate->description,
            'price' =>(string)$firstPlate->price,
        ]);



        $response->assertJsonCount(15, 'data.menu.plates');

        foreach ($this->plates as $plate) {
            $this->assertDatabaseHas('menus_plates', [
                'menu_id' => 1,
                'plate_id' => $this->restaurant->id
            ]);
        }
    }

    public function test_a_unauthenticated_user_cannot_see_any_menu(): void
    {
        //$this->withoutExceptionHandling();

        $response = $this->getJson("{$this->apiBase}/restaurants/{$this->restaurant->id}/menus/{$this->menu->id}");

        $response->assertStatus(401);

    }


    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->restaurant = Restaurant::factory()->create(['user_id' => $this->user->id]);
        $this->plates = Plate::factory()->count(15)->create([
            'restaurant_id' => $this->restaurant->id,
        ]);
        $this->menu = Menu::factory()
                            ->hasAttached($this->plates)
                            ->create([
            'restaurant_id' => $this->restaurant->id,
        ]);
    }
}
