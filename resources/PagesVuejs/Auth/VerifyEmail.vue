<template>
  <div class="auth-shell">
    <Head title="Verificar email" />
    <div class="auth-card">
      <div class="auth-brand">
        <div class="auth-badge">FP</div>
        FinPilot
      </div>

      <div>
        <h1 class="auth-title">Verifique seu email</h1>
        <p class="auth-subtitle">
          Enviamos um link de verificação. Clique no link para liberar o acesso completo.
        </p>
      </div>

      <div v-if="status === 'verification-link-sent'" class="auth-alert">
        Um novo link de verificação foi enviado para seu email.
      </div>

      <div class="auth-actions">
        <button class="auth-button" type="button" :disabled="form.processing" @click="resend">
          Reenviar verificação
        </button>
        <button class="auth-link" type="button" @click="logout">Sair</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
  status: { type: String, default: '' },
});

const form = useForm({});

const resend = () => {
  form.post(route('verification.send'));
};

const logout = () => {
  form.post(route('logout'));
};
</script>
