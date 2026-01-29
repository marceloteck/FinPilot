<template>
  <AppLayout title="Transações" subtitle="Registre, filtre e importe seus gastos e entradas">
    <Head title="Transações" />
    <section class="grid">
      <div class="card span-12">
        <div class="card-head">
          <div>
            <h2>Visões salvas</h2>
            <p class="muted">Aplique filtros e colunas rapidamente</p>
          </div>
          <div class="actions">
            <select class="select" v-model="selectedViewId" @change="applyView">
              <option v-for="view in views" :key="view.id" :value="view.id">
                {{ view.name }}
              </option>
            </select>
            <button class="btn ghost" type="button" @click="startSaveView">Salvar visão</button>
            <button class="btn ghost" type="button" @click="startSaveAs">Salvar como…</button>
            <button
              class="btn"
              type="button"
              :disabled="!activeView || activeView.is_default"
              @click="deleteView"
            >
              Excluir visão
            </button>
          </div>
        </div>

        <div v-if="showSaveView" class="mini">
          <div class="mini-row">
            <div>
              <strong>Salvar visão</strong>
              <div class="muted">Defina um nome para guardar seus filtros atuais.</div>
            </div>
            <div class="actions">
              <input v-model="viewName" class="input" placeholder="Nome da visão" />
              <button class="btn" type="button" @click="saveView">Confirmar</button>
              <button class="btn ghost" type="button" @click="cancelSaveView">Cancelar</button>
            </div>
          </div>
        </div>

        <div class="filters">
          <label class="field">
            <span class="label">Busca</span>
            <input v-model="filters.search" class="input" placeholder="Descrição" />
          </label>
          <label class="field">
            <span class="label">Categoria</span>
            <select v-model="filters.category_id" class="select">
              <option value="">Todas</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </label>
          <label class="field">
            <span class="label">Status</span>
            <select v-model="filters.status_id" class="select">
              <option value="">Todos</option>
              <option v-for="status in statuses" :key="status.id" :value="status.id">
                {{ status.name }}
              </option>
            </select>
          </label>
          <button class="btn" type="button" @click="applyFilters">Filtrar</button>
          <button class="btn ghost" type="button" @click="toggleAdvanced">
            Filtro avançado
          </button>
          <button class="btn ghost" type="button" @click="openForm()">
            + Adicionar
          </button>
        </div>

        <div v-if="showAdvanced" class="grid">
          <div class="card span-12">
            <div class="grid">
              <label class="field span-3">
                <span class="label">Valor mínimo</span>
                <input v-model="filters.min_amount" class="input" type="number" step="0.01" />
              </label>
              <label class="field span-3">
                <span class="label">Valor máximo</span>
                <input v-model="filters.max_amount" class="input" type="number" step="0.01" />
              </label>
              <label class="field span-3">
                <span class="label">Direção</span>
                <select v-model="filters.direction" class="select">
                  <option value="">Todas</option>
                  <option value="income">Entrada</option>
                  <option value="expense">Saída</option>
                </select>
              </label>
              <label class="field span-3">
                <span class="label">Ordenação</span>
                <select v-model="filters.sort_by" class="select">
                  <option value="date">Data</option>
                  <option value="amount">Valor</option>
                  <option value="description">Descrição</option>
                </select>
              </label>
              <label class="field span-3">
                <span class="label">De</span>
                <input v-model="filters.date_from" class="input" type="date" />
              </label>
              <label class="field span-3">
                <span class="label">Até</span>
                <input v-model="filters.date_to" class="input" type="date" />
              </label>
              <label class="field span-3">
                <span class="label">Direção</span>
                <select v-model="filters.sort_dir" class="select">
                  <option value="desc">Desc</option>
                  <option value="asc">Asc</option>
                </select>
              </label>
              <div class="actions span-12">
                <button class="btn" type="button" @click="applyFilters">Aplicar filtros</button>
                <button class="btn ghost" type="button" @click="clearFilters">Limpar filtros</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card span-12">
        <div class="card-head">
          <div>
            <h2>Transações</h2>
            <p class="muted">{{ periodLabel }}</p>
          </div>
        </div>

        <div v-if="transactions.data.length === 0" class="empty">
          <p>Nenhuma transação encontrada para o período atual.</p>
          <div class="actions">
            <button class="btn" type="button" @click="openForm()">Adicionar transação</button>
            <button class="btn ghost" type="button" @click="resetPeriod">Voltar para hoje</button>
            <button v-if="hasFilters" class="btn ghost" type="button" @click="clearFilters">Limpar filtros</button>
          </div>
        </div>

        <div v-else>
          <div class="table-wrap desktop-only">
            <table class="table">
              <thead>
                <tr>
                  <th v-if="columns.date">Data</th>
                  <th v-if="columns.description">Descrição</th>
                  <th v-if="columns.amount">Valor</th>
                  <th v-if="columns.account_name">Conta</th>
                  <th v-if="columns.category">Categoria</th>
                  <th v-if="columns.status">Status</th>
                  <th v-if="columns.notes">Notas</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in transactions.data" :key="item.id">
                  <td v-if="columns.date">{{ formatDate(item.date) }}</td>
                  <td v-if="columns.description">
                    <div class="cell-main">{{ item.description }}</div>
                  </td>
                  <td v-if="columns.amount" :class="{ 'mono': true }">
                    <span :class="{ 'tag ok': item.amount >= 0, 'tag': item.amount < 0 }">
                      {{ formatAmount(item.amount) }}
                    </span>
                  </td>
                  <td v-if="columns.account_name">{{ item.account_name || '—' }}</td>
                  <td v-if="columns.category">{{ item.category?.name || '—' }}</td>
                  <td v-if="columns.status">{{ item.status?.name || '—' }}</td>
                  <td v-if="columns.notes">{{ item.notes || '—' }}</td>
                  <td>
                    <div class="actions">
                      <button class="btn ghost" type="button" @click="openForm(item)">Editar</button>
                      <button class="btn" type="button" @click="confirmDelete(item)">Excluir</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="list mobile-only-block">
            <div v-for="item in transactions.data" :key="item.id" class="list-item">
              <div>
                <div class="li-title">{{ item.description }}</div>
                <div class="li-sub">
                  {{ formatDate(item.date) }} • {{ item.category?.name || 'Sem categoria' }} •
                  {{ item.status?.name || 'Sem status' }}
                </div>
              </div>
              <div class="li-right">
                <div class="li-amount" :class="{ plus: item.amount >= 0 }">
                  {{ formatAmount(item.amount) }}
                </div>
                <button class="btn ghost" type="button" @click="openForm(item)">Editar</button>
              </div>
            </div>
          </div>

          <div class="pagination">
            <button
              class="btn ghost"
              type="button"
              :disabled="!transactions.prev_page_url"
              @click="goTo(transactions.prev_page_url)"
            >
              Anterior
            </button>
            <span class="muted">Página {{ transactions.current_page }} de {{ transactions.last_page }}</span>
            <button
              class="btn ghost"
              type="button"
              :disabled="!transactions.next_page_url"
              @click="goTo(transactions.next_page_url)"
            >
              Próxima
            </button>
          </div>
        </div>
      </div>
    </section>

    <div v-if="showForm" class="modal-backdrop">
      <div class="modal card">
        <div class="card-head">
          <h2>{{ formMode === 'create' ? 'Nova transação' : 'Editar transação' }}</h2>
        </div>
        <div class="grid">
          <label class="field span-6">
            <span class="label">Data</span>
            <input v-model="form.date" class="input" type="date" />
          </label>
          <label class="field span-6">
            <span class="label">Descrição</span>
            <input v-model="form.description" class="input" placeholder="Descrição" />
          </label>
          <label class="field span-4">
            <span class="label">Valor</span>
            <input v-model="form.amount" class="input" type="number" step="0.01" />
          </label>
          <label class="field span-4">
            <span class="label">Categoria</span>
            <select v-model="form.category_id" class="select">
              <option value="">Sem categoria</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </label>
          <label class="field span-4">
            <span class="label">Status</span>
            <select v-model="form.status_id" class="select">
              <option value="">Sem status</option>
              <option v-for="status in statuses" :key="status.id" :value="status.id">
                {{ status.name }}
              </option>
            </select>
          </label>
          <label class="field span-6">
            <span class="label">Conta</span>
            <input v-model="form.account_name" class="input" placeholder="Conta" />
          </label>
          <label class="field span-6">
            <span class="label">Notas</span>
            <input v-model="form.notes" class="input" placeholder="Notas" />
          </label>
        </div>
        <div class="actions">
          <button class="btn" type="button" @click="submitForm">Salvar</button>
          <button class="btn ghost" type="button" @click="closeForm">Cancelar</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../components/Layouts/AppLayout.vue';
import { getPeriod, resetToToday } from '../../js/finpilot/period';

const page = usePage();

const transactions = computed(() => page.props.transactions);
const categories = computed(() => page.props.categories ?? []);
const statuses = computed(() => page.props.statuses ?? []);
const views = computed(() => page.props.views ?? []);
const activeView = computed(() => page.props.activeView ?? null);

const selectedViewId = ref(activeView.value?.id ?? (views.value[0]?.id ?? ''));

const filters = reactive({
  search: page.props.filters?.search ?? '',
  category_id: page.props.filters?.category_id ?? '',
  status_id: page.props.filters?.status_id ?? '',
  min_amount: page.props.filters?.min_amount ?? '',
  max_amount: page.props.filters?.max_amount ?? '',
  date_from: page.props.filters?.date_from ?? '',
  date_to: page.props.filters?.date_to ?? '',
  direction: page.props.filters?.direction ?? '',
  sort_by: page.props.filters?.sort_by ?? 'date',
  sort_dir: page.props.filters?.sort_dir ?? 'desc',
});

const columns = computed(() => {
  const config = activeView.value?.config_json?.columns ?? {
    date: true,
    description: true,
    amount: true,
    account_name: true,
    category: true,
    status: true,
    notes: false,
  };
  return config;
});

const showAdvanced = ref(false);
const showForm = ref(false);
const formMode = ref('create');
const editingId = ref(null);
const form = reactive({
  date: '',
  description: '',
  amount: '',
  account_name: '',
  category_id: '',
  status_id: '',
  notes: '',
});

const showSaveView = ref(false);
const saveMode = ref('update');
const viewName = ref('');

const periodLabel = computed(() => {
  const period = page.props.period ?? {};
  if (period.activeMonth && period.activeMonth !== 'all') {
    return `Período: ${period.activeMonth}/${period.activeYear}`;
  }
  return `Período: ${period.activeYear}`;
});

const hasFilters = computed(() => {
  return Boolean(
    filters.search ||
      filters.category_id ||
      filters.status_id ||
      filters.min_amount ||
      filters.max_amount ||
      filters.date_from ||
      filters.date_to ||
      filters.direction
  );
});

const applyFilters = () => {
  const period = getPeriod();
  router.get(
    '/transactions',
    {
      ...filters,
      view_id: selectedViewId.value,
      active_year: period.active_year,
      active_month: period.active_month,
    },
    { preserveState: true, replace: true }
  );
};

const syncFiltersFromView = (view) => {
  if (!view || !view.config_json) return;
  const configFilters = view.config_json.filters ?? {};
  filters.search = configFilters.search ?? '';
  filters.category_id = configFilters.category_id ?? '';
  filters.status_id = configFilters.status_id ?? '';
  filters.min_amount = configFilters.min_amount ?? '';
  filters.max_amount = configFilters.max_amount ?? '';
  filters.date_from = configFilters.date_from ?? '';
  filters.date_to = configFilters.date_to ?? '';
  filters.direction = configFilters.direction ?? '';

  const configSort = view.config_json.sort ?? {};
  filters.sort_by = configSort.by ?? 'date';
  filters.sort_dir = configSort.dir ?? 'desc';
};

const applyView = () => {
  const view = views.value.find((item) => item.id === Number(selectedViewId.value));
  syncFiltersFromView(view);
  applyFilters();
};

const toggleAdvanced = () => {
  showAdvanced.value = !showAdvanced.value;
};

const clearFilters = () => {
  filters.search = '';
  filters.category_id = '';
  filters.status_id = '';
  filters.min_amount = '';
  filters.max_amount = '';
  filters.date_from = '';
  filters.date_to = '';
  filters.direction = '';
  filters.sort_by = 'date';
  filters.sort_dir = 'desc';
  applyFilters();
};

const openForm = (item = null) => {
  showForm.value = true;
  if (item) {
    formMode.value = 'edit';
    editingId.value = item.id;
    form.date = item.date;
    form.description = item.description;
    form.amount = item.amount;
    form.account_name = item.account_name || '';
    form.category_id = item.category_id || '';
    form.status_id = item.status_id || '';
    form.notes = item.notes || '';
  } else {
    formMode.value = 'create';
    editingId.value = null;
    form.date = '';
    form.description = '';
    form.amount = '';
    form.account_name = '';
    form.category_id = '';
    form.status_id = '';
    form.notes = '';
  }
};

const closeForm = () => {
  showForm.value = false;
};

const submitForm = () => {
  const payload = { ...form };
  if (formMode.value === 'create') {
    router.post('/transactions', payload, { preserveScroll: true, onSuccess: closeForm });
  } else if (editingId.value) {
    router.put(`/transactions/${editingId.value}`, payload, { preserveScroll: true, onSuccess: closeForm });
  }
};

const confirmDelete = (item) => {
  if (!confirm('Deseja excluir esta transação?')) return;
  router.delete(`/transactions/${item.id}`, { preserveScroll: true });
};

const goTo = (url) => {
  if (!url) return;
  router.get(url, {}, { preserveState: true, replace: true });
};

const startSaveView = () => {
  if (!activeView.value) return;
  showSaveView.value = true;
  saveMode.value = 'update';
  viewName.value = activeView.value.name;
};

const startSaveAs = () => {
  showSaveView.value = true;
  saveMode.value = 'create';
  viewName.value = '';
};

const cancelSaveView = () => {
  showSaveView.value = false;
  viewName.value = '';
};

const saveView = () => {
  const payload = {
    entity: 'transactions',
    name: viewName.value,
    config_json: {
      filters: {
        search: filters.search || '',
        category_id: filters.category_id || null,
        status_id: filters.status_id || null,
        min_amount: filters.min_amount || null,
        max_amount: filters.max_amount || null,
        date_from: filters.date_from || null,
        date_to: filters.date_to || null,
        direction: filters.direction || null,
      },
      sort: {
        by: filters.sort_by,
        dir: filters.sort_dir,
      },
      columns: columns.value,
    },
  };

  if (saveMode.value === 'update' && activeView.value) {
    router.put(`/views/${activeView.value.id}`, { ...payload, name: viewName.value }, { onSuccess: cancelSaveView });
  } else {
    router.post('/views', payload, { onSuccess: cancelSaveView });
  }
};

const deleteView = () => {
  if (!activeView.value || activeView.value.is_default) return;
  if (!confirm('Deseja excluir esta visão?')) return;
  router.delete(`/views/${activeView.value.id}`);
};

const formatAmount = (value) => {
  const number = Number(value) || 0;
  return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const formatDate = (value) => {
  if (!value) return '';
  const date = new Date(value);
  return date.toLocaleDateString('pt-BR');
};

const resetPeriod = () => {
  resetToToday();
};

const onPeriodChange = () => {
  applyFilters();
};

watch(activeView, (value) => {
  selectedViewId.value = value?.id ?? (views.value[0]?.id ?? '');
  if (value) {
    syncFiltersFromView(value);
  }
});

onMounted(() => {
  window.addEventListener('finpilot:period-changed', onPeriodChange);
});

onBeforeUnmount(() => {
  window.removeEventListener('finpilot:period-changed', onPeriodChange);
});
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: grid;
  place-items: center;
  padding: 16px;
  z-index: 50;
}

.modal {
  width: min(720px, 100%);
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 16px;
}
</style>
