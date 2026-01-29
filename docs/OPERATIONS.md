# Operações e monitoramento

## Logs
- Logs principais ficam em `storage/logs/laravel.log`.
- Eventos relevantes: geração/confirmação de planos de IA e atualização Hotmart.

## Agendamentos
- `ai:archive-plans` roda diariamente para arquivar planos de IA antigos.
- Configure o cron do Laravel (`php artisan schedule:run`) a cada minuto no servidor.

## Saúde do sistema
- Monitore o uso de disco (planos e feedback).
- Verifique falhas recorrentes no log e ajuste alertas.
