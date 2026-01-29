# Banco de dados

## Principais tabelas
- `categories`, `statuses`, `transactions`, `views`
- `debt_types`, `debts`
- `ai_preferences`, `ai_plans`, `ai_plan_items`, `ai_feedback`
- `hotmart_settings`
- `subscription_settings`

## Observações
- Todas as tabelas têm `user_id` (nullable) para futuro multiusuário.
- Use `scopeForUser()` nos models para isolar dados por usuário.

## Seeds
- `DatabaseSeeder` chama seeders para categorias, status, transações, views, tipos de dívida e dívidas.
