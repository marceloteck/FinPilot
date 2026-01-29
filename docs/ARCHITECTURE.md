# Arquitetura

## Camadas
- **Controllers (Laravel)**: responsáveis por receber requests e enviar props para o Inertia.
- **Models (Eloquent)**: representam entidades do domínio (transações, dívidas, IA, etc).
- **Services**: regras de negócio mais complexas (ex: `PaymentPlanService`).
- **Frontend (Vue + Inertia)**: páginas e layout do app.

## Período global
- Helper: `app/Support/PeriodContext.php`
- Frontend: `resources/js/finpilot/period.js`
- Evento global: `finpilot:period-changed`

## Temas e acessibilidade
- Frontend: `resources/js/finpilot/theme.js`
- Atributos no `<html>`: `data-theme`, `data-font`, `data-contrast`

## Administração SaaS (Hotmart)
- Controller: `app/Http/Controllers/Finpilot/AdminHotmartController.php`
- Página: `resources/PagesVuejs/Pages/Admin/Hotmart.vue`
- Tabela: `hotmart_settings`

## Permissões por assinatura
- Helper: `app/Support/SubscriptionGate.php`
- Configuração: tabela `subscription_settings`
