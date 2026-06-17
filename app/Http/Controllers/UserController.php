<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(public UserService $userService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $query = User::query();

        // 1. Search filter (name, email, nim)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        // 2. Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        // 3. Major filter
        if ($request->filled('major')) {
            $query->where('major', $request->input('major'));
        }

        // Paginate users and keep query parameters
        $users = $query->latest()->paginate(10)->withQueryString();

        // Retrieve alphabetically sorted majors list from users table
        $majors = User::whereNotNull('major')
            ->where('major', '!=', '')
            ->distinct()
            ->orderBy('major', 'asc')
            ->pluck('major');

        return Inertia::render('users/Index', [
            'users' => $users,
            'majors' => $majors,
            'filters' => $request->only(['search', 'role', 'major']),
            'breadcrumbs' => [
                ['title' => 'Manajemen Pengguna', 'href' => route('users.index')],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->createUser($request->validated());

        return Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Berhasil membuat pengguna.',
        ])->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->updateUser($user, $request->validated());

        return Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Berhasil memperbarui pengguna.',
        ])->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $this->userService->deleteUser($user, auth()->user());

        return Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Berhasil menghapus pengguna.',
        ])->back();
    }

    /**
     * Toggle the active status of the specified user.
     */
    public function toggleActive(User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $this->userService->toggleActiveStatus($user, auth()->user());

        return Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Berhasil memperbarui status pengguna.',
        ])->back();
    }

    /**
     * Import users from a CSV/XLSX file.
     */
    public function import(ImportUserRequest $request): JsonResponse
    {
        try {
            $importedCount = $this->userService->importUsers($request->file('file'));

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimpor {$importedCount} mahasiswa.",
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
