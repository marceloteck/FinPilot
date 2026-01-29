# Administração SaaS (Hotmart)

## Objetivo
Centralizar a configuração da Hotmart para venda de acesso ao FinPilot (SaaS).

## Página
- URL: `/admin/hotmart`
- Vue: `resources/PagesVuejs/Pages/Admin/Hotmart.vue`

## Campos
- `is_enabled`: habilita ou desabilita a integração
- `product_id`: ID do produto na Hotmart
- `basic_key` e `basic_secret`: credenciais de API
- `webhook_secret`: segredo para validar eventos
- `webhook_url`: URL do webhook configurado na Hotmart
- `ai_enabled`, `import_enabled`, `reports_enabled`: flags por assinatura
- `max_users`: limite de usuários por plano

## Fluxo esperado
1. Criar produto na Hotmart.
2. Inserir credenciais e webhook na página de admin.
3. Validar eventos de compra para liberar acesso.

## Permissões por assinatura
As permissões são configuradas no admin para ativar/desativar IA, importação e relatórios,
além de definir limite de usuários. Estas flags são consumidas pelo helper `SubscriptionGate`.
