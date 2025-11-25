<?php

namespace App\Http\Controllers\Admin;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends AdminController
{
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $questions = Question::orderBy('group')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.questions.index', [
            'questions' => $questions,
        ]);
    }

    public function create(): \Illuminate\Contracts\View\View
    {
        return view('admin.questions.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'group' => ['required', 'integer', 'min:1'],
            'text' => ['required', 'string'],
            'key' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'options' => ['nullable'],
            'hint' => ['nullable', 'string'],
        ]);

        // Parse options if provided as JSON string
        $options = null;
        if (!empty($data['options'])) {
            if (is_string($data['options'])) {
                $decoded = json_decode($data['options'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $options = $decoded;
                }
            } elseif (is_array($data['options'])) {
                $options = $data['options'];
            }
        }

        Question::create([
            'group' => $data['group'],
            'text' => $data['text'],
            'key' => $data['key'],
            'type' => $data['type'] ?? null,
            'options' => $options,
            'hint' => $data['hint'] ?? null,
        ]);

        return redirect()->route('admin.questions.index')->with('status', 'Question added successfully.');
    }

    public function edit(Question $question): \Illuminate\Contracts\View\View
    {
        return view('admin.questions.edit', [
            'question' => $question,
        ]);
    }

    public function update(Request $request, Question $question): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'group' => ['required', 'integer', 'min:1'],
            'text' => ['required', 'string'],
            'key' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'options' => ['nullable', 'string'],
            'hint' => ['nullable', 'string'],
        ]);

        // Parse options if provided as JSON string
        $options = null;
        if (!empty($data['options'])) {
            $decoded = json_decode($data['options'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $options = $decoded;
            } else {
                // If not valid JSON, try to treat as array
                $options = is_array($data['options']) ? $data['options'] : null;
            }
        }

        $question->update([
            'group' => $data['group'],
            'text' => $data['text'],
            'key' => $data['key'],
            'type' => $data['type'] ?? null,
            'options' => $options,
            'hint' => $data['hint'] ?? null,
        ]);

        return redirect()->route('admin.questions.index')->with('status', 'Question updated successfully.');
    }

    public function destroy(Question $question): \Illuminate\Http\RedirectResponse
    {
        $question->delete();

        return back()->with('status', 'Question removed.');
    }
}