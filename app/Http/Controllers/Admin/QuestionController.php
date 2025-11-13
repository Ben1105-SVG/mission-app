<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::orderBy('group')->orderBy('id')->get();

        return view('admin.questions.index', [
            'questions' => $questions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'group' => ['required', 'integer', 'min:1'],
            'text' => ['required', 'string'],
            'key' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'hint' => ['nullable', 'string'],
        ]);

        Question::create([
            'group' => $data['group'],
            'text' => $data['text'],
            'key' => $data['key'],
            'type' => $data['type'] ?? null,
            'hint' => $data['hint'] ?? null,
        ]);

        return back()->with('status', 'Question added.');
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return back()->with('status', 'Question removed.');
    }
}


    