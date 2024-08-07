<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;
    protected $token = '';
    protected $email = '';





    public function test_an_existing_user_can_reset_their_password(): void
    {
        $this->sendResetPassword();


        $response = $this->putJson("{$this->apiBase}/reset-password?token={$this->token}", [
            'email' => $this->email,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword']);

            $response->assertStatus(200);
            $response->assertJsonStructure(['message', 'data', 'errors', 'status']);
            $user = User::find(1);
            $this->assertTrue(Hash::check('newpassword', $user->password));
    }





    public function test_email_must_be_required(): void
    {
        #teniendo
        $data = ['email' => ''];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/reset-password", $data);


        # Espero que haga

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['email']]);
        $response->assertJsonFragment(['errors' => ['email' => ['The email field is required.']]]);
    }


    public function test_email_must_be_valid_email(): void
    {
        #teniendo
        $data = ['email' => 'notemail'];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/reset-password", $data);
        // $response->dd();

        # Espero que haga

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['email']]);
        $response->assertJsonFragment(['errors' => ['email' => ['The email field must be a valid email address.']]]);
    }


    public function test_email_must_be_an_existing_email(): void
    {
        #teniendo
        $data = ['email' => 'noexist@mail.com'];

        # haciendo
        $response = $this->postJson("{$this->apiBase}/reset-password", $data);
        // $response->dd();

        # Espero que haga

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['email']]);
        $response->assertJsonFragment(['errors' => ['email' => ['The selected email is invalid.']]]);
    }


    public function test_email_must_be_associated_with_the_token(): void
    {
        $this->sendResetPassword();


        $response = $this->putJson("{$this->apiBase}/reset-password?token={$this->token}", [
            'email' => 'fake@email.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword']);

        $response->assertStatus(500);
        $response->assertJsonStructure(['message', 'data', 'status']);

        # Espero que haga

        $response->assertJsonFragment(['message' => 'Invalid Email']);
    }



    public function test_password_must_be_required(): void
    {
        $this->sendResetPassword();


        $response = $this->putJson("{$this->apiBase}/reset-password?token={$this->token}", [
            'email' => $this->email,
            'password' => '',
            'password_confirmation' => 'newpassword']);

            $response->assertStatus(422);
            $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['password']]);
    }



    public function test_password_must_be_confirmed(): void
    {
        $this->sendResetPassword();


        $response = $this->putJson("{$this->apiBase}/reset-password?token={$this->token}", [
            'email' => $this->email,
            'password' => 'newpassword',
            'password_confirmation' => '']);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data', 'status', 'errors' => ['password']]);

        # Espero que haga

        $response->assertJsonFragment(['errors' => ['password' => [
            'The password field confirmation does not match.'
        ]]]);

    }


    public function test_token_must_be_a_valid_token(): void
    {
        $this->sendResetPassword();


        $response = $this->putJson("{$this->apiBase}/reset-password?token={$this->token}adfsadfasd", [
            'email' => $this->email,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword']);

        $response->assertStatus(500);
        $response->assertJsonStructure(['message', 'data', 'status']);

        # Espero que haga

        $response->assertJsonFragment(['message' => 'Invalid Token']);

    }


    /**
    * @test
    */
    public function sendResetPassword()
    {
         //$this->withoutExceptionHandling();
         Notification::fake();
         # teniendo
         $data = ['email' => 'example@example.com'];

         # haciendo
         $response = $this->postJson("{$this->apiBase}/reset-password", $data);



         # Espero que haga
         $response->assertStatus(200);
         $response->assertJsonFragment(['message' => 'OK']);
         $user = User::find(1);
         Notification::assertSentTo([$user], function (ResetPasswordNotification $notification) {
             $url = $notification->url;
             $parts = parse_url($url);
             parse_str($parts['query'], $query);
             $this->token = $query['token'];
             $this->email = $query['email'];

             return str_contains($url, 'http://front.app/reset-password?token=');
         });
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->seed(UserSeeder::class);
    }

}
