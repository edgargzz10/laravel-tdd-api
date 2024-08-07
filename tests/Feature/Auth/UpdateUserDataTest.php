<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdateUserDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_modify_their_data(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo

        $data = [
            'name' => 'newname',
            'last_name' => 'new lastname',
        ];

        #haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/profile", $data);

        //dd(User::all());

        #esperando
        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data', 'errors', 'status']);
        $response->assertJsonFragment([
            'message' => 'OK', 'data' =>[
                'user' => [
                    'id' => 1,
                    'email' => 'example@example.com',
                    'name' => 'newname',
                    'last_name' => 'new lastname',
                    'roles'     => [Roles::USER->value],
                ]
            ],'status' => 200
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'example@example.com',
            'name' => 'User',
            'last_name' => 'Test',
        ]);
    }



    public function test_an_authenticated_user_cannot_modify_their_email(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo

        $data = [
            'email' => "newmail@example.com",
            'name' => 'newname',
            'last_name' => 'new lastname',
        ];

        #haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/profile", $data);

        //dd(User::all());

        #esperando
        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data', 'errors', 'status']);
        $response->assertJsonFragment([
            'message' => 'OK', 'data' =>[
                'user' => [
                    'id' => 1,
                    'email' => 'example@example.com',
                    'name' => 'newname',
                    'last_name' => 'new lastname',
                    'roles'     => [Roles::USER->value],
                ]
            ],'status' => 200
        ]);
    }


    public function test_an_authenticated_user_cannot_modify_their_password(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo

        $data = [
            'password' => "newpassword",
            'name' => 'newname',
            'last_name' => 'new lastname',
        ];

        #haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/profile", $data);

        //dd(User::all());

        #esperando
        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data', 'errors', 'status']);
        $user = User::find(1);
        $this->assertFalse(Hash::check('newpassword', $user->password));
    }



    public function test_name_must_be_required(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'name' => '',
            'last_name' => 'example example',
        ];

        # haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/profile", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['name']]);

    }


    public function test_name_must_have_at_lease_2_characters(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'name' => 'e',
            'last_name' => 'example example',
        ];

        # haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/profile", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['name']]);

    }


    public function test_last_name_must_be_required(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'name' => 'example',
            'last_name' => '',
        ];

        # haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/profile", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['last_name']]);

    }


    public function test_last_name_must_have_at_lease_2_characters(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'name' => 'example',
            'last_name' => 'e',
        ];

        # haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/profile", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['last_name']]);

    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->seed(UserSeeder::class);
    }
}
