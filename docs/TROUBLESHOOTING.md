# Troubleshooting

## Erro de dependências PHP
- Rode `composer install`.

## Erro de build Vite
- Rode `npm install` e `npm run dev`.

## Falha ao migrar
- Confirme DB no `.env`.
- Se usar SQLite em memória, rode via testes.

## Período global não atualiza
- Verifique se `resources/js/finpilot/index.js` está importado em `resources/js/config/app.js`.
- Garanta que o evento `finpilot:period-changed` está sendo observado nas páginas.
