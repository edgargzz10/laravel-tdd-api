<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NotFoundTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_not_found_error(): void
    {
        $response = $this->get("{$this->apiBase}/not-found");

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'data', 'message', 'status', 'errors'
        ]);
        $response->assertJsonPath('status', 404);
    }
}
