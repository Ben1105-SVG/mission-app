@extends('admin.layout')

@section('content')
    <div class="flex flex-col gap-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">Inquiries</h2>
                    <p class="text-sm text-slate-500">Review application submissions, adjust status, or remove records.</p>
                </div>
                <form method="GET" class="flex items-center gap-2">
                    <label for="status" class="text-sm font-medium text-slate-600">Filter by status:</label>
                    <select
                        id="status"
                        name="status"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        onchange="this.form.submit()"
                    >
                        <option value="">All</option>
                        <option value="green" @selected(request('status') === 'green')>Green</option>
                        <option value="yellow" @selected(request('status') === 'yellow')>Yellow</option>
                        <option value="red" @selected(request('status') === 'red')>Red</option>
                    </select>
                </form>
            </div>

            <dl class="mt-4 grid grid-cols-1 gap-4 text-sm text-slate-600 sm:grid-cols-3">
                <div class="rounded-2xl border border-emerald-200/70 bg-emerald-50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-emerald-700">Green</dt>
                    <dd class="mt-1 text-lg font-semibold text-emerald-700">{{ $totals['green'] ?? 0 }}</dd>
                </div>
                <div class="rounded-2xl border border-amber-200/70 bg-amber-50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-amber-700">Yellow</dt>
                    <dd class="mt-1 text-lg font-semibold text-amber-700">{{ $totals['yellow'] ?? 0 }}</dd>
                </div>
                <div class="rounded-2xl border border-rose-200/70 bg-rose-50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-rose-700">Red</dt>
                    <dd class="mt-1 text-lg font-semibold text-rose-700">{{ $totals['red'] ?? 0 }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Applicant</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Flags</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Submitted</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($inquiries as $inquiry)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900">{{ $inquiry->name }}</div>
                                    @if ($inquiry->group_leader_role)
                                        <div class="mt-1 text-xs text-slate-500">{{ $inquiry->group_leader_role }}</div>
                                    @else
                                        <div class="mt-1 text-xs text-slate-400">—</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-900">{{ $inquiry->email }}</div>
                                    @if ($inquiry->phone)
                                        <div class="mt-1 text-xs text-slate-500">{{ $inquiry->phone }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}" class="space-y-2 min-w-[200px]">
                                        @csrf
                                        @method('PATCH')
                                        <div class="mb-2">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                                @if($inquiry->status === 'green') bg-emerald-100 text-emerald-800
                                                @elseif($inquiry->status === 'yellow') bg-amber-100 text-amber-800
                                                @elseif($inquiry->status === 'red') bg-rose-100 text-rose-800
                                                @else bg-slate-100 text-slate-800
                                                @endif">
                                                {{ ucfirst($inquiry->status ?? 'Unknown') }}
                                            </span>
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
                                <td class="px-6 py-4 max-w-xs">
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
                                    @if (count($flagsArray) > 0)
                                        <ul class="space-y-1.5">
                                            @foreach ($flagsArray as $flag)
                                                <li class="rounded-md bg-slate-100 px-2.5 py-1.5 text-xs text-slate-700 border border-slate-200">
                                                    {{ is_string($flag) ? $flag : json_encode($flag) }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-xs text-slate-400 italic">No flags</span>
                                    @endif
                                    @if (count($explanations) > 0)
                                        <div class="mt-3 pt-3 border-t border-slate-200">
                                            <div class="text-xs font-semibold text-slate-600 mb-1.5">Explanations:</div>
                                            <ul class="space-y-1">
                                                @foreach ($explanations as $key => $explanation)
                                                    <li class="text-xs text-slate-500 italic pl-2 border-l-2 border-slate-200">
                                                        {{ is_string($explanation) ? $explanation : json_encode($explanation) }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if ($score !== null)
                                        <div class="mt-2 text-xs">
                                            <span class="font-semibold text-slate-600">Score:</span>
                                            <span class="text-slate-700">{{ $score }}/100</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-600">{{ $inquiry->created_at->format('M j, Y') }}</div>
                                    <div class="text-xs text-slate-400">{{ $inquiry->created_at->format('g:i A') }}</div>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry) }}" onsubmit="return confirm('Are you sure you want to delete this inquiry? This action cannot be undone.')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 shadow-sm transition-colors hover:bg-rose-50 hover:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1 active:bg-rose-100"
                                        >
                                            Delete
                                        </button>
                                    </form>
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
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-slate-600">
                            <span class="font-medium">Showing</span>
                            <span class="font-semibold text-slate-900">{{ $inquiries->firstItem() ?? 0 }}</span>
                            <span class="font-medium">to</span>
                            <span class="font-semibold text-slate-900">{{ $inquiries->lastItem() ?? 0 }}</span>
                            <span class="font-medium">of</span>
                            <span class="font-semibold text-slate-900">{{ $inquiries->total() }}</span>
                            <span class="font-medium">results</span>
                        </div>
                        <div class="flex-shrink-0">
                            {{ $inquiries->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-sm text-slate-600 text-center">
                        <span class="font-medium">Showing</span>
                        <span class="font-semibold text-slate-900">{{ $inquiries->count() }}</span>
                        <span class="font-medium">of</span>
                        <span class="font-semibold text-slate-900">{{ $inquiries->total() }}</span>
                        <span class="font-medium">results</span>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

