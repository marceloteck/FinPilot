<template>
  <div class="auth-shell">
    <Head title="Recuperar senha" />
    <div class="auth-card">
      <div class="auth-brand">
        <div class="auth-badge">FP</div>
        FinPilot
      </div>

      <div>
        <h1 class="auth-title">Esqueci minha senha</h1>
        <p class="auth-subtitle">Vamos enviar um link para redefinir sua senha.</p>
      </div>

      <div v-if="status" class="auth-alert">{{ status }}</div>

      <form class="auth-form" @submit.prevent="submit">
        <label class="auth-field">
          <span class="auth-label">Email</span>
          <input v-model="form.email" class="auth-input" type="email" autocomplete="username" />
          <span v-if="form.errors.email" class="auth-error">{{ form.errors.email }}</span>
        </label>

        <div class="auth-actions">
          <button class="auth-button" type="submit" :disabled="form.processing">Enviar link</button>
          <Link class="auth-link" :href="route('login')">Voltar ao login</Link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  status: {
    type: String,
    default: '',
  },
});

const form = useForm({
  email: '',
});

const submit = () => {
  form.post(route('password.email'));
};
</script>
