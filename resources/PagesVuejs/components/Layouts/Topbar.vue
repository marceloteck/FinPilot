<template>
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-btn mobile-only" type="button" aria-label="Abrir menu">
        ☰
      </button>

      <div class="page-title">
        <h1>{{ title }}</h1>
        <p>{{ subtitle }}</p>
      </div>
    </div>

    <div class="topbar-right">
      <section class="period" aria-label="Período ativo">
        <label class="field">
          <span class="label">Ano</span>
          <select id="periodYearSelect" class="select" aria-label="Selecionar ano">
            <option v-for="year in years" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
        </label>

        <label class="field">
          <span class="label">Mês</span>
          <select id="periodMonthSelect" class="select" aria-label="Selecionar mês">
            <option value="all">Todos</option>
            <option v-for="month in months" :key="month.value" :value="month.value">
              {{ month.label }}
            </option>
          </select>
        </label>

        <button id="periodTodayBtn" class="btn" type="button">Hoje</button>
      </section>

      <div class="actions">
        <button class="btn ghost" type="button">Importar</button>
        <Link class="btn primary" href="/ai">Gerar plano (IA)</Link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    required: true,
  },
});

const currentYear = new Date().getFullYear();
const years = computed(() => {
  const list = [];
  for (let year = currentYear - 2; year <= currentYear + 1; year += 1) {
    list.push(year);
  }
  return list;
});

const months = [
  { value: '01', label: 'Jan' },
  { value: '02', label: 'Fev' },
  { value: '03', label: 'Mar' },
  { value: '04', label: 'Abr' },
  { value: '05', label: 'Mai' },
  { value: '06', label: 'Jun' },
  { value: '07', label: 'Jul' },
  { value: '08', label: 'Ago' },
  { value: '09', label: 'Set' },
  { value: '10', label: 'Out' },
  { value: '11', label: 'Nov' },
  { value: '12', label: 'Dez' },
];
</script>
