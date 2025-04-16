<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Models\Todo;
use App\Services\TodoService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class TodoController extends Controller
{
    use AuthorizesRequests;

    protected $todoService;

    public function __construct(TodoService $todoService)
    {
        $this->todoService = $todoService;
    }

    public function index(): LengthAwarePaginator
    {
        $tag = request()->input('tag');

        return $this->todoService->getTodos(auth()->user(), $tag);
    }

    public function store(StoreTodoRequest $request): JsonResponse
    {
        $todo = $this->todoService->createTodo(auth()->user(), $request->validated());

        return response()->json($todo, 201);
    }

    public function show(Todo $todo): Todo
    {
        $this->authorize('view', $todo);

        return $todo;
    }

    public function update(UpdateTodoRequest $request, Todo $todo): Todo
    {
        $this->authorize('update', $todo);

        $todo = $this->todoService->updateTodo($todo, $request->validated());

        return $todo;
    }

    public function destroy(Todo $todo): JsonResponse
    {
        $this->authorize('delete', $todo);

        $this->todoService->deleteTodo($todo);

        return response()->json(null, 204);
    }
}
