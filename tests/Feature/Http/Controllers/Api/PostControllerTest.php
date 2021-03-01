<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store()
    {
        $this->withoutExceptionHandling();
        $response = $this->json('POST', '/api/posts', [
            'title' => 'test post',

        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => 'test post'])
            ->assertStatus(201);

        $this->assertDatabaseHas('posts', ['title' => 'test post']);
    }

    public function test_validate_title()
    {
        $response = $this->json('POST', '/api/posts', [
            'title' => '',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    public function test_show()
    {
        $this->withoutExceptionHandling();
        $post = Post::factory()->create();

        $response = $this->json('GET', "/api/posts/$post->id");

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => $post->title])
            ->assertStatus(200);
    }

    public function test_404_show()
    {
        $response = $this->json('GET','/api/posts/1000');
        $response->assertStatus(404);
    }

}
