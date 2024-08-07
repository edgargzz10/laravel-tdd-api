<?php

namespace Tests\Feature\User;

use App\Enums\Roles;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;
    protected User $admin;


    public function test_administratror_can_delte_any_user()
    {
        $response = $this->apiAs($this->admin, 'delete', route('users.destroy', $this->user));
        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }

    public function test_a_normal_user_cannot_delete_any_user()
    {
        $response = $this->apiAs($this->user, 'delete', route('users.destroy', $this->admin));
        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Roles::ADMIN->value);

        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::USER->value);
    }
}
