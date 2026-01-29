# Setup local

## Requisitos
- PHP 8.1+
- Composer
- Node.js 18+
- Banco SQLite ou MySQL

## Passos
1. Instale dependências:
   - `composer install`
   - `npm install`
2. Copie o arquivo `.env`:
   - `cp .env.example .env`
3. Configure o banco no `.env`.
4. Gere a chave:
   - `php artisan key:generate`
5. Migre e rode seeders:
   - `php artisan migrate --seed`
6. Suba o Vite e o servidor:
   - `npm run dev`
   - `php artisan serve`

## Observações
- O CSS principal é `resources/css/finpilot.css` importado em `resources/css/app.css`.
- O JS do período/tema fica em `resources/js/finpilot/`.
