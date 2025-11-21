<template>
  <div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 lg:p-8">
      <header class="mb-6">
        <p class="text-xs uppercase tracking-[0.35em] text-slate-500">
          Mission Intake
        </p>
        <div class="mt-2 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
          <h2 class="text-xl font-semibold text-slate-900">
            Group Readiness Questionnaire
          </h2>
          <span class="rounded-full bg-emerald-50 px-4 py-1.5 text-sm font-medium text-emerald-700 shadow-sm">
            {{ progressSummary }}
          </span>
        </div>
      </header>

      <div class="relative mb-8 h-2 overflow-hidden rounded-full bg-slate-100">
        <div
          class="absolute inset-y-0 left-0 rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-emerald-500 transition-all duration-500 ease-out"
          :style="{ width: `${progressPercent}%` }"
        ></div>
      </div>

      <p v-if="errorMessage" class="mb-4 rounded border border-rose-200 bg-rose-50 px-4 py-2 text-sm text-rose-700">
        {{ errorMessage }}
      </p>

      <!-- Submitted state -------------------------------------------------------------->
      <section v-if="submitted" class="space-y-4">
        <header class="rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-5 text-white shadow-lg shadow-emerald-500/40">
          <p class="text-xs uppercase tracking-[0.35em] text-emerald-100">Application received</p>
          <h3 class="mt-2 text-2xl font-semibold">Thank you for applying!</h3>
          <p class="mt-3 text-sm leading-relaxed text-emerald-50">
            {{ result.message || FINAL_MESSAGE }}
          </p>
        </header>

        <!-- Signup link for Green status only (status/score hidden from applicants) -->
        <div v-if="result.signup_link" class="mt-4">
          <a
            class="inline-flex rounded-full bg-white px-4 py-2 text-sm font-medium text-emerald-700 shadow-sm ring-1 ring-emerald-500 hover:bg-emerald-50"
            :href="result.signup_link"
            target="_blank"
            rel="noreferrer"
          >
            Reserve your trip
          </a>
        </div>

        <div class="flex justify-end">
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-700 hover:border-emerald-200 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:ring-offset-2"
            @click="resetAll"
          >
            Start again
          </button>
        </div>
      </section>

      <!-- Basic info step ------------------------------------------------------------->
      <section v-else-if="step === 0" class="space-y-6">
        <header>
          <h3 class="text-lg font-semibold text-slate-900">
            Let’s start with the basics
          </h3>
          <p class="mt-2 text-sm text-slate-600">
            Provide your contact information so we can stay connected throughout the process.
          </p>
        </header>

        <div class="grid gap-4 lg:grid-cols-2">
          <label class="block text-sm font-medium text-slate-700">
            Full name <span class="text-emerald-500">*</span>
            <input
              v-model="form.name"
              type="text"
              autocomplete="name"
              placeholder="Jamie Rodriguez"
              class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
            />
          </label>

          <label class="block text-sm font-medium text-slate-700">
            Email <span class="text-emerald-500">*</span>
            <input
              v-model="form.email"
              type="email"
              autocomplete="email"
              placeholder="jamie@email.com"
              class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
            />
          </label>

          <label class="block text-sm font-medium text-slate-700">
            Phone
            <input
              v-model="form.phone"
              type="tel"
              autocomplete="tel"
              placeholder="(555) 555-5555"
              class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
            />
          </label>

          <label class="block text-sm font-medium text-slate-700">
            Group leader role
            <input
              v-model="form.group_leader_role"
              type="text"
              placeholder="Youth Pastor"
              class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
            />
          </label>

          <label class="block text-sm font-medium text-slate-700">
            Time in this role (years)
            <input
              v-model.number="form.role_duration"
              type="number"
              step="0.1"
              min="0"
              placeholder="1.5"
              class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
            />
          </label>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2"
            @click="startQuestions"
          >
            Start questions
          </button>
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-700 hover:border-emerald-200 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:ring-offset-2"
            @click="resetAll"
          >
            Clear form
          </button>
        </div>
      </section>

      <!-- Question groups -------------------------------------------------------------->
      <section v-else class="space-y-6">
        <header class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-5 py-4 text-emerald-800">
          <p class="font-medium leading-relaxed">{{ currentStepPrompt }}</p>
        </header>

        <div
          v-if="liveFlags.length"
          class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-amber-900"
        >
          <h4 class="text-sm font-semibold uppercase tracking-wide">Heads-up</h4>
          <ul class="mt-1 list-disc space-y-1 pl-5 text-sm">
            <li v-for="flag in liveFlags" :key="flag.key">{{ flag.label }}</li>
          </ul>
        </div>

        <div class="space-y-5">
          <article
            v-for="question in currentQuestions"
            :key="question.id"
            class="rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg"
          >
            <!-- Marriage & Sexuality Statement -->
            <div v-if="question.key === 'view_of_marriage'" class="mb-4 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm leading-relaxed text-slate-700">
              <p class="mb-3 font-semibold text-slate-900">At Adventures in Missions, we hold to a Biblical view of marriage and sexuality.</p>
              <p class="mb-2">We believe that God's design for marriage is a covenant relationship between one man and one woman, and that sexuality is intended to be expressed within that covenant.</p>
              <p class="mb-2">While we serve people from many different backgrounds and perspectives, our first responsibility is to honor God, uphold His Word, and steward well the trust of our ministry partners, hosts, and participants. For this reason, not everyone will be a fit for our programs. Out of respect for the communities we serve and the unity of our teams, we cannot take every applicant onto the mission field.</p>
              <p>We ask that all participants, regardless of personal beliefs, submit to the authority of our leadership for the duration of the program. This includes refraining from all romantic relationships during the trip. The purpose of these guidelines is to ensure that the focus remains on Christ, discipleship, and serving others.</p>
            </div>

            <label class="block text-sm font-semibold text-slate-900 leading-relaxed">
              {{ question.text }}
            </label>

            <div class="mt-3 space-y-2">
              <select
                v-if="isBooleanQuestion(question)"
                v-model="answers[questionKey(question)]"
                class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
              >
                <option value="">Select…</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
              </select>

              <select
                v-else-if="isComfortQuestion(question)"
                v-model="answers[questionKey(question)]"
                class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
              >
                <option value="">Select…</option>
                <option value="very_comfortable">Very comfortable</option>
                <option value="comfortable">Comfortable</option>
                <option value="somewhat_uncomfortable">Somewhat uncomfortable</option>
                <option value="uncomfortable">Uncomfortable</option>
              </select>

              <input
                v-else-if="isNumericQuestion(question)"
                v-model.number="answers[questionKey(question)]"
                type="number"
                min="0"
                class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
              />

              <textarea
                v-else-if="isTextareaQuestion(question)"
                v-model="answers[questionKey(question)]"
                rows="4"
                class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
              ></textarea>

              <input
                v-else
                v-model="answers[questionKey(question)]"
                type="text"
                class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
              />

              <p v-if="question.hint" class="text-xs text-slate-500">
                {{ question.hint }}
              </p>
            </div>
          </article>
        </div>

        <footer class="flex flex-wrap gap-3">
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-700 hover:border-emerald-200 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:ring-offset-2"
            @click="previousStep"
          >
            Back
          </button>

          <button
            type="button"
            class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2"
            @click="advanceStep"
          >
            {{ isLastStep ? 'Submit application' : 'Finish section' }}
          </button>
        </footer>
      </section>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';

const FLOW_STEPS = [
  {
    id: 1,
    intro: "Great! You’re on your way. Now, let’s get through a few questions to help us understand your needs. It shouldn’t take you too long.",
    outro: "Thanks! This is perfect. Now we need to get a feel for your missions experience.",
  },
  {
    id: 2,
    intro: "Thanks! This is perfect. Now we need to get a feel for your missions experience.",
    outro: "Awesome! Now we have a few questions about your group. This will help us as we consider the quality of the fit for this location.",
  },
  {
    id: 3,
    intro: "Awesome! Now we have a few questions about your group. This will help us as we consider the quality of the fit for this location.",
    outro: "Thanks! We just have two more questions.",
  },
  {
    id: 4,
    intro: "Thanks! We just have two more questions.",
    outro: null,
  },
];

const FINAL_MESSAGE =
  "Thank you for applying for this trip. We appreciate your thoroughness! It really helps us as we help you organize the best trip possible! One of our Missions Coordinators will be in touch with you within the next 48 hours. In the meantime, if you have any questions feel free to send us an email at stm@adventures.org.";

const BASIC_STEP_ID = 0;
const step = ref(BASIC_STEP_ID);
const questions = ref([]);
const answers = reactive({});

const form = reactive({
  name: '',
  email: '',
  phone: '',
  group_leader_role: '',
  role_duration: null,
});

const submitted = ref(false);
const result = reactive({});
const errorMessage = ref('');

const stepOrder = FLOW_STEPS.map((entry) => entry.id);

const groupedQuestions = computed(() => {
  const ordered = [...questions.value].sort((a, b) => {
    const aOrder = a.order ?? a.position ?? a.sequence ?? a.id;
    const bOrder = b.order ?? b.position ?? b.sequence ?? b.id;
    return aOrder - bOrder;
  });

  const byGroup = new Map();
  ordered.forEach((question) => {
    const groupId = Number.parseInt(question.group, 10);
    if (!Number.isNaN(groupId) && stepOrder.includes(groupId)) {
      if (!byGroup.has(groupId)) byGroup.set(groupId, []);
      byGroup.get(groupId).push(question);
    }
  });

  let cursor = 0;
  return FLOW_STEPS.map((config, index) => {
    const explicit = byGroup.get(config.id);
    if (explicit?.length) {
      return { ...config, questions: explicit };
    }

    const remaining = ordered.slice(cursor);
    const take = index === FLOW_STEPS.length - 1 ? remaining.length : Math.min(remaining.length, 3);
    const fallback = ordered.slice(cursor, cursor + take);
    cursor += take;
    return { ...config, questions: fallback };
  });
});

const currentGroup = computed(() => {
  if (step.value === BASIC_STEP_ID) return null;
  return groupedQuestions.value.find((group) => group.id === step.value) ?? groupedQuestions.value[0];
});

const currentQuestions = computed(() => currentGroup.value?.questions ?? []);
const currentStepPrompt = computed(() => currentGroup.value?.intro ?? '');
const isLastStep = computed(() => step.value === FLOW_STEPS[FLOW_STEPS.length - 1].id);

const totalSteps = computed(() => FLOW_STEPS.length + 1); // + basic info

const completedSteps = computed(() => {
  let count = form.name && form.email ? 1 : 0;
  groupedQuestions.value.forEach((group) => {
    if (!group.questions.length) return;
    const done = group.questions.every((question) => {
      const value = answers[questionKey(question)];
      return value !== '' && value !== null && typeof value !== 'undefined';
    });
    if (done) count += 1;
  });
  return count;
});

const progressPercent = computed(() => Math.round((completedSteps.value / totalSteps.value) * 100));
const progressSummary = computed(() => `${completedSteps.value}/${totalSteps.value} completed (${progressPercent.value}%)`);

const liveFlags = computed(() => {
  const flags = [];
  const prayer = (answers.prayer_comfort ?? answers.praying ?? '').toString().toLowerCase();
  if (['uncomfortable', 'somewhat_uncomfortable', 'no'].includes(prayer)) {
    flags.push({ key: 'prayer', label: 'Leader is uncomfortable praying in public' });
  }
  const sharing = (answers.share_faith ?? answers.faith_sharing ?? '').toString().toLowerCase();
  if (['never', 'no', 'not yet'].includes(sharing)) {
    flags.push({ key: 'sharing', label: 'Leader has never shared their faith' });
  }
  if (form.role_duration !== null && Number(form.role_duration) < 1) {
    flags.push({ key: 'role', label: 'Leader has been in their role less than a year' });
  }
  return flags;
});

onMounted(async () => {
  restoreFromStorage();
  await fetchQuestions();
});

watch(
  [form, answers, step],
  () => {
    const snapshot = {
      form: { ...form },
      answers: { ...answers },
      step: step.value,
    };
    localStorage.setItem('mission_form_v1', JSON.stringify(snapshot));
  },
  { deep: true },
);

async function fetchQuestions() {
  try {
    const response = await axios.get('/api/questions');
    const payload = Array.isArray(response.data) ? response.data : [];
    questions.value = payload.map((item) => ({
      id: item.id,
      text: item.text,
      group: item.group,
      key: item.key ?? null,
      type: item.type ?? null,
      hint: item.hint ?? null,
      options: item.options ?? null,
    }));
    initialiseAnswerKeys();
  } catch (error) {
    console.error('Unable to load questions', error);
    errorMessage.value = 'Unable to load questions at the moment. Please refresh and try again.';
  }
}

function initialiseAnswerKeys() {
  groupedQuestions.value.flatMap((group) => group.questions).forEach((question) => {
    const key = questionKey(question);
    if (!(key in answers)) answers[key] = '';
  });
}

function restoreFromStorage() {
  const saved = localStorage.getItem('mission_form_v1');
  if (!saved) return;
  try {
    const parsed = JSON.parse(saved);
    Object.assign(form, parsed.form ?? {});
    Object.assign(answers, parsed.answers ?? {});
    step.value = parsed.step ?? BASIC_STEP_ID;
  } catch (error) {
    console.warn('Unable to restore saved state', error);
  }
}

function resetAll() {
  Object.assign(form, {
    name: '',
    email: '',
    phone: '',
    group_leader_role: '',
    role_duration: null,
  });
  Object.keys(answers).forEach((key) => delete answers[key]);
  initialiseAnswerKeys();
  Object.keys(result).forEach((key) => delete result[key]);
  submitted.value = false;
  step.value = BASIC_STEP_ID;
  errorMessage.value = '';
  localStorage.removeItem('mission_form_v1');
}

function startQuestions() {
  if (!form.name || !form.email) {
    errorMessage.value = 'Please provide your name and email before continuing.';
    return;
  }
  errorMessage.value = '';
  step.value = FLOW_STEPS[0].id;
}

function previousStep() {
  if (step.value === BASIC_STEP_ID) return;
  const index = stepOrder.indexOf(step.value);
  if (index <= 0) {
    step.value = BASIC_STEP_ID;
  } else {
    step.value = stepOrder[index - 1];
  }
  errorMessage.value = '';
}

function advanceStep() {
  if (step.value === BASIC_STEP_ID) {
    startQuestions();
    return;
  }

  if (!validateCurrentGroup()) {
    errorMessage.value = 'Please answer every question in this section before continuing.';
    return;
  }

  const index = stepOrder.indexOf(step.value);
  const next = stepOrder[index + 1];
  if (next) {
    step.value = next;
    errorMessage.value = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  submitForm();
}

function validateCurrentGroup() {
  return currentQuestions.value.every((question) => {
    const value = answers[questionKey(question)];
    return value !== '' && value !== null && typeof value !== 'undefined';
  });
}

async function submitForm() {
  const payloadAnswers = {};
  groupedQuestions.value.flatMap((group) => group.questions).forEach((question) => {
    const key = questionKey(question);
    const value = answers[key] ?? '';
    payloadAnswers[key] = value;
    payloadAnswers[String(question.id)] = value;
  });

  const payload = {
    name: form.name,
    email: form.email,
    phone: form.phone,
    group_leader_role: form.group_leader_role,
    role_duration: form.role_duration,
    answers: payloadAnswers,
  };

  try {
    const response = await axios.post('/api/inquiries', payload);
    Object.assign(result, response.data ?? {});
    if (!result.message) result.message = FINAL_MESSAGE;
    submitted.value = true;
    errorMessage.value = '';
    localStorage.removeItem('mission_form_v1');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (error) {
    console.error('Submit failed', error);
    errorMessage.value = error.response?.data?.message || 'Unable to submit right now. Please try again.';
  }
}

function questionKey(question) {
  const text = (question.text || '').toLowerCase();
  if (question.key) {
    return String(question.key);
  }
  const dictionary = [
    { terms: ['group or individual', 'inquiring on behalf'], key: 'group_context' },
    { terms: ['church affiliation'], key: 'church_affiliation' },
    { terms: ['organization are you affiliated'], key: 'church_affiliation' },
    { terms: ['mission trip before', 'first mission trip'], key: 'leader_first_trip' },
    { terms: ['identify as lgbtq', 'lgbtq+'], key: 'leader_lgbtq' },
    { terms: ['pray', 'prayer'], key: 'prayer_comfort' },
    { terms: ['share your faith', 'shared their faith'], key: 'faith_sharing' },
    { terms: ['how many people', 'group composition', 'age range'], key: 'group_composition' },
    { terms: ['purpose of trip'], key: 'trip_purpose' },
    { terms: ['marriage', 'sexuality'], key: 'view_of_marriage' },
    { terms: ['spiritual maturity', 'team readiness'], key: 'team_readiness' },
    { terms: ['challenges or concerns'], key: 'challenges' },
    { terms: ['statement of faith', 'statement of beliefs'], key: 'statement_of_faith' },
  ];
  for (const entry of dictionary) {
    if (entry.terms.some((term) => text.includes(term))) {
      return entry.key;
    }
  }
  return String(question.id);
}

function isBooleanQuestion(question) {
  if (question.type === 'boolean') return true;
  const text = (question.text || '').toLowerCase();
  return ['have you', 'are you', 'do you', 'did you', 'is your'].some((prefix) => text.startsWith(prefix));
}

function isComfortQuestion(question) {
  if (question.type === 'comfort_scale') return true;
  const text = (question.text || '').toLowerCase();
  return ['comfort', 'pray', 'spiritual', 'share your faith'].some((term) => text.includes(term));
}

function isNumericQuestion(question) {
  if (question.type === 'number') return true;
  const text = (question.text || '').toLowerCase();
  return ['how many', 'age range', 'average age', 'size of your group'].some((term) => text.includes(term));
}

function isTextareaQuestion(question) {
  if (question.type === 'textarea') return true;
  const text = (question.text || '').toLowerCase();
  return ['marriage', 'sexuality', 'challenges', 'concerns', 'hesitations'].some((term) => text.includes(term));
}

function formatFlagKey(key) {
  if (!key) return '';
  // Convert snake_case or kebab-case to Title Case
  return key
    .replace(/[_-]/g, ' ')
    .replace(/\b\w/g, (char) => char.toUpperCase());
}
</script>

<style scoped>
input,
select,
textarea {
  color: #111827; /* slate-900 */
}

input::placeholder,
textarea::placeholder {
  color: #6b7280; /* slate-500 */
}
</style>

