<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'options' => [
                'Manage Absence' => route('absences.index'),
                'Manage Students' => route('students.index'),
                'Manage Teachers' => route('teachers.index'),
                'Manage Modules' => route('modules.index'),
                'Manage Elements' => route('elements.index'),
            ]
        ]);
    }
}
