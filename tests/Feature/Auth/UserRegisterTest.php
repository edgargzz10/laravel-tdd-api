<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register(): void
    {
        // $this->withoutExceptionHandling();
        # teniendo

        $data = [
            'email' => 'email@email.com',
            'password' => 'password',
            'name' => 'example',
            'last_name' => 'example example',
        ];

        #haciendo
        $response = $this->postJson("{$this->apiBase}/users", $data);

        //dd(User::all());

        #esperando
        $response->assertStatus(200);

        $response->assertJsonStructure(['message', 'data', 'errors', 'status']);

        $response->assertJsonFragment([
            'message' => 'OK', 'data' =>
            ['user' => [
                'id' => 1,
                'email' => 'email@email.com',
                'name' => 'example',
                'last_name' => 'example example',
                'roles' => [Roles::USER->value]
        ]],'status' => 200]);

        $this->assertDatabaseCount('users', 1);

        $this->assertDatabaseHas('users', [
            'email' => 'email@email.com',
            'name' => 'example',
            'last_name' => 'example example',
        ]);
    }


    public function test_a_registered_user_can_login(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'email' => 'email@email.com',
            'password' => 'password',
            'name' => 'example',
            'last_name' => 'example example',
        ];

        # haciendo
        $this->postJson("{$this->apiBase}/users", $data);
        $response = $this->postJson("{$this->apiBase}/login", ['email' => 'email@email.com',
                                                            'password' => 'password']);

        # Espero que haga
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['token']]);


    }



    public function test_email_must_be_required(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'email' => '',
            'password' => 'password',
            'name' => 'example',
            'last_name' => 'example example',
        ];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/users", $data);



        # Espero que haga

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['email']]);
        $response->assertJsonFragment(['errors' => ['email' => ['The email field is required.']]]);
    }


    public function test_email_must_be_valid_email(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'email' => 'gjkgkjhhk',
            'password' => 'password',
            'name' => 'example',
            'last_name' => 'example example',
        ];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/users", $data);

        # Espero que haga

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['email']]);
        $response->assertJsonFragment(['errors' => ['email' => ['The email field must be a valid email address.']]]);
    }


    public function test_email_must_be_unique(): void
    {
        //$this->withoutExceptionHandling();
        User::factory()->create(['email' => 'email@email.com']);
        # teniendo
        $data = [
            'email' => 'email@email.com',
            'password' => 'password',
            'name' => 'example',
            'last_name' => 'example example',
        ];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/users", $data);

        # Espero que haga

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['email']]);
        $response->assertJsonFragment(['errors' => ['email' => ['The email has already been taken.']]]);
    }


    public function test_password_must_be_required(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'email' => 'email@email.com',
            'password' => '',
            'name' => 'example',
            'last_name' => 'example example',
        ];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/users", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['password']]);
        $response->assertJsonFragment(['errors' => ['password' => ['The password field is required.']]]);
    }



    public function test_password_must_have_at_lease_8_characters(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'email' => 'email@email.com',
            'password' => 'abcd',
            'name' => 'example',
            'last_name' => 'example example',
        ];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/users", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['password']]);
        $response->assertJsonFragment(['errors' => ['password' => ['The password field must be at least 8 characters.']]]);
    }


    public function test_name_must_be_required(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'email' => 'email@email.com',
            'password' => 'password',
            'name' => '',
            'last_name' => 'example example',
        ];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/users", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['name']]);

    }


    public function test_name_must_have_at_lease_2_characters(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'email' => 'email@email.com',
            'password' => 'password',
            'name' => 'e',
            'last_name' => 'example example',
        ];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/users", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['name']]);

    }


    public function test_last_name_must_be_required(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'email' => 'email@email.com',
            'password' => 'password',
            'name' => 'example',
            'last_name' => '',
        ];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/users", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['last_name']]);

    }


    public function test_last_name_must_have_at_lease_2_characters(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $data = [
            'email' => 'email@email.com',
            'password' => 'password',
            'name' => 'example',
            'last_name' => 'e',
        ];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/users", $data);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['last_name']]);

    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }
}
