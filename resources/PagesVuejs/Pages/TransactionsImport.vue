<template>
  <AppLayout title="Importar transações" subtitle="Envie um CSV com suas entradas e saídas">
    <Head title="Importar transações" />
    <section class="grid">
      <div class="card span-8">
        <div class="card-head">
          <div>
            <h2>Arquivo CSV</h2>
            <p class="muted">Faça upload do arquivo exportado do seu banco.</p>
          </div>
          <div class="card-actions">
            <a class="btn ghost" href="/transactions/import/template">Baixar modelo CSV</a>
          </div>
        </div>
        <form @submit.prevent="submitPreview" class="grid">
          <label class="field span-12">
            <span class="label">Selecione o arquivo</span>
            <input class="input" type="file" accept=".csv,text/csv" @change="onFileChange" />
            <span v-if="form.errors.file" class="muted">{{ form.errors.file }}</span>
          </label>
          <div class="actions span-12">
            <button class="btn" type="submit" :disabled="form.processing">
              {{ form.processing ? 'Processando...' : 'Gerar prévia' }}
            </button>
            <Link class="btn ghost" href="/transactions">Voltar</Link>
          </div>
        </form>
      </div>

      <div class="card span-4">
        <div class="card-head">
          <div>
            <h2>Colunas esperadas</h2>
            <p class="muted">O CSV pode ter cabeçalho.</p>
          </div>
        </div>
        <ul class="list">
          <li v-for="column in expectedColumns" :key="column" class="list-item">
            <div class="li-title">{{ column }}</div>
            <div class="li-sub">Obrigatória apenas se estiver na ordem.</div>
          </li>
        </ul>
        <p class="muted" style="margin-top: 12px;">
          Formato da data: 2024-08-31 ou 31/08/2024.
        </p>
        <div v-if="sampleRows.length" class="mini" style="margin-top: 12px;">
          <strong>Amostra do CSV</strong>
          <div v-for="(row, index) in sampleRows" :key="index" class="muted">
            {{ row.join(', ') }}
          </div>
        </div>
      </div>

      <div v-if="preview.length" class="card span-12">
        <div class="card-head">
          <div>
            <h2>Prévia da importação</h2>
            <p class="muted">Revise as linhas antes de confirmar.</p>
          </div>
          <div class="card-actions">
            <button class="btn" type="button" :disabled="confirmForm.processing || validCount === 0" @click="confirmImport">
              Confirmar importação
            </button>
          </div>
        </div>
        <div class="mini" style="margin-bottom: 12px;">
          <div class="mini-row">
            <strong>{{ validCount }} válidas</strong>
            <span class="muted">{{ invalidCount }} inválidas</span>
          </div>
          <div v-if="confirmForm.processing" class="muted">Processando importação...</div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Conta</th>
                <th>Categoria</th>
                <th>Status</th>
                <th>Notas</th>
                <th>Erros</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in preview" :key="index" :class="{ 'row-invalid': row.errors.length }">
                <td>{{ row.data.date }}</td>
                <td>{{ row.data.description }}</td>
                <td>{{ formatAmount(row.data.amount) }}</td>
                <td>{{ row.data.account_name }}</td>
                <td>{{ row.data.category }}</td>
                <td>{{ row.data.status }}</td>
                <td>{{ row.data.notes }}</td>
                <td>
                  <span v-if="row.errors.length" class="muted">{{ row.errors.join(', ') }}</span>
                  <span v-else class="muted">OK</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../components/Layouts/AppLayout.vue';

const props = defineProps({
  expectedColumns: { type: Array, default: () => [] },
  sampleRows: { type: Array, default: () => [] },
  preview: { type: Array, default: () => [] },
  validCount: { type: Number, default: 0 },
  invalidCount: { type: Number, default: 0 },
});

const form = useForm({
  file: null,
});

const confirmForm = useForm({});

const onFileChange = (event) => {
  const file = event.target.files[0];
  form.file = file;
};

const submitPreview = () => {
  form.post('/transactions/import/preview', {
    forceFormData: true,
  });
};

const confirmImport = () => {
  confirmForm.post('/transactions/import/confirm');
};

const formatAmount = (value) => {
  const number = Number(String(value).replace(',', '.')) || 0;
  return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};
</script>

<style scoped>
.row-invalid {
  background: rgba(245, 158, 11, 0.08);
}
</style>
