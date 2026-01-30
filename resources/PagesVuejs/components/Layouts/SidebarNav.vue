<template>
  <aside class="sidebar" aria-label="Navegação">
    <div class="brand">
      <div class="logo" aria-hidden="true">FP</div>
      <div class="brand-text">
        <div class="brand-name">FinPilot</div>
        <div class="brand-sub">Gerenciador + Assistente IA</div>
      </div>
    </div>

    <nav class="nav">
      <Link class="nav-link" :class="{ active: isActive('/dashboard') }" href="/dashboard">
        <span class="nav-ico" aria-hidden="true">▦</span> Home
      </Link>
      <Link class="nav-link" :class="{ active: isActive('/transactions') }" href="/transactions">
        <span class="nav-ico" aria-hidden="true">≣</span> Transações
      </Link>
      <Link class="nav-link" :class="{ active: isActive('/debts') }" href="/debts">
        <span class="nav-ico" aria-hidden="true">⎘</span> Dívidas
      </Link>
      <Link class="nav-link" :class="{ active: isActive('/ai') }" href="/ai">
        <span class="nav-ico" aria-hidden="true">✦</span> IA
      </Link>
      <Link class="nav-link" :class="{ active: isActive('/goals') }" href="/goals">
        <span class="nav-ico" aria-hidden="true">◎</span> Metas
      </Link>
      <Link class="nav-link" :class="{ active: isActive('/reports') }" href="/reports">
        <span class="nav-ico" aria-hidden="true">↗</span> Relatórios
      </Link>
      <Link class="nav-link" :class="{ active: isActive('/settings') }" href="/settings">
        <span class="nav-ico" aria-hidden="true">⚙</span> Configurações
      </Link>
    </nav>

    <div class="sidebar-foot">
      <Link class="btn ghost full" href="/settings">Personalizar Dashboard</Link>
      <div class="hint">
        Tema: <span class="chip">{{ uiPrefs.theme }}</span>
        • Acessibilidade: <span class="chip">{{ uiPrefs.contrast }}</span>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const uiPrefs = page.props.uiPrefs ?? { theme: 'system', contrast: 'normal' };

const isActive = (path) => (path === '/dashboard' ? page.url === '/dashboard' : page.url.startsWith(path));
</script>
