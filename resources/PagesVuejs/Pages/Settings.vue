<template>
  <AppLayout title="Configurações" subtitle="Tema, acessibilidade e preferências">
    <Head title="Configurações" />
    <section class="grid">
      <div class="card span-6">
        <div class="card-head">
          <div>
            <h2>Aparência</h2>
            <p class="muted">Escolha o tema que mais combina com você</p>
          </div>
        </div>
        <label class="field">
          <span class="label">Tema</span>
          <select id="themeSelect" class="select">
            <option value="system">Sistema</option>
            <option value="light">Claro</option>
            <option value="dark">Escuro</option>
          </select>
        </label>
      </div>

      <div class="card span-6">
        <div class="card-head">
          <div>
            <h2>Acessibilidade</h2>
            <p class="muted">Ajuste a fonte e o contraste</p>
          </div>
        </div>
        <label class="field">
          <span class="label">Fonte</span>
          <select id="fontSelect" class="select">
            <option value="md">Normal</option>
            <option value="lg">Grande</option>
            <option value="xl">Extra</option>
          </select>
        </label>
        <label class="field">
          <span class="label">Contraste</span>
          <select id="contrastSelect" class="select">
            <option value="normal">Normal</option>
            <option value="high">Alto</option>
          </select>
        </label>
      </div>

      <div class="card span-12">
        <div class="card-head">
          <div>
            <h2>Preferências futuras</h2>
            <p class="muted">Em breve você poderá controlar alertas, metas e integrações.</p>
          </div>
        </div>
        <p class="muted">
          Estamos preparando mais opções de personalização para o FinPilot. Fique de olho nas próximas versões.
        </p>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../components/Layouts/AppLayout.vue';
</script>
