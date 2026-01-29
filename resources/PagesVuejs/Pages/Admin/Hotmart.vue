<template>
  <AppLayout title="Administração" subtitle="Configurações de cobrança e Hotmart">
    <Head title="Administração Hotmart" />
    <section class="grid">
      <div class="card span-8">
        <div class="card-head">
          <div>
            <h2>Integração Hotmart</h2>
            <p class="muted">Configure produto, credenciais e webhook para liberar acessos SaaS.</p>
          </div>
        </div>

        <form @submit.prevent="submit">
          <label class="field">
            <span class="label">Ativar Hotmart</span>
            <select v-model="form.is_enabled" class="select">
              <option :value="false">Não</option>
              <option :value="true">Sim</option>
            </select>
          </label>

          <label class="field">
            <span class="label">ID do Produto</span>
            <input v-model="form.product_id" class="input" placeholder="Ex: 123456" />
          </label>

          <label class="field">
            <span class="label">Basic Key</span>
            <input v-model="form.basic_key" class="input" placeholder="Hotmart Basic Key" />
          </label>

          <label class="field">
            <span class="label">Basic Secret</span>
            <input v-model="form.basic_secret" class="input" placeholder="Hotmart Basic Secret" />
          </label>

          <label class="field">
            <span class="label">Webhook Secret</span>
            <input v-model="form.webhook_secret" class="input" placeholder="Segredo do webhook" />
          </label>

          <label class="field">
            <span class="label">Webhook URL</span>
            <input v-model="form.webhook_url" class="input" placeholder="https://seuapp.com/webhooks/hotmart" />
          </label>

          <div class="card" style="margin-top: 16px;">
            <div class="card-head">
              <div>
                <h3>Permissões por assinatura</h3>
                <p class="muted">Controle o que cada plano pode acessar.</p>
              </div>
            </div>

            <label class="field">
              <span class="label">IA habilitada</span>
              <select v-model="form.ai_enabled" class="select">
                <option :value="true">Sim</option>
                <option :value="false">Não</option>
              </select>
            </label>

            <label class="field">
              <span class="label">Importação habilitada</span>
              <select v-model="form.import_enabled" class="select">
                <option :value="true">Sim</option>
                <option :value="false">Não</option>
              </select>
            </label>

            <label class="field">
              <span class="label">Relatórios habilitados</span>
              <select v-model="form.reports_enabled" class="select">
                <option :value="true">Sim</option>
                <option :value="false">Não</option>
              </select>
            </label>

            <label class="field">
              <span class="label">Limite de usuários</span>
              <input v-model="form.max_users" class="input" type="number" min="1" placeholder="Ex: 10" />
            </label>
          </div>

          <div class="actions" style="margin-top: 12px;">
            <button class="btn" type="submit">Salvar configurações</button>
          </div>
        </form>
      </div>

      <div class="card span-4">
        <div class="card-head">
          <div>
            <h2>Checklist</h2>
            <p class="muted">Passos para liberar o SaaS</p>
          </div>
        </div>
        <ul class="list" style="margin: 0; padding-left: 16px;">
          <li>Gerar produto na Hotmart</li>
          <li>Configurar credenciais de API</li>
          <li>Adicionar URL de webhook</li>
          <li>Validar eventos de pagamento</li>
        </ul>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { reactive } from 'vue';
import AppLayout from '../../components/Layouts/AppLayout.vue';

const page = usePage();
const setting = page.props.setting || {};
const subscription = page.props.subscription || {};

const form = reactive({
  is_enabled: setting.is_enabled ?? false,
  product_id: setting.product_id ?? '',
  basic_key: setting.basic_key ?? '',
  basic_secret: setting.basic_secret ?? '',
  webhook_secret: setting.webhook_secret ?? '',
  webhook_url: setting.webhook_url ?? '',
  ai_enabled: subscription.ai_enabled ?? true,
  import_enabled: subscription.import_enabled ?? true,
  reports_enabled: subscription.reports_enabled ?? true,
  max_users: subscription.max_users ?? '',
});

const submit = () => {
  router.post('/admin/hotmart', form, { preserveScroll: true });
};
</script>
