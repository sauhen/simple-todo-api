<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth_token')->plainTextToken;
    }

    public function test_can_list_todos_with_pagination()
    {
        Todo::factory()->count(25)->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/todos?page=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'title', 'completed', 'tags']],
                'current_page',
                'last_page',
                'per_page',
                'total',
            ])
            ->assertJsonFragment(['per_page' => 20, 'current_page' => 1])
            ->assertJsonCount(20, 'data');
    }

    public function test_can_filter_todos_by_tag()
    {
        $tag = Tag::create(['name' => 'urgent']);
        $todo = Todo::factory()->create(['user_id' => $this->user->id]);
        $todo->tags()->attach($tag);
        Todo::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/todos?tag=urgent');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => $todo->title]);
    }

    public function test_can_create_todo()
    {
        $data = [
            'title' => 'Test task',
            'completed' => false,
            'tags' => ['test', 'urgent'],
        ];

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/todos', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => $data['title']])
            ->assertJsonStructure(['tags']);
    }

    public function test_cannot_create_todo_with_invalid_tags()
    {
        $data = [
            'title' => 'Test',
            'tags' => ['too_long_a_hashtag_exceeding_50_characters_1234567890'],
        ];

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/todos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('tags.0');
    }

    public function test_unauthorized_access_denied()
    {
        $response = $this->getJson('/api/todos');

        $response->assertStatus(401);
    }
}
