<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreMemberRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService,
    ) {}

    /**
     * GET /api/v1/projects
     * List project milik user (owner atau member), dengan pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->projectService->index($request->user());

        return ProjectResource::collection($projects)
            ->additional([
                'success' => true,
                'message' => 'Daftar project berhasil diambil.',
            ]);
    }

    /**
     * POST /api/v1/projects
     * Buat project baru. User pembuat otomatis menjadi owner.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $project = $this->projectService->store($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Project berhasil dibuat.',
            'data'    => new ProjectResource($project),
        ], 201);
    }

    /**
     * GET /api/v1/projects/{project}
     * Detail project — hanya owner/member yang bisa akses.
     */
    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project = $this->projectService->show($project);

        return response()->json([
            'success' => true,
            'message' => 'Detail project berhasil diambil.',
            'data'    => new ProjectResource($project),
        ]);
    }

    /**
     * PUT /api/v1/projects/{project}
     * Update project — hanya owner.
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project = $this->projectService->update($project, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Project berhasil diperbarui.',
            'data'    => new ProjectResource($project),
        ]);
    }

    /**
     * DELETE /api/v1/projects/{project}
     * Hapus project — hanya owner.
     */
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $this->projectService->destroy($project);

        return response()->json([
            'success' => true,
            'message' => 'Project berhasil dihapus.',
            'data'    => null,
        ]);
    }

    /**
     * POST /api/v1/projects/{project}/members
     * Tambah member ke project — hanya owner.
     */
    public function addMember(StoreMemberRequest $request, Project $project): JsonResponse
    {
        $this->authorize('addMember', $project);

        $member = $this->projectService->addMember($project, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Member berhasil ditambahkan ke project.',
            'data'    => [
                'project_id' => $member->project_id,
                'user_id'    => $member->user_id,
                'role'       => $member->role,
            ],
        ], 201);
    }
}
