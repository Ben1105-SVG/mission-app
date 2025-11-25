@extends('admin.layout')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.questions.index') }}" class="inline-flex items-center text-sm text-slate-600 hover:text-emerald-600 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Questions
            </a>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
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
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                            required
                        >
                        @error('group')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-700">Type (optional)</label>
                        <input
                            id="type"
                            name="type"
                            type="text"
                            value="{{ old('type') }}"
                            placeholder="text, select, comfort_scale..."
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                        >
                        @error('type')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="key" class="block text-sm font-medium text-slate-700">Key</label>
                    <input
                        id="key"
                        name="key"
                        type="text"
                        value="{{ old('key') }}"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                        required
                    >
                    @error('key')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="text" class="block text-sm font-medium text-slate-700">Question Text</label>
                    <textarea
                        id="text"
                        name="text"
                        rows="4"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 resize-none"
                        required
                    >{{ old('text') }}</textarea>
                    @error('text')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="hint" class="block text-sm font-medium text-slate-700">Hint (optional)</label>
                    <textarea
                        id="hint"
                        name="hint"
                        rows="3"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 resize-none"
                    >{{ old('hint') }}</textarea>
                    @error('hint')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="options" class="block text-sm font-medium text-slate-700">Options (optional, JSON array)</label>
                    <textarea
                        id="options"
                        name="options"
                        rows="3"
                        placeholder='["Option 1", "Option 2", "Option 3"]'
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-mono shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 resize-none"
                    >{{ old('options') }}</textarea>
                    <p class="mt-1 text-xs text-slate-500">Enter options as a JSON array, e.g., ["Yes", "No", "Maybe"]</p>
                    @error('options')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-4">
                    <button
                        type="submit"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 active:bg-emerald-800"
                    >
                        Create Question
                    </button>
                    <a
                        href="{{ route('admin.questions.index') }}"
                        class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2"
                    >
                        Cancel
                    </a>
                </div>
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


