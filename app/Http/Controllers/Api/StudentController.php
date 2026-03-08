<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Resources\StudentResource;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $students = Student::where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(15);

        return StudentResource::collection($students);
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);

        return new StudentResource($student);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Student::class);

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $student = Student::create([
            'user_id' => $request->user()->id,
            'name'    => $validated['name'],
            'email'   => $validated['email'] ?? null,
            'phone'   => $validated['phone'] ?? null,
        ]);

        return new StudentResource($student);
    }

    public function update(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'name'  => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
        ]);

        $student->update($validated);

        return new StudentResource($student);
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);

        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully'
        ]);
    }
}