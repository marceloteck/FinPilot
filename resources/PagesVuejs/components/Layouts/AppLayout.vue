<template>
  <div class="app">
    <SidebarNav />

    <main class="main">
      <Topbar :title="title" :subtitle="subtitle" />
      <slot />
    </main>

    <BottomNav />
  </div>
</template>

<script setup>
import SidebarNav from './SidebarNav.vue';
import Topbar from './Topbar.vue';
import BottomNav from './BottomNav.vue';

defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    required: true,
  },
});
</script>
