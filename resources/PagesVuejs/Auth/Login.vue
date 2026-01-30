<template>
  <div class="auth-shell">
    <Head title="Entrar" />
    <div class="auth-card">
      <div class="auth-brand">
        <div class="auth-badge">FP</div>
        FinPilot
      </div>

      <div>
        <h1 class="auth-title">Acesse sua conta</h1>
        <p class="auth-subtitle">Use suas credenciais para continuar.</p>
      </div>

      <div v-if="status" class="auth-alert">
        {{ status }}
      </div>

      <form class="auth-form" @submit.prevent="submit">
        <label class="auth-field">
          <span class="auth-label">Email</span>
          <input v-model="form.email" class="auth-input" type="email" autocomplete="username" />
          <span v-if="form.errors.email" class="auth-error">{{ form.errors.email }}</span>
        </label>

        <label class="auth-field">
          <span class="auth-label">Senha</span>
          <input v-model="form.password" class="auth-input" type="password" autocomplete="current-password" />
          <span v-if="form.errors.password" class="auth-error">{{ form.errors.password }}</span>
        </label>

        <div class="auth-row">
          <label>
            <input v-model="form.remember" type="checkbox" />
            Lembrar de mim
          </label>
          <Link v-if="canResetPassword" class="auth-link" :href="route('password.request')">
            Esqueci a senha
          </Link>
        </div>

        <div class="auth-actions">
          <button class="auth-button" type="submit" :disabled="form.processing">
            Entrar
          </button>
        </div>
      </form>

      <div class="auth-footer">
        Não tem conta?
        <Link class="auth-link" :href="route('register')">Criar conta</Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  canResetPassword: {
    type: Boolean,
    default: false,
  },
  status: {
    type: String,
    default: '',
  },
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>
