<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Post;
use App\User;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store(){

        $user =factory(User::class)->create();

        //Manejador de errores
        //$this->withoutExceptionHandling();

        //Accedmos a la ruta, enviando los parametros
        $response = $this->actingAs($user, 'api')->json('POST', '/api/posts',[
            'title' => 'El post de prueba'
        ]);
        
        //se revisa la estructura, que los datos existan y status
        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => 'El post de prueba'])
            ->assertStatus(201); //OK, creado un recurso
        
        //Revisamos que la tabla de la base de datos contenga los datos
        $this->assertDatabaseHas('posts', ['title' => 'El post de prueba']); 
    }

    public function test_validate_title(){

        $user =factory(User::class)->create();
        $response = $this->actingAs($user, 'api')->json('POST', '/api/posts',[
            'title' => ''
        ]);
        
        //status http 422
        $response->assertStatus(422) 
            ->assertJsonValidationErrors('title'); 
    }

    public function test_show(){
        $user =factory(User::class)->create();
        $post = factory(Post::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "/api/posts/$post->id");

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
        ->assertJson(['title' => $post->title])
        ->assertStatus(200); //OK, creado un recurso
    }

    public function test_404_show(){

        $user =factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', '/api/posts/1000');

        $response->assertStatus(404); //OK
    }

    public function test_update()
    {
        $user =factory(User::class)->create();
        $post = factory(Post::class)->create();
        
        $response = $this->actingAs($user, 'api')->json('PUT', "/api/posts/$post->id",[
            'title' => 'Nuevo'
        ]);
        
        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title' => 'Nuevo'])
            ->assertStatus(200); 
        
        $this->assertDatabaseHas('posts', ['title' => 'Nuevo']); 
    }

    public function test_delete(){

        $user =factory(User::class)->create();
        $post = factory(Post::class)->create();
        
        $response = $this->actingAs($user, 'api')->json('DELETE', "/api/posts/$post->id");
        
        $response->assertSee(Null)
            ->assertStatus(204); //sin contenido...
        
        $this->assertDatabaseMissing('posts', ['id' => $post->id]); 
    }

    public function test_index(){

        $user =factory(User::class)->create();
        factory(Post::class, 5)->create();

        $response = $this->actingAs($user, 'api')->json('GET', '/api/posts');

        $response->assertJsonStructure([
            'data' => [
                '*' =>['id', 'title', 'created_at', 'updated_at']
            ]
        ])->assertStatus(200);
    }

    public function test_guest(){
        $this->json('GET',    '/api/posts')->assertStatus(401);
        $this->json('POST',   '/api/posts')->assertStatus(401);
        $this->json('GET',    '/api/posts/1000')->assertStatus(401);
        $this->json('PUT',    '/api/posts/1000')->assertStatus(401);
        $this->json('DELETE', '/api/posts/1000')->assertStatus(401);
    }
        
}
