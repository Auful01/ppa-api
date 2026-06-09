<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Support\Api\SiteContext;
use Illuminate\Http\Request;

class DepartmentApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::query()->orderBy('department_name');

        if ($request->filled('is_site')) {
            $isSite = filter_var($request->input('is_site'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_site', $isSite ? 'Y' : null);
        }

        if ($request->filled('search')) {
            $query->where('department_name', 'like', '%' . $request->string('search') . '%');
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        SiteContext::authorizeWrite($request);

        $validated = $request->validate([
            'department_name' => ['required', 'string'],
            'code' => ['nullable', 'string'],
            'singkatan' => ['nullable', 'string'],
            'is_site' => ['nullable'],
        ]);

        $department = Department::create([
            'department_name' => $validated['department_name'],
            'code' => $validated['code'] ?? $validated['singkatan'] ?? null,
            'is_site' => $this->normalizeIsSite($request),
        ]);

        return response()->json([
            'message' => 'Department created successfully.',
            'data' => $department,
        ], 201);
    }

    public function show(int $id)
    {
        return response()->json([
            'data' => Department::findOrFail($id),
        ]);
    }

    public function update(Request $request, int $id)
    {
        SiteContext::authorizeWrite($request);

        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'department_name' => ['sometimes', 'string'],
            'code' => ['nullable', 'string'],
            'is_site' => ['nullable'],
        ]);

        if ($request->has('is_site')) {
            $validated['is_site'] = $this->normalizeIsSite($request);
        }

        $department->update($validated);

        return response()->json([
            'message' => 'Department updated successfully.',
            'data' => $department,
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        SiteContext::authorizeWrite($request);

        $department = Department::findOrFail($id);
        $department->delete();

        return response()->json([
            'message' => 'Department deleted.',
        ]);
    }

    private function normalizeIsSite(Request $request): ?string
    {
        if (! $request->has('is_site')) {
            return null;
        }

        $value = $request->input('is_site');

        if ($value === 'N' || $value === '0' || $value === 0 || $value === false) {
            return null;
        }

        return 'Y';
    }
}
