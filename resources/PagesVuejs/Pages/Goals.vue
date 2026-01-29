<template>
  <AppLayout title="Metas" subtitle="Orçamento e objetivos">
    <Head title="Metas" />
    <section class="grid">
      <div class="card span-12">
        <div class="card-head">
          <div>
            <h2>Em breve</h2>
            <p class="muted">Metas financeiras e orçamentos serão adicionados em breve.</p>
          </div>
        </div>
        <p class="muted">Você poderá definir metas, acompanhar progresso e ajustar limites.</p>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../components/Layouts/AppLayout.vue';
</script>
