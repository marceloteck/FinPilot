<template>
  <AppLayout title="Dashboard" subtitle="Visão geral do seu mês e plano de ação">
    <Head title="Dashboard" />
    <section class="grid">
      <div class="card span-12">
        <div class="card-head">
          <div>
            <h2>Resumo do mês</h2>
            <p class="muted">Salário vs compromissos • Sobra/Falta • Alertas</p>
          </div>
          <div class="segmented" role="group" aria-label="Modo de visão">
            <button class="seg" :class="{ active: !isYearView }" type="button" @click="setMonthView">Mês</button>
            <button class="seg" :class="{ active: isYearView }" type="button" @click="setYearView">Ano</button>
            <Link class="btn ghost" href="/reports">Relatórios</Link>
          </div>
        </div>

        <div class="kpis">
          <article class="kpi">
            <div class="kpi-label">Entradas do mês</div>
            <div class="kpi-value">{{ formatAmount(summary.income) }}</div>
            <div class="kpi-foot up" :title="formatTooltip(comparison.income_diff, comparison.income_pct)">
              {{ formatDelta(comparison.income_diff) }} {{ comparison.period_label }}
            </div>
          </article>

          <article class="kpi">
            <div class="kpi-label">Gastos do mês</div>
            <div class="kpi-value">{{ formatAmount(summary.expenses) }}</div>
            <div class="kpi-foot" :title="formatTooltip(comparison.expenses_diff, comparison.expenses_pct)">
              {{ formatDelta(comparison.expenses_diff) }} {{ comparison.period_label }}
            </div>
          </article>

          <article class="kpi">
            <div class="kpi-label">Dívidas do mês (mínimos)</div>
            <div class="kpi-value">{{ formatAmount(summary.debt_minimum) }}</div>
            <div class="kpi-foot" :class="summary.commitment_percent >= 80 ? 'warn' : ''" :title="formatTooltip(comparison.debt_diff, comparison.debt_pct)">
              {{ formatDelta(comparison.debt_diff) }} {{ comparison.period_label }}
            </div>
          </article>

          <article class="kpi highlight">
            <div class="kpi-label">Sobra / Falta</div>
            <div class="kpi-value">{{ formatAmount(summary.balance) }}</div>
            <div class="kpi-foot" :title="formatTooltip(comparison.balance_diff, comparison.balance_pct)">
              {{ formatDelta(comparison.balance_diff) }} {{ comparison.period_label }}
            </div>
          </article>
        </div>

        <div class="bar-wrap" aria-label="Barra de comprometimento do salário">
          <div class="bar">
            <div class="bar-fill" :style="{ width: commitmentBar }"></div>
            <div class="bar-meta">
              <span>Comprometido</span>
              <span>{{ summary.commitment_percent ?? 0 }}%</span>
            </div>
          </div>
          <div class="bar-sub">
            <span class="dot debt"></span> Dívidas
            <span class="dot fixed"></span> Fixos
            <span class="dot var"></span> Variáveis
          </div>
          <div class="muted" style="margin-top: 8px;">
            Status: <strong>{{ summary.commitment_status }}</strong>
          </div>
        </div>
      </div>

      <div class="card span-12">
        <div class="card-head">
          <div>
            <h2>Evolução mensal</h2>
            <p class="muted">Entradas, saídas e saldo ao longo do ano</p>
          </div>
        </div>
        <div class="trend-chart">
          <div v-for="item in monthlyTrend" :key="item.month" class="trend-col">
            <div class="trend-bar income" :style="{ height: trendHeight(item.income) }"></div>
            <div class="trend-bar expense" :style="{ height: trendHeight(item.expenses) }"></div>
            <div class="trend-label">{{ item.month }}</div>
          </div>
        </div>
      </div>

      <div class="card span-7">
        <div class="card-head">
          <div>
            <h2>Alertas automáticos</h2>
            <p class="muted">Atenção rápida antes do vencimento</p>
          </div>
        </div>
        <div class="alerts">
          <div v-if="alerts.length === 0" class="alert">
            Nenhum alerta no período atual.
          </div>
          <div v-for="alert in alerts" :key="`${alert.type}-${alert.title}`" class="alert" :class="alert.status">
            <strong>{{ alert.title }}</strong>
            <span v-if="alert.label">• {{ alert.label }}</span>
            <span v-else-if="alert.days !== null">vence em {{ alert.days }} dias</span>
            • mínimo {{ formatAmount(alert.minimum) }}
            <Link class="link" :href="alert.link || '/debts'">Ver</Link>
          </div>
        </div>
      </div>

      <div class="card span-5">
        <div class="card-head">
          <div>
            <h2>Dívidas deste mês</h2>
            <p class="muted">Preview das próximas 3</p>
          </div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Credor</th>
                <th>Tipo</th>
                <th>Venc.</th>
                <th>Mínimo</th>
                <th>Compromet.</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="debts.length === 0">
                <td colspan="5" class="muted">Sem dívidas cadastradas.</td>
              </tr>
              <tr v-for="debt in debts" :key="debt.id">
                <td>{{ debt.creditor || debt.name }}</td>
                <td>{{ debt.type?.name || '—' }}</td>
                <td>
                  {{ formatDueDay(debt.due_day) }}
                  <span v-if="isOverdue(debt)" class="badge warn" style="margin-left: 6px;">Atrasada</span>
                </td>
                <td class="right">{{ formatAmount(debt.monthly_minimum) }}</td>
                <td class="right">{{ debtPercent(debt.monthly_minimum) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="actions">
          <Link class="btn ghost" href="/ai">Gerar plano de pagamento</Link>
          <Link class="btn" href="/debts">Adicionar dívida</Link>
        </div>
      </div>

      <div class="card span-7">
        <div class="card-head">
          <div>
            <h2>Assistente IA</h2>
            <p class="muted">Sugestões de alocação por prioridade</p>
          </div>
        </div>
        <div class="grid">
          <div class="card span-12">
            <div class="card-head">
              <h3>Obrigatório</h3>
              <span class="badge warn">Alta</span>
            </div>
            <p class="muted">Pague mínimos para evitar juros e multas.</p>
            <div class="kpi-value">{{ formatAmount(aiSummary.minimum) }}</div>
          </div>
          <div class="card span-12">
            <div class="card-head">
              <h3>Prioritário</h3>
              <span class="badge">Média</span>
            </div>
            <p class="muted">Direcione o extra para dívidas com maior score.</p>
            <div class="kpi-value">{{ formatAmount(aiSummary.available) }}</div>
          </div>
          <div class="card span-12">
            <div class="card-head">
              <h3>Se sobrar</h3>
              <span class="badge ok">Baixa</span>
            </div>
            <p class="muted">Reserva de emergência e objetivos.</p>
            <div class="kpi-value">{{ formatAmount(aiSummary.overflow) }}</div>
          </div>
        </div>
        <div class="actions">
          <Link class="btn" href="/ai">Confirmar plano</Link>
          <Link class="btn ghost" href="/ai">Ajustar valores</Link>
        </div>
        <div class="mini" style="margin-top: 12px;">
          <div class="mini-row">
            <strong>Plano IA</strong>
            <span class="muted">{{ aiPlan ? formatDate(aiPlan.created_at) : 'Nenhum plano gerado' }}</span>
          </div>
          <div v-if="aiPlan" class="muted">Status: {{ aiPlan.status }}</div>
          <div v-else class="actions" style="margin-top: 8px;">
            <Link class="btn" href="/ai">Gerar plano agora</Link>
          </div>
        </div>
      </div>

      <div class="card span-5">
        <div class="card-head">
          <div>
            <h2>Transações recentes</h2>
            <p class="muted">Últimos 3 lançamentos</p>
          </div>
        </div>
        <div class="list">
          <div v-if="transactions.length === 0" class="muted">Nenhuma transação no período.</div>
          <div v-for="transaction in transactions" :key="transaction.id" class="list-item">
            <div>
              <div class="li-title">{{ transaction.description }}</div>
              <div class="li-sub">
                {{ formatDate(transaction.date) }} • {{ transaction.category?.name || 'Sem categoria' }}
              </div>
            </div>
            <div class="li-right">
              <div class="li-amount" :class="{ plus: transaction.amount >= 0 }">
                {{ formatAmount(transaction.amount) }}
              </div>
              <span class="badge">{{ transaction.status?.name || 'Sem status' }}</span>
            </div>
          </div>
        </div>
        <div class="actions" style="margin-top: 12px;">
          <Link class="btn ghost" href="/transactions">Ver mais transações</Link>
        </div>
      </div>

      <div class="card span-12">
        <div class="card-head">
          <div>
            <h2>Resumo de metas</h2>
            <p class="muted">Em breve você poderá acompanhar metas financeiras.</p>
          </div>
          <div class="card-actions">
            <Link class="btn ghost" href="/goals">Ver metas</Link>
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted } from 'vue';
import AppLayout from '../components/Layouts/AppLayout.vue';
import { getPeriod, setMonth } from '../../js/finpilot/period';

const page = usePage();
const summary = computed(() => page.props.summary ?? { income: 0, expenses: 0, debt_minimum: 0, balance: 0, commitment_percent: null, commitment_status: 'Sem renda' });
const comparison = computed(() => page.props.comparison ?? {
  period_label: '',
  income_diff: 0,
  expenses_diff: 0,
  debt_diff: 0,
  balance_diff: 0,
  income_pct: null,
  expenses_pct: null,
  debt_pct: null,
  balance_pct: null,
});
const monthlyTrend = computed(() => page.props.monthlyTrend ?? []);
const period = computed(() => page.props.period ?? { activeMonth: 'all', activeYear: new Date().getFullYear() });
const debts = computed(() => page.props.debts ?? []);
const transactions = computed(() => page.props.transactions ?? []);
const alerts = computed(() => page.props.alerts ?? []);
const aiPlan = computed(() => page.props.aiPlan ?? null);

const aiSummary = computed(() => aiPlan.value?.summary_json ?? { minimum: 0, available: 0, overflow: 0 });

const isYearView = computed(() => period.value.activeMonth === 'all');

const commitmentBar = computed(() => {
  const percent = summary.value.commitment_percent ?? 0;
  return `${Math.min(100, Math.max(0, percent))}%`;
});

const formatAmount = (value) => {
  const number = Number(value) || 0;
  return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const formatDelta = (value) => {
  const number = Number(value) || 0;
  const signal = number > 0 ? '+' : '';
  return `${signal}${formatAmount(number)}`;
};

const formatTooltip = (diff, pct) => {
  const percent = pct === null || pct === undefined ? '—' : `${pct}%`;
  return `Diferença: ${formatDelta(diff)} (${percent})`;
};

const formatDate = (value) => {
  if (!value) return '';
  return new Date(value).toLocaleDateString('pt-BR');
};

const trendHeight = (value) => {
  const max = Math.max(...monthlyTrend.value.map((item) => item.income || 0), 1);
  const percent = Math.min(100, Math.max(5, (value / max) * 100));
  return `${percent}%`;
};

const debtPercent = (value) => {
  if (!summary.value.income) return '—';
  const percent = (Number(value) / Number(summary.value.income)) * 100;
  return `${percent.toFixed(1)}%`;
};

const formatDueDay = (day) => {
  if (!day) return '—';
  return `${day}`.padStart(2, '0');
};

const isOverdue = (debt) => {
  const activeMonth = period.value.activeMonth;
  const activeYear = period.value.activeYear;
  if (!debt.due_day || activeMonth === 'all') return false;
  const today = new Date();
  const currentMonth = `${today.getMonth() + 1}`.padStart(2, '0');
  if (String(activeYear) !== String(today.getFullYear()) || String(activeMonth) !== currentMonth) {
    return false;
  }
  return Number(debt.due_day) < today.getDate();
};

const onPeriodChange = () => {
  const period = getPeriod();
  router.get('/dashboard', {
    active_year: period.active_year,
    active_month: period.active_month,
  }, { preserveState: true, replace: true });
};

const setYearView = () => {
  setMonth('all');
};

const setMonthView = () => {
  const period = getPeriod();
  if (period.active_month === 'all') {
    const today = new Date();
    setMonth(`${today.getMonth() + 1}`.padStart(2, '0'));
  } else {
    setMonth(period.active_month);
  }
};

onMounted(() => {
  window.addEventListener('finpilot:period-changed', onPeriodChange);
});

onBeforeUnmount(() => {
  window.removeEventListener('finpilot:period-changed', onPeriodChange);
});
</script>

<style scoped>
.trend-chart {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 8px;
  align-items: end;
  min-height: 160px;
}

.trend-col {
  display: grid;
  gap: 4px;
  align-items: end;
}

.trend-bar {
  width: 100%;
  border-radius: 6px 6px 0 0;
}

.trend-bar.income {
  background: rgba(22, 163, 74, 0.4);
}

.trend-bar.expense {
  background: rgba(245, 158, 11, 0.4);
}

.trend-label {
  font-size: 11px;
  text-align: center;
  color: var(--muted);
}
</style>
