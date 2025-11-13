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

        <section class="rounded-3xl border border-slate-200 bg-white p-0 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-6 py-3">Applicant</th>
                            <th class="px-6 py-3">Contact</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Flags</th>
                            <th class="px-6 py-3">Submitted</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($inquiries as $inquiry)
                            <tr class="align-top">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800">{{ $inquiry->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $inquiry->group_leader_role ?? '—' }}</div>
                                </td>
                                <td class="px-6 py-4 space-y-1">
                                    <div class="text-slate-600">{{ $inquiry->email }}</div>
                                    @if ($inquiry->phone)
                                        <div class="text-xs text-slate-500">{{ $inquiry->phone }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}" class="space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <select
                                            name="status"
                                            class="w-full rounded-xl border border-slate-200 px-2 py-1 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                                        >
                                            @foreach (['green' => 'Green', 'yellow' => 'Yellow', 'red' => 'Red'] as $value => $label)
                                                <option value="{{ $value }}" @selected($inquiry->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <textarea
                                            name="flags"
                                            rows="3"
                                            placeholder="One flag per line…"
                                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                                        >{{ old('flags', $inquiry->flags ? implode("\n", (array) $inquiry->flags) : '') }}</textarea>
                                        <button
                                            type="submit"
                                            class="w-full rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                        >
                                            Save
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4">
                                    <ul class="space-y-1 text-xs text-slate-600">
                                        @forelse ((array) $inquiry->flags as $flag)
                                            <li class="rounded bg-slate-100 px-2 py-1">{{ $flag }}</li>
                                        @empty
                                            <li class="text-slate-400">No flags</li>
                                        @endforelse
                                    </ul>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    {{ $inquiry->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry) }}" onsubmit="return confirm('Delete this inquiry?')">
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
                                <td colspan="6" class="px-6 py-5 text-center text-sm text-slate-500">
                                    No inquiries found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $inquiries->links() }}
            </div>
        </section>
    </div>
@endsection

