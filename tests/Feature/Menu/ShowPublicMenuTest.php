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

class ShowPublicMenuTest extends TestCase
{

    use RefreshDatabase;


    protected Collection $plates;
    protected Menu $menu;



    public function test_an_authenticated_user_can_see_a_menu(): void
    {
        $this->withoutExceptionHandling();

        $response = $this->getJson(route('public.menu.show', $this->menu));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [ 'menu' => [
                'id', 'name', 'description', 'plates' => [
                    '*' => ['name', 'description', 'price']
                ]
            ]],
            'message', 'errors', 'status'
        ]);


        $response->assertJsonPath('data.menu.name', $this->menu->name);
        $response->assertJsonPath('data.menu.description', $this->menu->description);

        $firstPlate = $this->plates->first();

        $response->assertJsonPath('data.menu.plates.0', [
            'name' =>$firstPlate->name,
            'description' =>$firstPlate->description,
            'price' =>(string)$firstPlate->price,
        ]);



        $response->assertJsonCount(15, 'data.menu.plates');
    }




    protected function setUp(): void
    {
        parent::setUp();

        $restaurant = Restaurant::factory()->create();
        $this->plates = Plate::factory()->count(15)->create([
            'restaurant_id' =>$restaurant,
        ]);
        $this->menu = Menu::factory()
                            ->hasAttached($this->plates)
                            ->create([
            'restaurant_id' => $restaurant,
        ]);
    }
}
