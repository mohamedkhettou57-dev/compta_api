<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // GET /api/students
    public function index()
    {
        return response()->json(Student::all(), 200);
    }

    // POST /api/students
    public function store(StoreStudentRequest $request)
    {
        $student = Student::create($request->validated());

        return response()->json($student, 201);
    }

    // GET /api/students/{student}
    public function show(Student $student)
    {
        return response()->json($student, 200);
    }

    // PUT/PATCH /api/students/{student}
  public function update(UpdateStudentRequest $request, Student $student)
{
    $student->update($request->validated());
    return response()->json($student, 200);
}
    // DELETE /api/students/{student}
    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully'
        ], 200);
    }
}