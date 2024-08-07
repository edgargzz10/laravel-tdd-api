<?php

namespace Tests\Feature\Menu;

use App\Models\Menu;
use App\Models\Plate;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class EditMenuTest extends TestCase
{

    use RefreshDatabase;
    protected User $user;
    protected Restaurant $restaurant;
    protected Collection $plates;
    protected Menu $menu;
    /**
     * A basic feature test example.
     */
    public function test_an_authenticated_user_can_edit_a_menu(): void
    {
        $this->withoutExceptionHandling();
        $data = [
            'name' => 'new menu name',
            'description' => 'new menu description',
            'plate_ids' => $this->plates->pluck('id'),
        ];
        $response = $this->apiAs($this->user, 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus/{$this->menu->id}", $data);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [ 'menu' => [
                'id', 'name', 'description', 'plates' => [
                    '*' => ['name', 'description', 'price']
                ]
            ]],
            'message', 'errors', 'status'
        ]);


        $response->assertJsonPath('data.menu.name', 'new menu name');
        $response->assertJsonPath('data.menu.description', 'new menu description');

        $firstPlate = $this->plates->first();

        $response->assertJsonPath('data.menu.plates.0', [
            'name' =>$firstPlate->name,
            'description' =>$firstPlate->description,
            'price' =>(string)$firstPlate->price,
        ]);

        $this->assertDatabaseMissing('menus', [
            'restaurant_id' => $this->restaurant->id,
            'name' => $this->restaurant->name,
            'description' => $this->restaurant->description
        ]);

        $response->assertJsonCount(15, 'data.menu.plates');

        foreach ($this->plates as $plate) {
            $this->assertDatabaseHas('menus_plates', [
                'menu_id' => 1,
                'plate_id' => $this->restaurant->id
            ]);
        }
    }

    public function test_a_unauthenticated_user_cannot_edit_a_menu(): void
    {
        //$this->withoutExceptionHandling();
        $data = [
            'name' => 'new menu name',
            'description' => 'new menu description',
            'plate_ids' => $this->plates->pluck('id'),
        ];
        $response = $this->putJson("{$this->apiBase}/restaurants/{$this->restaurant->id}/menus/{$this->menu->id}", $data);

        $response->assertStatus(401);

    }

    public function test_menu_plates_should_not_be_duplicates(): void
    {
        $this->withoutExceptionHandling();
        $data = [
            'name' => 'new menu name',
            'description' => 'new menu description',
            'plate_ids' => [$this->plates->first()->id, $this->plates->first()->id],
        ];
        $response = $this->apiAs($this->user, 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus/{$this->menu->id}", $data);



        $this->assertDatabaseCount('menus_plates', 1);
        $this->assertTrue(Menu::first()->plates()->count() == 1);
    }

    public function test_a_user_can_add_a_new_menu_plate(): void
    {
        $this->withoutExceptionHandling();
        $plate =Plate::factory()->create(['restaurant_id' => $this->restaurant]);
        $data = [
            'name' => 'new menu name',
            'description' => 'new menu description',
            'plate_ids' => [...$this->plates->pluck('id'), $plate->id],
        ];
        $response = $this->apiAs($this->user, 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus/{$this->menu->id}", $data);

        $response->assertStatus(200);

        $response->assertJsonCount(16, 'data.menu.plates');

        $this->assertDatabaseHas('menus_plates', [
            'menu_id' => 1,
            'plate_id' => $this->restaurant->id
        ]);

        foreach ($this->plates as $plate) {
            $this->assertDatabaseHas('menus_plates', [
                'menu_id' => 1,
                'plate_id' => $this->restaurant->id
            ]);
        }
    }

    public function test_a_user_can_remove_a_new_menu_plate(): void
    {
        $this->withoutExceptionHandling();
        $plateIds = $this->plates->splice(0, 14)->pluck('id');
        $data = [
            'name' => 'new menu name',
            'description' => 'new menu description',
            'plate_ids' => $plateIds
        ];
        $response = $this->apiAs($this->user, 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus/{$this->menu->id}", $data);

        $response->assertStatus(200);

        $response->assertJsonCount(14, 'data.menu.plates');

        foreach ($plateIds as $plateId) {
            $this->assertDatabaseHas('menus_plates', [
                'menu_id' => 1,
                'plate_id' => $plateId
            ]);
        }

        $this->assertDatabaseMissing('menus_plates', [
            'menu_id' => 1,
            'plate_id' => $this->plates->last()->id,
        ]);
    }

    public function test_menu_name_is_required()
    {
        $data = [
            'name'=> '',
            'description' => 'Description test',
            'plate_ids' => $this->plates->pluck('id'),
        ];

        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus/{$this->menu->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_menu_description_is_required()
    {
        $data = [
            'name'=> 'Name test',
            'description' => '',
            'plate_ids' => $this->plates->pluck('id'),
        ];

        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus/{$this->menu->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['description']]);
    }

    public function test_menu_plates_is_required()
    {
        $data = [
            'name'=> 'Name test',
            'description' => 'Description test',
            'plate_ids' => [],
        ];

        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus/{$this->menu->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['plate_ids']]);
    }

    public function test_menu_must_exist()
    {
        $data = [
            'name'=> 'Name test',
            'description' => 'Description test',
            'plate_ids' => [100],
        ];

        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus/{$this->menu->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['plate_ids.0']]);
    }

    public function test_menu_plates_must_belongs_to_user()
    {
        $data = [
            'name'=> 'Name test',
            'description' => 'Description test',
            'plate_ids' => [1],
        ];

        $user = User::factory()->create();
        $response = $this->apiAs($user, 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus/{$this->menu->id}", $data);

        $response->assertStatus(403);
        // $response->assertJsonStructure(['errors' => ['plate_ids.0']]);
    }

    public function test_menu_plates_must_belongs_to_user_2()
    {
        $plate = Plate::factory()->create();
        $data = [
            'name'=> 'Name test',
            'description' => 'Description test',
            'plate_ids' => [$plate->id],
        ];

        $response = $this->apiAs($this->user, 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus/{$this->menu->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['plate_ids.0']]);
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
