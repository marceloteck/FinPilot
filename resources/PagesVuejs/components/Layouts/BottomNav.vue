<template>
  <nav class="bottom-nav" aria-label="Atalhos">
    <Link class="nav-link" :class="{ active: isActive('/dashboard') }" href="/dashboard">Home</Link>
    <Link class="nav-link" :class="{ active: isActive('/transactions') }" href="/transactions">Transações</Link>
    <Link class="nav-link" :class="{ active: isActive('/debts') }" href="/debts">Dívidas</Link>
    <Link class="nav-link" :class="{ active: isActive('/ai') }" href="/ai">IA</Link>
    <Link class="nav-link" :class="{ active: isActive('/settings') }" href="/settings">Config</Link>
  </nav>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const isActive = (path) => (path === '/dashboard' ? page.url === '/dashboard' : page.url.startsWith(path));
</script>
