<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class TodoService
{
    public function getTodos(User $user, ?string $tag = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = $user->todos()->with('tags');

        if ($tag) {
            $query->whereHas('tags', fn ($q) => $q->where('name', $tag));
        }

        return $query->paginate($perPage);
    }

    public function createTodo(User $user, array $data): Todo
    {
        $todo = $user->todos()->create([
            'title' => $data['title'],
            'completed' => $data['completed'] ?? false,
        ]);

        if (! empty($data['tags'])) {
            $tagsIds = collect($data['tags'])->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName])->id;
            });
            $todo->tags()->sync($tagsIds);
        }

        return $todo->load('tags');
    }

    public function updateTodo(Todo $todo, array $data): Todo
    {
        $todo->update([
            'title' => $data['title'] ?? $todo->title,
            'completed' => $data['completed'] ?? $todo->completed,
        ]);

        if (isset($data['tags'])) {
            $tagIds = collect($data['tags'])->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName])->id;
            });
            $todo->tags()->sync($tagIds);
        }

        return $todo->load('tags');
    }

    public function deleteTodo(Todo $todo): void
    {
        $todo->delete();
    }
}
