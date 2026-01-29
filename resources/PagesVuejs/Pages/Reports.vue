<template>
  <AppLayout title="Relatórios" subtitle="Anual e mensal • evolução e tendências">
    <Head title="Relatórios" />
    <section class="grid">
      <div class="card span-12">
        <div class="card-head">
          <div>
            <h2>Resumo anual</h2>
            <p class="muted">Entradas, gastos e dívidas no ano</p>
          </div>
        </div>
        <div class="kpis">
          <article class="kpi">
            <div class="kpi-label">Entradas</div>
            <div class="kpi-value">{{ formatAmount(summary.income) }}</div>
          </article>
          <article class="kpi">
            <div class="kpi-label">Gastos</div>
            <div class="kpi-value">{{ formatAmount(summary.expenses) }}</div>
          </article>
          <article class="kpi">
            <div class="kpi-label">Dívidas (mínimos)</div>
            <div class="kpi-value">{{ formatAmount(summary.debts) }}</div>
          </article>
          <article class="kpi highlight">
            <div class="kpi-label">Sobra / Falta</div>
            <div class="kpi-value">{{ formatAmount(summary.balance) }}</div>
          </article>
        </div>
      </div>

      <div class="card span-12">
        <div class="card-head">
          <div>
            <h2>Mês a mês</h2>
            <p class="muted">Evolução mensal do ano ativo</p>
          </div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Mês</th>
                <th>Entradas</th>
                <th>Gastos</th>
                <th>Dívidas</th>
                <th>Sobra/Falta</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in monthly" :key="row.month">
                <td>{{ row.month }}</td>
                <td>{{ formatAmount(row.income) }}</td>
                <td>{{ formatAmount(row.expenses) }}</td>
                <td>{{ formatAmount(row.debts) }}</td>
                <td>{{ formatAmount(row.balance) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted } from 'vue';
import AppLayout from '../components/Layouts/AppLayout.vue';
import { getPeriod } from '../../js/finpilot/period';

const props = defineProps({
  summary: { type: Object, required: true },
  monthly: { type: Array, required: true },
});

const formatAmount = (value) => {
  const number = Number(value) || 0;
  return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const onPeriodChange = () => {
  const period = getPeriod();
  router.get('/reports', { active_year: period.active_year }, { preserveState: true, replace: true });
};

onMounted(() => {
  window.addEventListener('finpilot:period-changed', onPeriodChange);
});

onBeforeUnmount(() => {
  window.removeEventListener('finpilot:period-changed', onPeriodChange);
});
</script>
