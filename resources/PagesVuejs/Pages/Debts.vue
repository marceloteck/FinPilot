<template>
  <AppLayout title="Dívidas" subtitle="Acompanhe vencimentos, impacto no mês e prioridades">
    <Head title="Dívidas" />
    <section class="grid">
      <div class="card span-12">
        <div class="card-head">
          <div>
            <h2>Resumo do mês</h2>
            <p class="muted">Entradas vs mínimos • Comprometimento</p>
          </div>
        </div>
        <div class="kpis">
          <article class="kpi">
            <div class="kpi-label">Entradas do período</div>
            <div class="kpi-value">{{ formatAmount(summary.income) }}</div>
            <div class="kpi-foot">Baseado nas transações</div>
          </article>
          <article class="kpi">
            <div class="kpi-label">Dívidas (mínimos)</div>
            <div class="kpi-value">{{ formatAmount(summary.total_minimum) }}</div>
            <div class="kpi-foot">{{ debtCount }} dívidas ativas</div>
          </article>
          <article class="kpi">
            <div class="kpi-label">Comprometimento</div>
            <div class="kpi-value">
              {{ summary.commitment_percent !== null ? `${summary.commitment_percent}%` : '—' }}
            </div>
            <div class="kpi-foot" :class="summary.commitment_percent >= 80 ? 'warn' : ''">
              {{ summary.commitment_percent === null ? 'Sem renda cadastrada' : 'do salário comprometido' }}
            </div>
          </article>
          <article class="kpi highlight">
            <div class="kpi-label">Sobra / Falta</div>
            <div class="kpi-value">{{ formatAmount(summary.balance_after) }}</div>
            <div class="kpi-foot">após mínimos</div>
          </article>
        </div>

        <div class="actions" style="margin-top: 12px;">
          <button class="btn" type="button" @click="openForm()">Adicionar dívida</button>
          <Link class="btn ghost" href="/ai">Gerar plano do mês</Link>
        </div>

        <div v-if="summary.income === 0" class="alert warn" style="margin-top: 12px;">
          Cadastre uma entrada (salário) para o cálculo de comprometimento ficar correto.
        </div>
      </div>

      <div v-if="debtsByType.length === 0" class="card span-12">
        <div class="empty">
          <p>Nenhuma dívida cadastrada.</p>
          <button class="btn" type="button" @click="openForm()">Adicionar dívida</button>
        </div>
      </div>

      <div v-for="group in debtsByType" :key="group.type.id" class="card span-12">
        <div class="card-head">
          <div>
            <h2>{{ group.type.name }}</h2>
            <p class="muted">{{ group.debts.length }} dívidas</p>
          </div>
        </div>

        <div v-if="group.debts.length === 0" class="muted">Sem dívidas desse tipo.</div>

        <div v-else>
          <div class="table-wrap desktop-only">
            <table class="table">
              <thead>
                <tr>
                  <th>Nome</th>
                  <th>Vencimento</th>
                  <th>Parcela mínima</th>
                  <th>Prioridade</th>
                  <th>Status</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="debt in group.debts" :key="debt.id">
                  <td>
                    <div class="cell-main">{{ debt.name }}</div>
                    <div class="cell-sub">{{ debt.creditor || 'Sem credor' }}</div>
                  </td>
                  <td>Dia {{ debt.due_day }}</td>
                  <td>{{ formatAmount(debt.monthly_minimum) }}</td>
                  <td>
                    <span class="score">{{ debt.priority_score }}</span>
                    <span class="badge" :class="priorityClass(debt.priority_score)">
                      {{ priorityLabel(debt.priority_score) }}
                    </span>
                  </td>
                  <td>{{ formatStatus(debt.status) }}</td>
                  <td>
                    <div class="actions">
                      <button class="btn ghost" type="button" @click="openForm(debt)">Editar</button>
                      <button class="btn" type="button" @click="markPaid(debt)">Pago</button>
                      <button class="btn ghost" type="button" @click="confirmDelete(debt)">Excluir</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="list mobile-only-block">
            <div v-for="debt in group.debts" :key="debt.id" class="list-item">
              <div>
                <div class="li-title">{{ debt.name }}</div>
                <div class="li-sub">
                  Dia {{ debt.due_day }} • {{ formatStatus(debt.status) }}
                </div>
              </div>
              <div class="li-right">
                <div class="li-amount">{{ formatAmount(debt.monthly_minimum) }}</div>
                <span class="badge" :class="priorityClass(debt.priority_score)">
                  {{ priorityLabel(debt.priority_score) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div v-if="showForm" class="modal-backdrop">
      <div class="modal card">
        <div class="card-head">
          <h2>{{ formMode === 'create' ? 'Nova dívida' : 'Editar dívida' }}</h2>
        </div>
        <div class="grid">
          <label class="field span-6">
            <span class="label">Nome</span>
            <input v-model="form.name" class="input" placeholder="Nome da dívida" />
          </label>
          <label class="field span-6">
            <span class="label">Tipo</span>
            <select v-model="form.debt_type_id" class="select">
              <option value="">Selecione</option>
              <option v-for="type in debtTypes" :key="type.id" :value="type.id">
                {{ type.name }}
              </option>
            </select>
          </label>
          <label class="field span-6">
            <span class="label">Credor</span>
            <input v-model="form.creditor" class="input" placeholder="Credor" />
          </label>
          <label class="field span-6">
            <span class="label">Parcela mínima</span>
            <input v-model="form.monthly_minimum" class="input" type="number" step="0.01" />
          </label>
          <label class="field span-6">
            <span class="label">Saldo total (opcional)</span>
            <input v-model="form.total_amount" class="input" type="number" step="0.01" />
          </label>
          <label class="field span-6">
            <span class="label">Dia de vencimento</span>
            <input v-model="form.due_day" class="input" type="number" min="1" max="31" />
          </label>
          <label class="field span-6">
            <span class="label">Status</span>
            <select v-model="form.status" class="select">
              <option value="active">Ativa</option>
              <option value="paid">Paga</option>
              <option value="renegotiating">Renegociando</option>
            </select>
          </label>
          <label class="field span-6">
            <span class="label">Observações</span>
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
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import AppLayout from '../components/Layouts/AppLayout.vue';
import { getPeriod } from '../../js/finpilot/period';

const page = usePage();
const summary = computed(() => page.props.summary ?? { income: 0, total_minimum: 0, commitment_percent: null, balance_after: 0 });
const debtsByType = computed(() => page.props.debtsByType ?? []);
const debtTypes = computed(() => page.props.debtTypes ?? []);

const debtCount = computed(() =>
  debtsByType.value.reduce((total, group) => total + group.debts.length, 0)
);

const showForm = ref(false);
const formMode = ref('create');
const editingId = ref(null);
const form = reactive({
  name: '',
  debt_type_id: '',
  creditor: '',
  monthly_minimum: '',
  total_amount: '',
  due_day: '',
  status: 'active',
  notes: '',
});

const formatAmount = (value) => {
  const number = Number(value) || 0;
  return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const priorityLabel = (score) => {
  if (score >= 80) return 'Alta';
  if (score >= 50) return 'Média';
  return 'Baixa';
};

const priorityClass = (score) => {
  if (score >= 80) return 'warn';
  if (score >= 50) return '';
  return 'ok';
};

const formatStatus = (status) => {
  if (status === 'paid') return 'Paga';
  if (status === 'renegotiating') return 'Renegociando';
  return 'Ativa';
};

const openForm = (debt = null) => {
  showForm.value = true;
  if (debt) {
    formMode.value = 'edit';
    editingId.value = debt.id;
    form.name = debt.name;
    form.debt_type_id = debt.debt_type_id;
    form.creditor = debt.creditor || '';
    form.monthly_minimum = debt.monthly_minimum;
    form.total_amount = debt.total_amount || '';
    form.due_day = debt.due_day;
    form.status = debt.status;
    form.notes = debt.notes || '';
  } else {
    formMode.value = 'create';
    editingId.value = null;
    form.name = '';
    form.debt_type_id = '';
    form.creditor = '';
    form.monthly_minimum = '';
    form.total_amount = '';
    form.due_day = '';
    form.status = 'active';
    form.notes = '';
  }
};

const closeForm = () => {
  showForm.value = false;
};

const submitForm = () => {
  const payload = { ...form };
  if (formMode.value === 'create') {
    router.post('/debts', payload, { preserveScroll: true, onSuccess: closeForm });
  } else if (editingId.value) {
    router.put(`/debts/${editingId.value}`, payload, { preserveScroll: true, onSuccess: closeForm });
  }
};

const payloadFromDebt = (debt, overrides = {}) => ({
  debt_type_id: debt.debt_type_id,
  name: debt.name,
  creditor: debt.creditor,
  total_amount: debt.total_amount,
  monthly_minimum: debt.monthly_minimum,
  interest_rate: debt.interest_rate,
  due_day: debt.due_day,
  start_date: debt.start_date,
  end_date: debt.end_date,
  status: debt.status,
  notes: debt.notes,
  ...overrides,
});

const markPaid = (debt) => {
  router.put(`/debts/${debt.id}`, payloadFromDebt(debt, { status: 'paid' }));
};

const confirmDelete = (debt) => {
  if (!confirm('Deseja excluir esta dívida?')) return;
  router.delete(`/debts/${debt.id}`, { preserveScroll: true });
};

const onPeriodChange = () => {
  const period = getPeriod();
  router.get('/debts', {
    active_year: period.active_year,
    active_month: period.active_month,
  }, { preserveState: true, replace: true });
};

onMounted(() => {
  window.addEventListener('finpilot:period-changed', onPeriodChange);
});

onBeforeUnmount(() => {
  window.removeEventListener('finpilot:period-changed', onPeriodChange);
});
</script>
