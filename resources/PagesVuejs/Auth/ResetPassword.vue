<template>
  <div class="auth-shell">
    <Head title="Redefinir senha" />
    <div class="auth-card">
      <div class="auth-brand">
        <div class="auth-badge">FP</div>
        FinPilot
      </div>

      <div>
        <h1 class="auth-title">Redefinir senha</h1>
        <p class="auth-subtitle">Escolha uma nova senha para sua conta.</p>
      </div>

      <form class="auth-form" @submit.prevent="submit">
        <label class="auth-field">
          <span class="auth-label">Email</span>
          <input v-model="form.email" class="auth-input" type="email" autocomplete="username" />
          <span v-if="form.errors.email" class="auth-error">{{ form.errors.email }}</span>
        </label>

        <label class="auth-field">
          <span class="auth-label">Nova senha</span>
          <input v-model="form.password" class="auth-input" type="password" autocomplete="new-password" />
          <span v-if="form.errors.password" class="auth-error">{{ form.errors.password }}</span>
        </label>

        <label class="auth-field">
          <span class="auth-label">Confirmar senha</span>
          <input v-model="form.password_confirmation" class="auth-input" type="password" autocomplete="new-password" />
        </label>

        <div class="auth-actions">
          <button class="auth-button" type="submit" :disabled="form.processing">Salvar senha</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
  token: { type: String, default: '' },
  email: { type: String, default: '' },
});

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('password.store'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>
