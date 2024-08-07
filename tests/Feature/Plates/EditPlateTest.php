<?php

namespace Tests\Feature\Plates;

use App\Models\Plate;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EditPlateTest extends TestCase
{

    use RefreshDatabase;
    protected Restaurant $restaurant;
    protected Plate $plate;

    /**
     * A basic feature test example.
     */
    public function test_an_authenticated_user_can_edit_a_plate(): void
    {
        // $this->withoutExceptionHandling();
        $data = [
            'name'=> 'NEW Name test',
            'description' => 'NEW Description test',
            'price' => 'NEW $123',
            'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyAQMAAAAk8RryAAAAAXNSR0IB2cksfwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAANQTFRFAAAAp3o92gAAAA1JREFUGBljGAWDCgAAAZAAAcH2qj4AAAAASUVORK5CYII='
        ];

            $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/plates/{$this->plate->id}", $data);


            $response->assertStatus(200);
            $response->assertJsonStructure([
                'data' => ['plate' => ['id', 'restaurant_id', 'name', 'description', 'price', 'image']],
                'message', 'status', 'errors'
            ]);
            $plate = Plate::find(1);
            $response->assertJsonFragment([
                'data' => [
                    'plate' => [
                        ...$data,
                        'id' => $this->plate->id,
                        'restaurant_id' => $this->restaurant->id,
                        'links'         => ['parent' => route('restaurants.show', $this->restaurant)],
                        'image' => $plate->image,
                    ]
                ]
            ]);

            $this->assertDatabaseMissing('plates', [
                'name'=> 'Name test',
                'description' => 'Description test',
                'price' => '$123'
            ]);
    }

    public function test_a_unauthenticated_user_cannot_update_a_plate(): void
    {
        $data = [
            'name'=> 'New Name test',
            'description' => 'New Description test',
            'price' => 'New $123'
        ];

            $response = $this->putJson("{$this->apiBase}/restaurants/{$this->restaurant->id}/plates/{$this->plate->id}", $data);


            $response->assertStatus(401);
    }

    public function test_a_unauthenticated_user_can_only_update_their_plates(): void
    {
        $data = [
            'name'=> 'New Name test',
            'description' => 'New Description test',
            'price' => 'New $123'
        ];

        $user = User::factory()->create();
            $response = $this->apiAs($user, 'put',"{$this->apiBase}/restaurants/{$this->restaurant->id}/plates/{$this->plate->id}", $data);


            $response->assertStatus(403);
    }

    public function test_plate_name_is_required()
    {
        $data = [
            'name'=> '',
            'description' => 'New Description test',
            'price' => 'New $123'
        ];

        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/plates/{$this->plate->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_plate_description_is_required()
    {
        $data = [
            'name'=> 'New Name test',
            'description' => '',
            'price' => 'New $123'
        ];

        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/plates/{$this->plate->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['description']]);
    }



    public function test_plate_price_is_required()
    {
        $data = [
            'name'=> 'New Name test',
            'description' => 'New Description test',
            'price' => ''
        ];

        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/restaurants/{$this->restaurant->id}/plates/{$this->plate->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['price']]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);
        $this->restaurant = Restaurant::factory()->create([
            'user_id' => 1
        ]);

        $this->plate = Plate::factory()->create([
            'restaurant_id' => $this->restaurant->id
        ]);
    }
}
