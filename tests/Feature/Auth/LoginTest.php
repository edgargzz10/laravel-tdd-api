<?php

namespace Tests\Feature;

use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;





    public function test_an_existing_user_can_login(): void
    {
        //$this->withoutExceptionHandling();
        # teniendo
        $credentials = ['email' => 'example@example.com', 'password' => 'password'];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/login", $credentials);



        # Espero que haga
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['token']]);
    }



    public function test_a_non_existing_user_cannot_login(): void
    {
        # teniendo
        $credentials = ['email' => 'example@noneexist.com', 'password' => 'password'];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/login", $credentials);

        # Espero que haga
        $response->assertStatus(401);
        $response->assertJsonFragment(['status' => 401, 'message' => 'Unauthorized']);
    }



    public function test_email_must_be_required(): void
    {
        # teniendo
        $credentials = ['password' => 'password'];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/login", $credentials);


        # Espero que haga

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['email']]);
        $response->assertJsonFragment(['errors' => ['email' => ['The email field is required.']]]);
    }


    public function test_email_must_be_valid_email(): void
    {
        # teniendo
        $credentials = ['email' => 'sdfsdfsdfds','password' => 'password'];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/login", $credentials);
        // $response->dd();

        # Espero que haga

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['email']]);
        $response->assertJsonFragment(['errors' => ['email' => ['The email field must be a valid email address.']]]);
    }


    public function test_email_must_be_a_string(): void
    {
        # teniendo
        $credentials = ['email' => 123123132456,'password' => 'password'];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/login", $credentials);
        //$response->dd();

        # Espero que haga

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['email']]);
        $response->assertJsonFragment(['errors' => ['email' => [
            'The email field must be a string.',
            'The email field must be a valid email address.']]]);
    }



    public function test_password_must_be_required(): void
    {
        # teniendo
        $credentials = ['email' => 'example@example.com'];

        # haciendo
        $response = $this->postJson('/api/v1/login', $credentials);

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['password']]);
        $response->assertJsonFragment(['errors' => ['password' => ['The password field is required.']]]);
    }



    public function test_password_must_have_at_lease_8_characters(): void
    {
        # teniendo
        $credentials = ['email' => 'example@example.com', 'password' => 'fds'];

        # haciendo
        $response = $this->postJson('/api/v1/login', $credentials);
        //$response->dd();

        # Espero que haga
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['password']]);
        $response->assertJsonFragment(['errors' => ['password' => ['The password field must be at least 8 characters.']]]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);
    }

}
