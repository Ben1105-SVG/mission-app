<?php

namespace App\Http\Controllers;

use App\Models\Question;

class QuestionController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $questions = Question::orderBy('group')->get(['id', 'text', 'group', 'key', 'type', 'options', 'hint']);
        return response()->json($questions);
    }
}
