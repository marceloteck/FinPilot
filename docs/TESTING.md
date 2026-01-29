# Testes

## Configuração
O `phpunit.xml` já vem com SQLite em memória:
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=:memory:`

## Rodar a suíte
```bash
php artisan test
```

## O que é coberto
- Rotas principais com Inertia
- CRUD de transações e dívidas
- Assistente IA (geração/confirmação/feedback)
- Unit test do `PaymentPlanService`
