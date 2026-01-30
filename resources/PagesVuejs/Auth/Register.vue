<template>
  <div class="auth-shell">
    <Head title="Criar conta" />
    <div class="auth-card">
      <div class="auth-brand">
        <div class="auth-badge">FP</div>
        FinPilot
      </div>

      <div>
        <h1 class="auth-title">Crie sua conta</h1>
        <p class="auth-subtitle">Leva menos de um minuto para começar.</p>
      </div>

      <form class="auth-form" @submit.prevent="submit">
        <label class="auth-field">
          <span class="auth-label">Nome</span>
          <input v-model="form.name" class="auth-input" type="text" autocomplete="name" />
          <span v-if="form.errors.name" class="auth-error">{{ form.errors.name }}</span>
        </label>

        <label class="auth-field">
          <span class="auth-label">Email</span>
          <input v-model="form.email" class="auth-input" type="email" autocomplete="username" />
          <span v-if="form.errors.email" class="auth-error">{{ form.errors.email }}</span>
        </label>

        <label class="auth-field">
          <span class="auth-label">Senha</span>
          <input v-model="form.password" class="auth-input" type="password" autocomplete="new-password" />
          <span v-if="form.errors.password" class="auth-error">{{ form.errors.password }}</span>
        </label>

        <label class="auth-field">
          <span class="auth-label">Confirmar senha</span>
          <input v-model="form.password_confirmation" class="auth-input" type="password" autocomplete="new-password" />
        </label>

        <div class="auth-actions">
          <button class="auth-button" type="submit" :disabled="form.processing">
            Criar conta
          </button>
        </div>
      </form>

      <div class="auth-footer">
        Já tem conta?
        <Link class="auth-link" :href="route('login')">Entrar</Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>
