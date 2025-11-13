@extends('admin.layout')

@section('content')
    <div class="grid gap-6 lg:grid-cols-5 lg:items-start lg:gap-10">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-3">
            <h2 class="text-xl font-semibold text-slate-800">Existing Questions</h2>
            <p class="mt-1 text-sm text-slate-500">Manage the prompts that appear in the group readiness questionnaire.</p>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Group</th>
                            <th class="px-4 py-3">Question</th>
                            <th class="px-4 py-3">Key</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($questions as $question)
                            <tr>
                                <td class="px-4 py-3 text-xs font-medium text-slate-600">{{ $question->group }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-800">{{ $question->text }}</div>
                                    @if ($question->hint)
                                        <div class="text-xs text-slate-500">Hint: {{ $question->hint }}</div>
                                    @endif
                                    @if ($question->type)
                                        <div class="text-xs text-slate-400 uppercase tracking-wide">Type: {{ $question->type }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ $question->key }}</td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" onsubmit="return confirm('Delete this question?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="rounded-full border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">No questions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-xl font-semibold text-slate-800">Add New Question</h2>
            <p class="mt-1 text-sm text-slate-500">Define the prompt, semantic key, and group allocation.</p>

            <form method="POST" action="{{ route('admin.questions.store') }}" class="mt-6 space-y-5">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="group" class="block text-sm font-medium text-slate-700">Group</label>
                        <input
                            id="group"
                            name="group"
                            type="number"
                            min="1"
                            value="{{ old('group', 1) }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                            required
                        >
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-700">Type (optional)</label>
                        <input
                            id="type"
                            name="type"
                            type="text"
                            value="{{ old('type') }}"
                            placeholder="text, select, comfort_scale..."
                            class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        >
                    </div>
                </div>

                <div>
                    <label for="key" class="block text-sm font-medium text-slate-700">Key</label>
                    <input
                        id="key"
                        name="key"
                        type="text"
                        value="{{ old('key') }}"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        required
                    >
                </div>

                <div>
                    <label for="text" class="block text-sm font-medium text-slate-700">Question Text</label>
                    <textarea
                        id="text"
                        name="text"
                        rows="4"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        required
                    >{{ old('text') }}</textarea>
                </div>

                <div>
                    <label for="hint" class="block text-sm font-medium text-slate-700">Hint (optional)</label>
                    <textarea
                        id="hint"
                        name="hint"
                        rows="3"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >{{ old('hint') }}</textarea>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2"
                >
                    Add Question
                </button>
            </form>

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-disc space-y-1 pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>
    </div>
@endsection

