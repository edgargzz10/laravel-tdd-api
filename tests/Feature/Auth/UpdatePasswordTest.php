<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{

    use RefreshDatabase;

    public function test_an_authenticated_user_can_update_their_password(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo

        $data = [
            'old_password' => 'password',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ];

        #haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/password", $data);

        //dd(User::all());

        #esperando
        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data', 'errors', 'status']);
        $user = User::find(1);
        $this->assertTrue(Hash::check('newpassword', $user->password));
    }


    public function test_old_password_must_be_validated(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo

        $data = [
            'old_password' => 'wrongpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ];

        #haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/password", $data);

        //dd(User::all());

        #esperando
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['old_password']]);
        $response->assertJsonFragment(['errors' => ['old_password' => [
            'The password does not match.'
        ]]]);
    }


    public function test_old_password_must_be_required(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'old_password' => '',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ];

        # haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/password", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['old_password']]);

    }



    public function test_password_must_be_required(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'old_password' => 'password',
            'password' => '',
            'password_confirmation' => 'newpassword',
        ];

        # haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/password", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['password']]);

    }



    public function test_password_must_be_confirmed(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'old_password' => 'password',
            'password' => 'newpassword',
            'password_confirmation' => '',
        ];

        # haciendo
        $response = $this->apiAs(User::find(1), 'put', "{$this->apiBase}/password", $data);
        //$response->dd();

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['password']]);
        $response->assertJsonFragment(['errors' => ['password' => [
            'The password field confirmation does not match.'
        ]]]);

    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);
    }

}
