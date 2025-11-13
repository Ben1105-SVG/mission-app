<template>
    <div class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-100">
        <header class="border-b border-slate-200 bg-white/80 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/70">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        Missions Intake
                    </p>
                    <h1 class="text-xl font-semibold">Group Readiness Questionnaire</h1>
                </div>
            </div>
        </header>

        <main class="mx-auto flex max-w-5xl flex-col gap-6 px-6 py-10 lg:flex-row">
            <section class="w-full lg:w-3/4">
                <QuestionsList />
            </section>

            <aside class="w-full space-y-4 rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/70 lg:w-1/4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Next Steps
                </h2>
                <ul class="space-y-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                    <li class="rounded-lg border border-slate-200/60 bg-slate-100/60 px-4 py-3 dark:border-slate-700/60 dark:bg-slate-800/60">
                        Review each group to understand the interview flow.
                    </li>
                    <li class="rounded-lg border border-slate-200/60 bg-slate-100/60 px-4 py-3 dark:border-slate-700/60 dark:bg-slate-800/60">
                        Confirm that questions align with the automated scoring rules.
                    </li>
                    <li class="rounded-lg border border-slate-200/60 bg-slate-100/60 px-4 py-3 dark:border-slate-700/60 dark:bg-slate-800/60">
                        Coordinate with Missions Coordinators for any follow-up.
                    </li>
                </ul>
            </aside>
        </main>

        <footer class="border-t border-slate-200 bg-white/80 px-6 py-4 text-center text-xs text-slate-500 backdrop-blur dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-400">
            Questions or feedback? Email
            <a href="mailto:stm@adventures.org" class="font-medium text-emerald-600 hover:underline dark:text-emerald-300">
                stm@adventures.org
            </a>
        </footer>
    </div>
</template>

<script setup>
import QuestionsList from './components/QuestionsList.vue';
</script>

