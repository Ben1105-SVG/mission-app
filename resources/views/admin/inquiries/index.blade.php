@extends('admin.layout')

@section('content')
    <div class="flex flex-col gap-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">Inquiries</h2>
                    <p class="text-sm text-slate-500">
                        Click a status card to quickly filter by Green, Yellow, or Red. Click it again to show all inquiries.
                    </p>
                </div>
            </div>

            @php
                $activeStatus = request('status');
                $statusRoute = fn (string $status) =>
                    $activeStatus === $status
                        ? route('admin.inquiries.index')
                        : route('admin.inquiries.index', ['status' => $status]);
            @endphp

            <dl class="mt-4 grid grid-cols-1 gap-4 text-sm text-slate-600 sm:grid-cols-3">
                <a
                    href="{{ $statusRoute('green') }}"
                    class="group block rounded-2xl border px-4 py-3 transition-colors
                        @if($activeStatus === 'green')
                            border-emerald-400 bg-emerald-50 ring-1 ring-emerald-300
                        @else
                            border-emerald-200/70 bg-emerald-50 hover:border-emerald-300
                        @endif"
                >
                    <dt class="text-xs uppercase tracking-wide text-emerald-700 flex items-center justify-between">
                        <span>Green</span>
                        @if($activeStatus === 'green')
                            <span class="text-[10px] font-semibold text-emerald-700">Showing only green • Click again for all</span>
                        @endif
                    </dt>
                    <dd class="mt-1 text-lg font-semibold text-emerald-700">{{ $totals['green'] ?? 0 }}</dd>
                </a>

                <a
                    href="{{ $statusRoute('yellow') }}"
                    class="group block rounded-2xl border px-4 py-3 transition-colors
                        @if($activeStatus === 'yellow')
                            border-amber-400 bg-amber-50 ring-1 ring-amber-300
                        @else
                            border-amber-200/70 bg-amber-50 hover:border-amber-300
                        @endif"
                >
                    <dt class="text-xs uppercase tracking-wide text-amber-700 flex items-center justify-between">
                        <span>Yellow</span>
                        @if($activeStatus === 'yellow')
                            <span class="text-[10px] font-semibold text-amber-700">Showing only yellow • Click again for all</span>
                        @endif
                    </dt>
                    <dd class="mt-1 text-lg font-semibold text-amber-700">{{ $totals['yellow'] ?? 0 }}</dd>
                </a>

                <a
                    href="{{ $statusRoute('red') }}"
                    class="group block rounded-2xl border px-4 py-3 transition-colors
                        @if($activeStatus === 'red')
                            border-rose-400 bg-rose-50 ring-1 ring-rose-300
                        @else
                            border-rose-200/70 bg-rose-50 hover:border-rose-300
                        @endif"
                >
                    <dt class="text-xs uppercase tracking-wide text-rose-700 flex items-center justify-between">
                        <span>Red</span>
                        @if($activeStatus === 'red')
                            <span class="text-[10px] font-semibold text-rose-700">Showing only red • Click again for all</span>
                        @endif
                    </dt>
                    <dd class="mt-1 text-lg font-semibold text-rose-700">{{ $totals['red'] ?? 0 }}</dd>
                </a>
            </dl>
        </section>

        @if ($inquiries->total() === 0)
            <section class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center shadow-sm">
                <div class="mx-auto max-w-xl space-y-3">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-200 text-slate-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-800">No inquiries yet</h3>
                    <p class="text-sm text-slate-500">
                        Once someone submits the mission trip application, their inquiry will appear here for you to review.
                    </p>
                </div>
            </section>
        @else
        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full table-fixed divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="w-[18%] px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Applicant</th>
                            <th class="w-[18%] px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Contact</th>
                            <th class="w-[22%] px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Status</th>
                            <th class="w-[24%] px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Flags</th>
                            <th class="w-[10%] px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Submitted</th>
                            <th class="w-[8%] px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($inquiries as $inquiry)
                            <tr class="align-top border-l-4 transition-colors odd:bg-white even:bg-slate-50/60 hover:bg-slate-100
                                @if($inquiry->status === 'green')
                                    border-emerald-400
                                @elseif($inquiry->status === 'yellow')
                                    border-amber-400 bg-amber-50/40
                                @elseif($inquiry->status === 'red')
                                    border-rose-400 bg-rose-50/40
                                @else
                                    border-slate-200
                                @endif
                            ">
                                <td class="px-6 py-4 align-top">
                                    <div class="font-semibold text-slate-900">{{ $inquiry->name }}</div>
                                    @if ($inquiry->group_leader_role)
                                        <div class="mt-1 text-xs text-slate-500">{{ $inquiry->group_leader_role }}</div>
                                    @else
                                        <div class="mt-1 text-xs text-slate-400">—</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="text-sm font-medium text-slate-900 break-words">{{ $inquiry->email }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $inquiry->phone ?: '—' }}</div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}" class="space-y-3">
                                        @csrf
                                        @method('PATCH')
                                        <div class="flex items-center gap-2 text-xs font-semibold">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5
                                                @if($inquiry->status === 'green') bg-emerald-100 text-emerald-800
                                                @elseif($inquiry->status === 'yellow') bg-amber-100 text-amber-800
                                                @elseif($inquiry->status === 'red') bg-rose-100 text-rose-800
                                                @else bg-slate-100 text-slate-800
                                                @endif">
                                                {{ ucfirst($inquiry->status ?? 'Unknown') }}
                                            </span>
                                            <span class="text-slate-400">/</span>
                                            <span class="text-slate-500">Update</span>
                                        </div>
                                        <select
                                            name="status"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium shadow-sm transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                                        >
                                            @foreach (['green' => 'Green', 'yellow' => 'Yellow', 'red' => 'Red'] as $value => $label)
                                                <option value="{{ $value }}" @selected($inquiry->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @php
                                            $flagsText = old('flags', '');
                                            if (!$flagsText && is_array($inquiry->flags) && count($inquiry->flags) > 0) {
                                                // New format: flags are in ['flags'] key
                                                if (isset($inquiry->flags['flags']) && is_array($inquiry->flags['flags'])) {
                                                    $flagsText = implode("\n", array_filter($inquiry->flags['flags']));
                                                }
                                                // Old format: flags are direct array (numeric keys)
                                                else {
                                                    $keys = array_keys($inquiry->flags);
                                                    if (!empty($keys) && $keys === range(0, count($inquiry->flags) - 1)) {
                                                        $flagsText = implode("\n", array_filter($inquiry->flags));
                                                    }
                                                }
                                            }
                                        @endphp
                                        <textarea
                                            name="flags"
                                            rows="3"
                                            placeholder="One flag per line…"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 resize-none"
                                        >{{ $flagsText }}</textarea>
                                        <button
                                            type="submit"
                                            class="w-full rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 active:bg-emerald-800"
                                        >
                                            Save Changes
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    @php
                                        $flagsArray = [];
                                        if (is_array($inquiry->flags) && count($inquiry->flags) > 0) {
                                            // New format: flags are in ['flags'] key
                                            if (isset($inquiry->flags['flags']) && is_array($inquiry->flags['flags'])) {
                                                $flagsArray = array_filter($inquiry->flags['flags']);
                                            }
                                            // Old format: flags are direct array (numeric keys)
                                            else {
                                                $keys = array_keys($inquiry->flags);
                                                if (!empty($keys) && $keys === range(0, count($inquiry->flags) - 1)) {
                                                    $flagsArray = array_filter($inquiry->flags);
                                                }
                                            }
                                        }
                                        $explanations = (is_array($inquiry->flags) && isset($inquiry->flags['explanations']) && is_array($inquiry->flags['explanations'])) ? $inquiry->flags['explanations'] : [];
                                        $score = (is_array($inquiry->flags) && isset($inquiry->flags['score'])) ? $inquiry->flags['score'] : null;
                                    @endphp
                                    <div class="space-y-2">
                                        @if (count($flagsArray) > 0)
                                            <div class="pb-1">
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($flagsArray as $flag)
                                                        <span class="inline-flex items-start rounded-md border border-amber-200 bg-amber-50 px-2 py-1.5 text-xs text-amber-900">
                                                            <span class="mt-0.5 mr-1.5 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-amber-500"></span>
                                                            <span class="max-w-[240px] break-words">
                                                                {{ is_string($flag) ? $flag : json_encode($flag) }}
                                                            </span>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400 italic">No flags</span>
                                        @endif

                                        @if (count($explanations) > 0)
                                            <div class="mt-2 rounded-md border border-slate-200 bg-slate-50 px-2.5 py-2">
                                                <div class="mb-1.5 text-xs font-semibold text-slate-600">Explanations</div>
                                                <ul class="space-y-1">
                                                    @foreach ($explanations as $key => $explanation)
                                                        @php
                                                            $text = is_string($explanation) ? $explanation : json_encode($explanation);
                                                            $preview = \Illuminate\Support\Str::limit($text, 160);
                                                        @endphp
                                                        <li class="text-xs text-slate-500">
                                                            <details>
                                                                <summary class="cursor-pointer list-none text-slate-500 hover:text-slate-700">
                                                                    {{ $preview }}
                                                                </summary>
                                                                @if ($text !== $preview)
                                                                    <p class="mt-1 text-slate-600">
                                                                        {{ $text }}
                                                                    </p>
                                                                @endif
                                                            </details>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                    @if ($score !== null)
                                        <div class="mt-2 text-xs">
                                            <span class="font-semibold text-slate-600">Score:</span>
                                            <span class="text-slate-700">{{ $score }}/100</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-top whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-700">{{ $inquiry->created_at->format('M j, Y') }}</div>
                                    <div class="text-xs text-slate-400">{{ $inquiry->created_at->format('g:i A') }}</div>
                                </td>
                                <td class="px-6 py-4 align-top text-right">
                                    <div class="flex flex-col items-end gap-2 text-xs">
                                        <form method="POST" action="{{ route('admin.inquiries.sendEmail', $inquiry) }}" class="inline-flex w-full justify-end">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 font-semibold text-blue-700 shadow-sm transition-colors hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 active:bg-blue-200"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12l14-7-4 14-3.5-4.5L10 17l1.5-4.5L5 12z" />
                                                </svg>
                                                Send Email
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry) }}" onsubmit="return confirm('Are you sure you want to delete this inquiry? This action cannot be undone.')" class="inline-flex w-full justify-end">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1 rounded-lg border border-rose-300 bg-white px-3 py-1.5 font-semibold text-rose-600 shadow-sm transition-colors hover:bg-rose-50 hover:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1 active:bg-rose-100"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="text-sm text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="mt-2 font-medium">No inquiries found</p>
                                        <p class="mt-1 text-xs">Try adjusting your filter criteria</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                @if ($inquiries->hasPages())
                    <div class="flex flex-col items-center justify-between gap-4 text-sm text-slate-600 sm:flex-row">
                        <div>
                            <span class="font-medium">Showing</span>
                            <span class="font-semibold text-slate-900">{{ $inquiries->firstItem() ?? 0 }}</span>
                            <span class="font-medium">to</span>
                            <span class="font-semibold text-slate-900">{{ $inquiries->lastItem() ?? 0 }}</span>
                            <span class="font-medium">of</span>
                            <span class="font-semibold text-slate-900">{{ $inquiries->total() }}</span>
                            <span class="font-medium">results</span>
                        </div>
                        @php
                            $current = $inquiries->currentPage();
                            $last = $inquiries->lastPage();

                            // Window of up to 3 pages around the current page
                            $start = max(1, $current - 1);
                            $end = min($last, $start + 2);
                            $start = max(1, $end - 2);
                        @endphp
                        <nav class="flex items-center gap-1 text-xs font-medium text-slate-600" aria-label="Pagination">
                            {{-- First --}}
                            @if ($current > 1)
                                <a href="{{ $inquiries->url(1) }}"
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
                                <a href="{{ $inquiries->url($current - 1) }}"
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
                                    <a href="{{ $inquiries->url($page) }}"
                                       class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-full border border-slate-200 bg-white px-3 text-xs hover:bg-slate-100">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endfor

                            {{-- Next --}}
                            @if ($current < $last)
                                <a href="{{ $inquiries->url($current + 1) }}"
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
                                <a href="{{ $inquiries->url($last) }}"
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
                        <span class="font-semibold text-slate-900">{{ $inquiries->count() }}</span>
                        <span class="font-medium">of</span>
                        <span class="font-semibold text-slate-900">{{ $inquiries->total() }}</span>
                        <span class="font-medium">results</span>
                    </div>
                @endif
            </div>
        </section>
        @endif
    </div>
@endsection

