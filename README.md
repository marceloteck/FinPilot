# CÓDIGO PADRÃO PARA CRIAÇÃO DE PROJETOS COM VUE.JS, LARAVEL, E INERTIA.JS

## FinPilot — guia rápido
- CSS base do FinPilot: `resources/css/finpilot.css` (importado em `resources/css/app.css`).
- JS de tema e acessibilidade: `resources/js/finpilot/theme.js` (inicializado via `resources/js/finpilot/index.js`).
- JS de período global (ano/mês): `resources/js/finpilot/period.js` (inicializado via `resources/js/finpilot/index.js`).

### Como habilitar selects de tema no futuro
Na página de configurações, use os IDs abaixo para o JS aplicar automaticamente:
- `#themeSelect` (system/light/dark)
- `#fontSelect` (md/lg/xl)
- `#contrastSelect` (normal/high)

## Visão geral do FinPilot
O FinPilot é um gerenciador financeiro inteligente focado em:
- transações
- dívidas e impacto no salário
- assistente com planos de pagamento
- relatórios mensais e anuais

## Stack
- Laravel (backend)
- Vue 3 + Inertia.js (frontend)
- Vite (build)

## Como rodar local
1. Instale dependências PHP e Node.
2. Gere o `.env` e configure o banco.
3. Rode migrations e seeders.
4. Execute `npm run dev` e `php artisan serve`.

## Estrutura do projeto (FinPilot)
- Layout principal: `resources/PagesVuejs/components/Layouts/AppLayout.vue`
- Páginas: `resources/PagesVuejs/Pages`
- Assistente IA: `app/Services/AI/PaymentPlanService.php` e `resources/PagesVuejs/Pages/AI.vue`
- Período global (JS): `resources/js/finpilot/period.js`

## Documentação
- Pasta `docs/` contém setup, arquitetura, rotas, frontend, testes e troubleshooting.
