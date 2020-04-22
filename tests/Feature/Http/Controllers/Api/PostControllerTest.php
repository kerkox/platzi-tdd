<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Post;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostControllerTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_post_store()
    {
//        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $response = $this->actingAs($user, 'api')->json('POST','/api/posts', [
            'title' => 'El post de prueba'
            ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
        ->assertJson(['title' => 'El post de prueba'])
        ->assertStatus(201); //Ok, creado un recurso

        $this->assertDatabaseHas('posts', ['title' => 'El post de prueba']);
    }

    public function test_post_validate_title()
    {
//        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $response = $this->actingAs($user,'api')->json('POST','/api/posts', [
            'title' => ''
        ]);
        //Estatus HTTP
        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    public function test_post_show()
    {
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();

        $response = $this->actingAs($user,'api')->json('GET',"/api/posts/$post->id");

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => $post->title])
            ->assertStatus(200); //Ok
    }
    public function test_post_404_show()
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user,'api')->json('GET',"/api/posts/1000");

        $response->assertStatus(404); //Ok
    }

    public function test_post_update()
    {
//        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();
        $title = "nuevo";
        $response = $this->actingAs($user,'api')->json('PUT',"/api/posts/$post->id", [
            'title' => $title
        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => $title])
            ->assertStatus(200); //Ok

        $this->assertDatabaseHas('posts', ['title' => $title]);
    }

    public function test_post_delete()
    {
//        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();
        $response = $this->actingAs($user,'api')->json('DELETE',"/api/posts/$post->id");

        $response->assertSee(null)
            ->assertStatus(204); //Sin contenido

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_post_index()
    {
        $user = factory(User::class)->create();
        factory(Post::class, 5)->create();
        $response = $this->actingAs($user,'api')->json('GET', '/api/posts');

        $response->assertJsonStructure([
           'data' => [
               '*' => ['id', 'title', 'created_at', 'updated_at']
           ]
        ])->assertStatus(200);
    }

    public function test_post_guest()
    {
        $this->json('GET', '/api/posts')->assertStatus(401);
        $this->json('POST', '/api/posts')->assertStatus(401);
        $this->json('GET', '/api/posts/1000')->assertStatus(401);
        $this->json('PUT', '/api/posts/1000')->assertStatus(401);
        $this->json('DELETE', '/api/posts/1000')->assertStatus(401);
    }
}
