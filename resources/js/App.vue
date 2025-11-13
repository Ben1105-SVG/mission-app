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

        <main class="mx-auto flex max-w-5xl flex-col gap-6 px-6 py-10 lg:flex-row lg:items-start lg:gap-10">
            <section class="w-full lg:flex-[3]">
                <QuestionsList />
            </section>
          <aside class="w-full space-y-4 lg:flex-[1.2]">
            <article class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/70">
              <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Next Steps
              </h2>
              <ul class="mt-4 space-y-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
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
            </article>


          </aside>
        </main>
      <section>
        <div class="container mx-auto mb-[15px]">
          <article class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/70">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
              Jamaica Trip FAQs
            </h2>
            <dl class="mt-4 space-y-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
              <div>
                <dt class="font-semibold text-slate-700 dark:text-slate-100">How many people can we host?</dt>
                <dd class="mt-1">
                  Final capacity is being finalized, but we’ll confirm group size as the Jamaica launch wraps up.
                </dd>
              </div>
              <div>
                <dt class="font-semibold text-slate-700 dark:text-slate-100">What is the lodging like?</dt>
                <dd class="mt-1">
                  Lodging may range from church floors to bunk housing. We’ll provide exact details once partners confirm the space.
                </dd>
              </div>
              <div>
                <dt class="font-semibold text-slate-700 dark:text-slate-100">What’s covered in the trip cost?</dt>
                <dd class="mt-2">
                  <ul class="list-disc space-y-1 pl-4">
                    <li>All meals during the trip.</li>
                    <li>Lodging accommodations (specific setup to be confirmed).</li>
                    <li>Local transportation: airport pickup/drop-off and daily ministry site travel.</li>
                    <li>An on-site Mission Leader with your group for the duration of the trip.</li>
                    <li>Trip setup, coordination, and logistics support.</li>
                    <li>Pre-trip communication, preparation resources, and coaching.</li>
                    <li>Team devotional guide and commemorative T-shirt.</li>
                  </ul>
                </dd>
              </div>
            </dl>
            <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">
              More Jamaica-specific details are coming together leading up to Thanksgiving—your coordinator will share updates as soon as they’re locked in.
            </p>
          </article>
        </div>
      </section>
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

