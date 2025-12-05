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

            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                @if ($questions->hasPages())
                    <div class="flex flex-col items-center justify-between gap-4 text-sm text-slate-600 sm:flex-row">
                        <div>
                            <span class="font-medium">Showing</span>
                            <span class="font-semibold text-slate-900">{{ $questions->firstItem() ?? 0 }}</span>
                            <span class="font-medium">to</span>
                            <span class="font-semibold text-slate-900">{{ $questions->lastItem() ?? 0 }}</span>
                            <span class="font-medium">of</span>
                            <span class="font-semibold text-slate-900">{{ $questions->total() }}</span>
                            <span class="font-medium">results</span>
                        </div>
                        @php
                            $current = $questions->currentPage();
                            $last = $questions->lastPage();

                            // Window of up to 3 pages around the current page
                            $start = max(1, $current - 1);
                            $end = min($last, $start + 2);
                            $start = max(1, $end - 2);
                        @endphp
                        <nav class="flex items-center gap-1 text-xs font-medium text-slate-600" aria-label="Pagination">
                            {{-- First --}}
                            @if ($current > 1)
                                <a href="{{ $questions->url(1) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white hover:bg-slate-100"
                                   aria-label="First page">
                                    &laquo;
                                </a>
                            @else
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-100 bg-slate-50 text-slate-300 cursor-default">
                                    &laquo;
                                </span>
                            @endif

                            {{-- Previous --}}
                            @if ($current > 1)
                                <a href="{{ $questions->url($current - 1) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white hover:bg-slate-100"
                                   aria-label="Previous page">
                                    &lsaquo;
                                </a>
                            @else
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-100 bg-slate-50 text-slate-300 cursor-default">
                                    &lsaquo;
                                </span>
                            @endif

                            {{-- Page numbers (max 3) --}}
                            @for ($page = $start; $page <= $end; $page++)
                                @if ($page == $current)
                                    <span class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-full bg-emerald-600 px-3 text-xs font-semibold text-white">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $questions->url($page) }}"
                                       class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-full border border-slate-200 bg-white px-3 text-xs hover:bg-slate-100">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endfor

                            {{-- Next --}}
                            @if ($current < $last)
                                <a href="{{ $questions->url($current + 1) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white hover:bg-slate-100"
                                   aria-label="Next page">
                                    &rsaquo;
                                </a>
                            @else
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-100 bg-slate-50 text-slate-300 cursor-default">
                                    &rsaquo;
                                </span>
                            @endif

                            {{-- Last --}}
                            @if ($current < $last)
                                <a href="{{ $questions->url($last) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white hover:bg-slate-100"
                                   aria-label="Last page">
                                    &raquo;
                                </a>
                            @else
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-100 bg-slate-50 text-slate-300 cursor-default">
                                    &raquo;
                                </span>
                            @endif
                        </nav>
                    </div>
                @else
                    <div class="text-center text-sm text-slate-600">
                        <span class="font-medium">Showing</span>
                        <span class="font-semibold text-slate-900">{{ $questions->count() }}</span>
                        <span class="font-medium">of</span>
                        <span class="font-semibold text-slate-900">{{ $questions->total() }}</span>
                        <span class="font-medium">results</span>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

