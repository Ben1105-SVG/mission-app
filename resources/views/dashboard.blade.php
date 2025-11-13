<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | {{ config('app.name', 'Mission Application') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <header class="border-b border-slate-200 bg-white/80 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <div>
                <p class="text-xs uppercase tracking-widest text-slate-500">Team Dashboard</p>
                <h1 class="text-lg font-semibold">Welcome back, {{ auth()->user()->name }}</h1>
            </div>
            <nav class="flex items-center gap-3 text-sm">
                <a href="/" class="rounded-full border border-slate-200 px-4 py-1.5 font-medium text-slate-600 hover:bg-slate-100">Public Site</a>
                @can('access-admin')
                    <a href="{{ route('admin.inquiries.index') }}" class="rounded-full border border-emerald-200 bg-emerald-50 px-4 py-1.5 font-medium text-emerald-600 hover:bg-emerald-100">Admin</a>
                @endcan
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-full border border-slate-200 px-4 py-1.5 font-medium text-slate-600 hover:bg-slate-100">
                        Log out
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-10 space-y-8">
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['title' => 'Active Trips', 'value' => '12', 'change' => '+3.2%', 'color' => 'emerald'],
                ['title' => 'Pending Applications', 'value' => '48', 'change' => '+5.1%', 'color' => 'sky'],
                ['title' => 'Average Team Size', 'value' => '16', 'change' => 'Stable', 'color' => 'amber'],
                ['title' => 'Support Tickets', 'value' => '7', 'change' => '-1.8%', 'color' => 'rose'],
            ] as $card)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-slate-500">{{ $card['title'] }}</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-semibold text-slate-800">{{ $card['value'] }}</span>
                        <span
                            @class([
                                'text-xs font-medium',
                                'text-emerald-600' => str_contains($card['change'], '+'),
                                'text-rose-600' => str_contains($card['change'], '-'),
                                'text-slate-500' => $card['change'] === 'Stable',
                            ])
                        >
                            {{ $card['change'] }}
                        </span>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-800">Upcoming Team Launches</h2>
                        <p class="text-sm text-slate-500">A snapshot of teams preparing to deploy this quarter.</p>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600">Timeline</span>
                </div>
                <ul class="mt-6 space-y-4">
                    @foreach ([
                        ['date' => 'Dec 5, 2025', 'location' => 'Kingston, Jamaica', 'leader' => 'Metro Community Church', 'members' => 22],
                        ['date' => 'Dec 12, 2025', 'location' => 'San Juan, Puerto Rico', 'leader' => 'Hope City Youth', 'members' => 18],
                        ['date' => 'Jan 3, 2026', 'location' => 'Cusco, Peru', 'leader' => 'Northside Baptist', 'members' => 15],
                        ['date' => 'Jan 17, 2026', 'location' => 'Nairobi, Kenya', 'leader' => 'Awaken Church', 'members' => 24],
                    ] as $trip)
                        <li class="rounded-2xl border border-slate-100 px-4 py-3">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $trip['location'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $trip['leader'] }}</p>
                                </div>
                                <div class="text-right text-xs text-slate-500">
                                    <p class="font-medium text-slate-600">{{ $trip['date'] }}</p>
                                    <p>{{ $trip['members'] }} participants</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-800">Company Pulse</h2>
                <p class="mt-1 text-sm text-slate-500">Quick indicators of partner engagement and preparation.</p>
                <div class="mt-5 space-y-4 text-sm">
                    <div class="rounded-xl border border-emerald-200/70 bg-emerald-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-emerald-700">Partner Satisfaction</p>
                        <p class="mt-1 text-lg font-semibold text-emerald-700">92%</p>
                        <p class="text-xs text-emerald-600">▲ Up 4% vs last month</p>
                    </div>
                    <div class="rounded-xl border border-sky-200/70 bg-sky-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-sky-700">Training Completion</p>
                        <p class="mt-1 text-lg font-semibold text-sky-700">78% of teams</p>
                        <p class="text-xs text-sky-600">Teams awaiting prep resources: 6</p>
                    </div>
                    <div class="rounded-xl border border-amber-200/70 bg-amber-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-amber-700">Support Follow-ups</p>
                        <p class="mt-1 text-lg font-semibold text-amber-700">14 active cases</p>
                        <p class="text-xs text-amber-600">Median response time: 3h 12m</p>
                    </div>
                </div>
            </article>
        </section>
    </main>
</body>
</html>

