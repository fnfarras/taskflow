<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
    ) {}

    /**
     * GET /api/v1/projects/{project}/tasks
     * List task dalam project. Filter: ?status= dan ?assignee_id=
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $filters = request()->only('status', 'assignee_id');
        $tasks   = $this->taskService->index($project, $filters);

        return TaskResource::collection($tasks)
            ->additional([
                'success' => true,
                'message' => 'Daftar task berhasil diambil.',
            ]);
    }

    /**
     * POST /api/v1/projects/{project}/tasks
     * Buat task baru dalam project.
     */
    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $this->authorize('create', [Task::class, $project]);

        $task = $this->taskService->store($project, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil dibuat.',
            'data'    => new TaskResource($task),
        ], 201);
    }

    /**
     * GET /api/v1/tasks/{task}   (shallow route)
     * Detail task.
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task = $this->taskService->show($task);

        return response()->json([
            'success' => true,
            'message' => 'Detail task berhasil diambil.',
            'data'    => new TaskResource($task),
        ]);
    }

    /**
     * PUT /api/v1/tasks/{task}   (shallow route)
     * Update task.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task = $this->taskService->update($task, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil diperbarui.',
            'data'    => new TaskResource($task),
        ]);
    }

    /**
     * DELETE /api/v1/tasks/{task}   (shallow route)
     * Soft delete task.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->destroy($task);

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil dihapus.',
            'data'    => null,
        ]);
    }
}
