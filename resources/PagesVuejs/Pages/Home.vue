<template>
  <AppLayout title="Dashboard" subtitle="Visão geral do seu mês e plano de ação">
    <Head title="Dashboard" />
    <section class="grid">
      <div class="card span-12">
        <div class="card-head">
          <div>
            <h2>Resumo do mês</h2>
            <p class="muted">Salário vs compromissos • Sobra/Falta • Alertas</p>
          </div>
          <div class="segmented" role="group" aria-label="Modo de visão">
            <button class="seg active" type="button">Mês</button>
            <button class="seg" type="button">Ano</button>
          </div>
        </div>

        <div class="kpis">
          <article class="kpi">
            <div class="kpi-label">Entradas do mês</div>
            <div class="kpi-value">R$ 4.120,00</div>
            <div class="kpi-foot up">+R$ 240 vs mês anterior</div>
          </article>

          <article class="kpi">
            <div class="kpi-label">Gastos do mês</div>
            <div class="kpi-value">R$ 2.480,35</div>
            <div class="kpi-foot">fixos 1.520 • variáveis 960</div>
          </article>

          <article class="kpi">
            <div class="kpi-label">Dívidas do mês (mínimos)</div>
            <div class="kpi-value">R$ 980,00</div>
            <div class="kpi-foot warn">2 vencimentos próximos</div>
          </article>

          <article class="kpi highlight">
            <div class="kpi-label">Sobra / Falta</div>
            <div class="kpi-value">R$ 659,65</div>
            <div class="kpi-foot">comprometido 86%</div>
          </article>
        </div>

        <div class="bar-wrap" aria-label="Barra de comprometimento do salário">
          <div class="bar">
            <div class="bar-fill" style="width: 86%"></div>
            <div class="bar-meta">
              <span>Comprometido</span>
              <span>86%</span>
            </div>
          </div>
          <div class="bar-sub">
            <span class="dot debt"></span> Dívidas
            <span class="dot fixed"></span> Fixos
            <span class="dot var"></span> Variáveis
          </div>
        </div>
      </div>

      <div class="card span-7">
        <div class="card-head">
          <div>
            <h2>Alertas automáticos</h2>
            <p class="muted">Atenção rápida antes do vencimento</p>
          </div>
        </div>
        <div class="alerts">
          <div class="alert warn">
            <strong>Cartão Nubank</strong> vence em 3 dias • mínimo R$ 312,00
            <button class="link" type="button">Ver</button>
          </div>
          <div class="alert">
            <strong>Assinatura detectada</strong> Spotify R$ 19,90
            <button class="link" type="button">Revisar</button>
          </div>
          <div class="alert ok">
            <strong>Meta cumprida</strong> Reserva de emergência +R$ 200
            <button class="link" type="button">Detalhes</button>
          </div>
        </div>
      </div>

      <div class="card span-5">
        <div class="card-head">
          <div>
            <h2>Dívidas deste mês</h2>
            <p class="muted">Preview das próximas 3</p>
          </div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Credor</th>
                <th>Tipo</th>
                <th>Venc.</th>
                <th>Mínimo</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Nubank</td>
                <td>Cartão</td>
                <td>12/07</td>
                <td class="right">R$ 312</td>
              </tr>
              <tr>
                <td>Itaú</td>
                <td>Empréstimo</td>
                <td>18/07</td>
                <td class="right">R$ 420</td>
              </tr>
              <tr>
                <td>BV</td>
                <td>Financiamento</td>
                <td>25/07</td>
                <td class="right">R$ 248</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="actions">
          <button class="btn ghost" type="button">Gerar plano de pagamento</button>
          <button class="btn" type="button">Adicionar dívida</button>
        </div>
      </div>

      <div class="card span-7">
        <div class="card-head">
          <div>
            <h2>Assistente IA</h2>
            <p class="muted">Sugestões de alocação por prioridade</p>
          </div>
        </div>
        <div class="grid">
          <div class="card span-12">
            <div class="card-head">
              <h3>Obrigatório</h3>
              <span class="badge warn">Alta</span>
            </div>
            <p class="muted">Pague mínimos para evitar juros e multas.</p>
            <div class="kpi-value">R$ 980,00</div>
          </div>
          <div class="card span-12">
            <div class="card-head">
              <h3>Prioritário</h3>
              <span class="badge">Média</span>
            </div>
            <p class="muted">Direcione o extra para dívidas com maior score.</p>
            <div class="kpi-value">R$ 420,00</div>
          </div>
          <div class="card span-12">
            <div class="card-head">
              <h3>Se sobrar</h3>
              <span class="badge ok">Baixa</span>
            </div>
            <p class="muted">Reserva de emergência e objetivos.</p>
            <div class="kpi-value">R$ 200,00</div>
          </div>
        </div>
        <div class="actions">
          <button class="btn" type="button">Confirmar plano</button>
          <button class="btn ghost" type="button">Ajustar valores</button>
        </div>
      </div>

      <div class="card span-5">
        <div class="card-head">
          <div>
            <h2>Transações recentes</h2>
            <p class="muted">Últimos 3 lançamentos</p>
          </div>
        </div>
        <div class="list">
          <div class="list-item">
            <div>
              <div class="li-title">Mercado do mês</div>
              <div class="li-sub">Cartão • 05/07</div>
            </div>
            <div class="li-right">
              <div class="li-amount">- R$ 480</div>
              <span class="badge">Confirmado</span>
            </div>
          </div>
          <div class="list-item">
            <div>
              <div class="li-title">Salário</div>
              <div class="li-sub">Conta principal • 03/07</div>
            </div>
            <div class="li-right">
              <div class="li-amount plus">+ R$ 3.900</div>
              <span class="badge ok">Entrada</span>
            </div>
          </div>
          <div class="list-item">
            <div>
              <div class="li-title">Assinatura streaming</div>
              <div class="li-sub">Revisar • 02/07</div>
            </div>
            <div class="li-right">
              <div class="li-amount">- R$ 39,90</div>
              <span class="badge warn">Revisar</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../components/Layouts/AppLayout.vue';
</script>
