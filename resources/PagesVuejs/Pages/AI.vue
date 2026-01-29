<template>
  <AppLayout title="Assistente IA" subtitle="Sugestões com confirmação • aprende com você">
    <Head title="Assistente IA" />

    <section class="grid">
      <div class="card span-7">
        <div class="card-head">
          <div>
            <h2>Resumo do período</h2>
            <p class="muted">Entradas, mínimos e disponível</p>
          </div>
        </div>
        <div class="kpis">
          <article class="kpi">
            <div class="kpi-label">Entradas</div>
            <div class="kpi-value">{{ formatAmount(summary.income) }}</div>
          </article>
          <article class="kpi">
            <div class="kpi-label">Dívidas mínimas</div>
            <div class="kpi-value">{{ formatAmount(summary.minimum) }}</div>
          </article>
          <article class="kpi">
            <div class="kpi-label">Disponível</div>
            <div class="kpi-value">{{ formatAmount(summary.available) }}</div>
          </article>
          <article class="kpi highlight">
            <div class="kpi-label">Comprometimento</div>
            <div class="kpi-value">{{ commitmentPercent }}%</div>
          </article>
        </div>

        <div v-if="summary.income === 0" class="alert warn" style="margin-top: 12px;">
          Cadastre uma entrada (salário) para o plano ficar correto.
        </div>

        <div v-if="period.activeMonth === 'all'" class="alert info" style="margin-top: 12px;">
          Escolha um mês para gerar um plano mensal.
          <button class="link" type="button" @click="setToday">Definir mês atual</button>
        </div>

        <div class="actions" style="margin-top: 12px;">
          <button class="btn" type="button" @click="generatePlan" :disabled="period.activeMonth === 'all'">
            Gerar plano (IA)
          </button>
          <button class="btn ghost" type="button" @click="confirmPlan" :disabled="!plan || plan.status === 'confirmed'">
            Confirmar plano
          </button>
          <button class="btn ghost" type="button" v-if="plan && plan.status === 'confirmed'" @click="generatePlan">
            Gerar novo draft
          </button>
        </div>
      </div>

      <div class="card span-5">
        <div class="card-head">
          <div>
            <h2>Preferências do assistente</h2>
            <p class="muted">Estratégia padrão</p>
          </div>
        </div>
        <label class="field">
          <span class="label">Estratégia</span>
          <select v-model="strategy" class="select" @change="changeStrategy">
            <option value="avalanche">Avalanche</option>
            <option value="snowball">Snowball</option>
          </select>
        </label>
      </div>

      <div class="card span-7">
        <div class="card-head">
          <div>
            <h2>Plano sugerido</h2>
            <p class="muted">Obrigatório • Prioritário • Se sobrar</p>
          </div>
        </div>

        <div v-if="!plan" class="empty">
          <p>Gere um plano para visualizar as recomendações.</p>
        </div>

        <div v-else class="grid">
          <div class="card span-12">
            <div class="card-head">
              <h3>Obrigatório</h3>
              <span class="badge warn">Essencial</span>
            </div>
            <div class="list">
              <div v-for="item in buckets.mandatory" :key="item.id" class="list-item">
                <div>
                  <div class="li-title">{{ item.debt?.name || 'Reserva' }}</div>
                  <div class="li-sub">{{ item.debt?.creditor || 'Mínimo obrigatório' }}</div>
                </div>
                <div class="li-right">
                  <div class="li-amount">{{ formatAmount(item.suggested_amount) }}</div>
                  <button class="btn ghost" type="button" @click="openAdjust(item)">Ajustar</button>
                </div>
              </div>
            </div>
          </div>

          <div class="card span-12">
            <div class="card-head">
              <h3>Prioritário</h3>
              <span class="badge">{{ strategyLabel }}</span>
            </div>
            <div v-if="buckets.priority.length === 0" class="muted">Sem extra disponível.</div>
            <div class="list" v-else>
              <div v-for="item in buckets.priority" :key="item.id" class="list-item">
                <div>
                  <div class="li-title">{{ item.debt?.name || 'Extra' }}</div>
                  <div class="li-sub">{{ reasonText(item) }}</div>
                </div>
                <div class="li-right">
                  <div class="li-amount">{{ formatAmount(item.suggested_amount) }}</div>
                  <button class="btn ghost" type="button" @click="openAdjust(item)">Ajustar</button>
                </div>
              </div>
            </div>
          </div>

          <div class="card span-12">
            <div class="card-head">
              <h3>Se sobrar</h3>
              <span class="badge ok">Reserva</span>
            </div>
            <p class="muted">Reserva de emergência → objetivos → investimento educativo.</p>
            <div v-if="buckets.overflow.length === 0" class="muted">Sem sobra neste período.</div>
            <div v-else class="list">
              <div v-for="item in buckets.overflow" :key="item.id" class="list-item">
                <div>
                  <div class="li-title">Reserva de emergência</div>
                  <div class="li-sub">Priorize liquidez</div>
                </div>
                <div class="li-right">
                  <div class="li-amount plus">{{ formatAmount(item.suggested_amount) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card span-5">
        <div class="card-head">
          <div>
            <h2>Simulação rápida</h2>
            <p class="muted">Tempo estimado para quitar</p>
          </div>
        </div>
        <label class="field">
          <span class="label">Extra mensal</span>
          <input v-model="simulation.extra" class="input" type="number" step="0.01" />
        </label>
        <label class="field">
          <span class="label">Estratégia</span>
          <select v-model="simulation.strategy" class="select">
            <option value="avalanche">Avalanche</option>
            <option value="snowball">Snowball</option>
          </select>
        </label>
        <button class="btn" type="button" @click="runSimulation">Simular</button>
        <div v-if="simulation.result" class="mini">
          <div class="mini-row">
            <span>Dívida que cai primeiro</span>
            <strong>{{ simulation.result.firstDebt }}</strong>
          </div>
          <div class="mini-row">
            <span>Tempo estimado</span>
            <strong>{{ simulation.result.months }} meses</strong>
          </div>
        </div>
      </div>
    </section>

    <div v-if="adjustModal.open" class="modal-backdrop">
      <div class="modal card">
        <div class="card-head">
          <h2>Ajustar sugestão</h2>
        </div>
        <p class="muted">{{ adjustModal.item?.debt?.name || 'Item do plano' }}</p>
        <label class="field">
          <span class="label">Valor confirmado</span>
          <input v-model="adjustModal.amount" class="input" type="number" step="0.01" />
        </label>
        <div class="actions">
          <button class="btn" type="button" @click="applyAdjustment">Confirmar</button>
          <button class="btn ghost" type="button" @click="closeAdjust">Cancelar</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import AppLayout from '../components/Layouts/AppLayout.vue';
import { getPeriod, resetToToday } from '../../js/finpilot/period';

const page = usePage();
const plan = computed(() => page.props.plan ?? null);
const preferences = computed(() => page.props.preferences ?? { payoff_strategy: 'avalanche' });
const period = computed(() => page.props.period ?? { activeYear: new Date().getFullYear(), activeMonth: 'all' });

const summary = computed(() => plan.value?.summary_json ?? {
  income: 0,
  minimum: 0,
  available: 0,
  overflow: 0,
});

const buckets = computed(() => {
  const items = plan.value?.items ?? [];
  return {
    mandatory: items.filter((item) => item.bucket === 'mandatory'),
    priority: items.filter((item) => item.bucket === 'priority'),
    overflow: items.filter((item) => item.bucket === 'overflow'),
  };
});

const commitmentPercent = computed(() => {
  if (!summary.value.income) return 0;
  return Math.round((summary.value.minimum / summary.value.income) * 100);
});

const strategy = ref(preferences.value.payoff_strategy || 'avalanche');
const strategyLabel = computed(() => (strategy.value === 'snowball' ? 'Snowball' : 'Avalanche'));

const simulation = reactive({
  extra: '',
  strategy: 'avalanche',
  result: null,
});

const adjustModal = reactive({
  open: false,
  item: null,
  amount: '',
});

const formatAmount = (value) => {
  const number = Number(value) || 0;
  return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const reasonText = (item) => {
  const reasons = item.reason_json?.reasons || [];
  return reasons.join(' • ') || 'Prioridade calculada';
};

const generatePlan = () => {
  const currentPeriod = getPeriod();
  router.post('/ai/generate', {
    active_year: currentPeriod.active_year,
    active_month: currentPeriod.active_month,
  });
};

const confirmPlan = () => {
  if (!plan.value) return;
  const itemsPayload = (plan.value.items || []).map((item) => ({
    id: item.id,
    confirmed_amount: item.confirmed_amount ?? item.suggested_amount,
  }));
  router.post('/ai/confirm', {
    ai_plan_id: plan.value.id,
    items: itemsPayload,
  });
};

const changeStrategy = () => {
  router.post('/ai/feedback', {
    action: 'change_strategy',
    payload: {
      strategy: strategy.value,
      year: period.value.activeYear,
      month: period.value.activeMonth === 'all' ? null : period.value.activeMonth,
    },
  });
};

const setToday = () => {
  resetToToday();
};

const runSimulation = () => {
  const extra = Number(simulation.extra) || 0;
  const debts = buckets.value.mandatory.map((item) => item.debt).filter(Boolean);
  if (debts.length === 0) {
    simulation.result = { firstDebt: '—', months: 0 };
    return;
  }

  let ordered = debts;
  if (simulation.strategy === 'snowball') {
    ordered = [...debts].sort((a, b) => (a.total_amount || 0) - (b.total_amount || 0));
  } else {
    ordered = [...debts].sort((a, b) => b.priority_score - a.priority_score);
  }

  const target = ordered[0];
  const estimatedTotal = target.total_amount || target.monthly_minimum * 12;
  const months = extra > 0 ? Math.ceil(estimatedTotal / extra) : Math.ceil(estimatedTotal / target.monthly_minimum);

  simulation.result = {
    firstDebt: target.name,
    months: months || 0,
  };
};

const openAdjust = (item) => {
  adjustModal.open = true;
  adjustModal.item = item;
  adjustModal.amount = item.confirmed_amount ?? item.suggested_amount;
};

const closeAdjust = () => {
  adjustModal.open = false;
  adjustModal.item = null;
  adjustModal.amount = '';
};

const applyAdjustment = () => {
  if (!adjustModal.item) return;
  router.post('/ai/feedback', {
    action: 'adjust_amount',
    payload: {
      item_id: adjustModal.item.id,
      confirmed_amount: adjustModal.amount,
      year: period.value.activeYear,
      month: period.value.activeMonth === 'all' ? null : period.value.activeMonth,
    },
  });
  closeAdjust();
};

watch(preferences, (value) => {
  strategy.value = value.payoff_strategy || 'avalanche';
});

const onPeriodChange = () => {
  const currentPeriod = getPeriod();
  router.get('/ai', {
    active_year: currentPeriod.active_year,
    active_month: currentPeriod.active_month,
  }, { preserveState: true, replace: true });
};

onMounted(() => {
  window.addEventListener('finpilot:period-changed', onPeriodChange);
});

onBeforeUnmount(() => {
  window.removeEventListener('finpilot:period-changed', onPeriodChange);
});
</script>
