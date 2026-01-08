# Contrato/Termos do Consórcio (modelo)

> Preencha os campos entre colchetes antes de enviar ao cliente.

## Como exportar pelo sistema
- Na tela do consórcio, abra "Exportar" e escolha "Contrato/Regras (PDF)" para gerar este contrato com os dados básicos do consórcio.
- O PDF sai com nome `contrato_<consorcio>.pdf`. Campos em branco devem ser preenchidos manualmente antes de envio.
- Para exportar contratos de todos os clientes em um único PDF use "PDF Clientes (único)"; para ZIP separado por cliente, escolha "PDF por Cliente (ZIP)" (se ZipArchive não estiver disponível, cai no PDF único).

## 1. Identificação
- Nome do consórcio: [NOME]
- Administradora: [EMPRESA]
- Vagas disponíveis: [MAX_PARTICIPANTES]
- Frequência de sorteio: [SEMANAL | QUINZENAL | MENSAL | TRIMESTRAL]
- Duração: [DURACAO_MESES] meses
- Valor da parcela: R$ [VALOR_MENSAL]
- Início previsto: [DATA_INICIO]

## 2. Participação
- Cada participante recebe um número de participação único.
- Status possíveis: ativo, desistente, contemplado, cancelado.
- Participante contemplado não pode ser removido.

## 3. Pagamentos
- Parcelas mensais conforme valor acima.
- Métodos aceitos: dinheiro, PIX, cartão crédito/débito, transferência, boleto.
- Situações da parcela: pendente, paga, vencida.
- Pagamentos vencidos podem ter atualização de juros/multa (definir política: [POLITICA_JUROS_MULTA]).

## 4. Contemplação
- Ocorre por sorteio na frequência definida.
- Requisitos para participar do sorteio: estar ativo e não contemplado.
- Registro da contemplação: tipo (sorteio/oferta), data e participação vencedora.

## 5. Resgate
- Modos de resgate: produtos ou dinheiro.
- Para produtos: listar itens escolhidos e valores. Para dinheiro: valor de resgate acordado.
- Prazo de entrega/pagamento do resgate: [PRAZO_RESGATE].

## 6. Desistência e Reativação
- Participante ativo pode ser marcado como desistente. Reativação possível se autorizado.
- Efeitos financeiros da desistência: [REGRAS_REEMBOLSO_SE_HOUVER].

## 7. Cancelamento do Consórcio
- O consórcio pode ser desativado/cancelado pela administradora por [MOTIVOS].
- Efeitos sobre participantes e valores: [REGRAS_CANCELAMENTO].

## 8. Exportações e Relatórios
- Dados do consórcio podem ser exportados (PDF/ZIP). Caso o recurso ZIP não esteja disponível no ambiente, usar PDF.
- Relatórios incluem participantes, pagamentos, sorteios e contemplações.

## 9. Comunicação
- Canal oficial para dúvidas e suporte: [CANAL_CONTATO].
- Avisos importantes (sorteios, vencimentos) serão enviados por: [E-MAIL | WHATSAPP | APP].

## 10. Foro e Vigência
- Vigência: da adesão até o término da duração ou cancelamento.
- Foro para dirimir dúvidas: [CIDADE/UF].

---

Checklist ao enviar:
- [ ] Preencher todos os campos entre colchetes.
- [ ] Informar política de juros/multa para parcelas vencidas.
- [ ] Definir regras de reembolso em desistência e cancelamento.
- [ ] Definir prazos e forma de resgate (produtos/dinheiro).
