@extends('admin.layout')

@section('content')
    <div class="flex flex-col gap-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">Questions</h2>
                    <p class="mt-1 text-sm text-slate-500">Manage the prompts that appear in the group readiness questionnaire.</p>
                </div>
                <a
                    href="{{ route('admin.questions.create') }}"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Question
                </a>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div>
                <table class="w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Group</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Question</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Key</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Type</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($questions as $question)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 font-semibold text-sm">
                                        {{ $question->group }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900">{{ $question->text }}</div>
                                    @if ($question->hint)
                                        <div class="mt-1.5 text-xs text-slate-500 italic">{{ $question->hint }}</div>
                                    @endif
                                    @if ($question->options && is_array($question->options) && count($question->options) > 0)
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            @foreach ($question->options as $option)
                                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700 border border-slate-200">
                                                    {{ is_string($option) ? $option : json_encode($option) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <code class="text-xs font-mono text-slate-600 bg-slate-100 px-2 py-1 rounded border border-slate-200">{{ $question->key }}</code>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($question->type)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                            {{ $question->type }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="{{ route('admin.questions.edit', $question) }}"
                                            class="rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-600 shadow-sm transition-colors hover:bg-emerald-50 hover:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 active:bg-emerald-100"
                                        >
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" onsubmit="return confirm('Are you sure you want to delete this question? This action cannot be undone.')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 shadow-sm transition-colors hover:bg-rose-50 hover:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1 active:bg-rose-100"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="text-sm text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="mt-2 font-medium">No questions found</p>
                                        <p class="mt-1 text-xs">Add your first question using the form on the right</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($questions->hasPages())
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                    {{ $questions->links() }}
                </div>
            @elseif ($questions->total() > 0)
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <div class="text-sm text-slate-600 text-center">
                        <span class="font-medium">Showing</span>
                        <span class="font-semibold text-slate-900">{{ $questions->count() }}</span>
                        <span class="font-medium">of</span>
                        <span class="font-semibold text-slate-900">{{ $questions->total() }}</span>
                        <span class="font-medium">results</span>
                    </div>
                </div>
            @endif
        </section>
    </div>
@endsection

