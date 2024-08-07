<?php

namespace Tests\Feature\Menu;

use App\Jobs\GenerateQrJob;
use App\Models\Menu;
use App\Models\Plate;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateMenuTest extends TestCase
{

    use RefreshDatabase;
    protected User $user;
    protected Restaurant $restaurant;
    protected Collection $plates;
    /**
     * A basic feature test example.
     */
    public function test_an_authenticated_user_can_create_a_menu(): void
    {
        $this->withoutExceptionHandling();
        Queue::fake();
        $data = [
            'name' => 'menu name',
            'description' => 'menu description',
            'plate_ids' => $this->plates->pluck('id'),
        ];
        $response = $this->apiAs($this->user, 'post', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus", $data);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [ 'menu' => [
                'id', 'name', 'description', 'plates' => [
                    '*' => ['name', 'description', 'price']
                ]
            ]],
            'message', 'errors', 'status'
        ]);

        $firstPlate = $this->plates->first();

        $response->assertJsonPath('data.menu.plates.0', [
            'name' =>$firstPlate->name,
            'description' =>$firstPlate->description,
            'price' =>(string)$firstPlate->price,
        ]);

        $this->assertDatabaseHas('menus', [
            'restaurant_id' => $this->restaurant->id,
            'name' => 'menu name',
            'description' => 'menu description'
        ]);

        foreach ($this->plates as $plate) {
            $this->assertDatabaseHas('menus_plates', [
                'menu_id' => 1,
                'plate_id' => $this->restaurant->id
            ]);
        }

        Queue::assertPushed(GenerateQrJob::class, 1);
    }

    public function test_menu_plates_should_not_be_duplicates(): void
    {
        $this->withoutExceptionHandling();
        $data = [
            'name' => 'menu name',
            'description' => 'menu description',
            'plate_ids' => [$this->plates->first()->id, $this->plates->first()->id],
        ];
        $response = $this->apiAs($this->user, 'post', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus", $data);

        $response->assertStatus(200);


        $this->assertDatabaseCount('menus_plates', 1);
        $this->assertTrue(Menu::first()->plates()->count() == 1);
    }


    public function test_a_unauthenticated_user_cannot_create_a_menu(): void
    {
        //$this->withoutExceptionHandling();
        $data = [
            'name' => 'new menu name',
            'description' => 'new menu description',
            'plate_ids' => $this->plates->pluck('id'),
        ];
        $response = $this->postJson("{$this->apiBase}/restaurants/{$this->restaurant->id}/menus", $data);

        $response->assertStatus(401);

    }

    public function test_menu_name_is_required()
    {
        $data = [
            'name'=> '',
            'description' => 'Description test',
            'plate_ids' => $this->plates->pluck('id'),
        ];

        $response = $this->apiAs(User::find(1), 'post', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus", $data);

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

        $response = $this->apiAs(User::find(1), 'post', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus", $data);

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

        $response = $this->apiAs(User::find(1), 'post', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus", $data);

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

        $response = $this->apiAs(User::find(1), 'post', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus", $data);

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
        $response = $this->apiAs($user, 'post', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus", $data);

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

        $response = $this->apiAs($this->user, 'post', "{$this->apiBase}/restaurants/{$this->restaurant->id}/menus", $data);

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
    }
}
