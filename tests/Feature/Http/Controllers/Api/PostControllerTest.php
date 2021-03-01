<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store()
    {
        $user = User::factory()->create();
        $this->withoutExceptionHandling();
        $response = $this->actingAs($user, 'api')->json('POST', '/api/posts', [
            'title' => 'test post',

        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => 'test post'])
            ->assertStatus(201);

        $this->assertDatabaseHas('posts', ['title' => 'test post']);
    }

    public function test_validate_title()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->json('POST', '/api/posts', [
            'title' => '',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    public function test_show()
    {
        $user = User::factory()->create();
        $this->withoutExceptionHandling();
        $post = Post::factory()->create();

        $response = $this->actingAs($user, 'api')->json('GET', "/api/posts/$post->id");

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => $post->title])
            ->assertStatus(200);
    }

    public function test_404_show()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->json('GET', '/api/posts/1000');
        $response->assertStatus(404);
    }

    public function test_update()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $nuevo_titulo = 'nuevo titulo' . time();
        $response = $this->actingAs($user, 'api')->json('PUT', "/api/posts/$post->id", [
            'title' => $nuevo_titulo
        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => $nuevo_titulo])
            ->assertStatus(200);

        $this->assertDatabaseHas('posts', ['title' => $nuevo_titulo]);
    }

    public function test_404_update()
    {
        $user = User::factory()->create();
        $nuevo_titulo = 'nuevo titulo' . time();
        $response = $this->actingAs($user, 'api')->json('PUT', '/api/posts/1000', [
            'title' => $nuevo_titulo
        ]);
        $response->assertStatus(404);
    }

    public function test_delete()
    {
        $user = User::factory()->create();
        $this->withoutExceptionHandling();
        $post = Post::factory()->create();
        $response = $this->actingAs($user, 'api')->json('DELETE', "/api/posts/$post->id");

        $response->assertSee(null)
            ->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_index()
    {
        $user = User::factory()->create();
        $posts = Post::factory(5)->create();
        $response = $this->actingAs($user, 'api')->json('GET', '/api/posts');
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'created_at', 'updated_at']
            ]
        ])
            ->assertStatus(200);
    }

    public function test_guest()
    {
        $this->json('GET', '/api/posts')->assertStatus(401);
        $this->json('POST', '/api/posts')->assertStatus(401);
        $this->json('GET', '/api/posts/1000')->assertStatus(401);
        $this->json('PUT', '/api/posts/1000')->assertStatus(401);
        $this->json('DELETE', '/api/posts/1000')->assertStatus(401);
    }

}
