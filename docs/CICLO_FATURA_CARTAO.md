# 💳 Sistema de Ciclo de Fatura do Cartão

## 📋 Visão Geral

O sistema agora exibe as transações financeiras (invoices) agrupadas por **ciclo de fatura do cartão**, ao invés de usar o mês calendário tradicional.

## 🎯 Como Funciona

### Exemplo Prático

Se o seu cartão de crédito tem:
- **Dia de abertura:** 6
- **Dia de fechamento:** 5 (do mês seguinte)

Então a fatura do mês de **Janeiro/2025** incluirá transações de:
- **06/01/2025** até **05/02/2025**

### Configuração do Banco/Cartão

1. Acesse **Bancos** > **Adicionar Novo** ou **Editar**
2. Preencha os campos:
   - **📅 Dia de Abertura da Fatura:** Selecione qualquer data que tenha o dia 6 (ex: 06/01/2025)
   - **🔒 Dia de Fechamento da Fatura:** Selecione qualquer data que tenha o dia 5 (ex: 05/01/2025)

> **Importante:** O sistema usa apenas o **dia do mês** dessas datas, não importa o mês ou ano selecionado!

## 🔧 Alterações Técnicas Realizadas

### 1. Componente Livewire (`InvoicesIndex.php`)

#### Método `calculateDateRanges()`
Calcula o intervalo de datas baseado no ciclo de fatura:

```php
private function calculateDateRanges()
{
    // Extrai o dia de início e fim do banco/cartão
    $startDay = Carbon::parse($this->bank->start_date)->day;
    $endDay = Carbon::parse($this->bank->end_date)->day;
    
    // Se o dia de início > dia de fim, o ciclo passa para o próximo mês
    if ($startDay > $endDay) {
        // Ex: dia 6 até dia 5 do próximo mês
        $this->currentStartDate = Carbon::create($this->year, $this->month, $startDay);
        $this->currentEndDate = Carbon::create($this->year, $this->month, $startDay)
            ->addMonth()->day($endDay);
    } else {
        // Ciclo normal dentro do mesmo mês
        $this->currentStartDate = Carbon::create($this->year, $this->month, $startDay);
        $this->currentEndDate = Carbon::create($this->year, $this->month, $endDay);
    }
}
```

#### Método `prepareCalendarData()`
Monta o calendário baseado no ciclo de fatura:

```php
private function prepareCalendarData(): void
{
    $firstDayOfCycle = $this->currentStartDate->copy();
    $lastDayOfCycle = $this->currentEndDate->copy();
    
    // Adiciona todos os dias do ciclo ao calendário
    $currentDay = $firstDayOfCycle->copy();
    while ($currentDay->lte($lastDayOfCycle)) {
        $calendarDays[] = [
            'date' => $currentDay->format('Y-m-d'),
            'day' => $currentDay->day,
            'isCurrentMonth' => true,
            'isToday' => $currentDay->isToday(),
            'invoices' => $dayInvoices
        ];
        $currentDay->addDay();
    }
}
```

#### Métodos `previousMonth()` e `nextMonth()`
Navegam entre ciclos de fatura ao invés de meses calendário.

### 2. Views de Criação/Edição de Bancos

Atualizadas para deixar claro que os campos `start_date` e `end_date` definem o ciclo de fatura:

- Labels descritivos: "📅 Dia de Abertura da Fatura" e "🔒 Dia de Fechamento da Fatura"
- Mensagem de ajuda explicando como funciona o ciclo
- Box informativo com exemplo prático

### 3. Modelo Bank

Campos utilizados:
- `start_date` (date): Data com o dia de abertura do ciclo
- `end_date` (date): Data com o dia de fechamento do ciclo

## 📊 Interface do Usuário

### Calendário
- Mostra todos os dias do ciclo de fatura
- Dias fora do ciclo aparecem em cinza
- Dias com transações têm um indicador visual (bolinha vermelha)
- Ao clicar em um dia, filtra apenas as transações daquele dia

### Navegação
- Botões "Anterior" e "Próximo" navegam entre ciclos de fatura
- Selects de mês/ano permitem pular para um ciclo específico
- Nome do ciclo exibe o período: "Fatura Jan/2025 - Fev/2025 (dia 6 até dia 5)"

### Estatísticas
As estatísticas (total de despesas, maior/menor transação, etc.) são calculadas baseadas no ciclo de fatura selecionado.

## 🧪 Testando

1. **Configure um banco/cartão:**
   - Acesse Bancos > Adicionar Novo
   - Configure o ciclo (ex: dia 6 até dia 5)
   - Salve

2. **Adicione transações:**
   - Crie transações em diferentes datas
   - Por exemplo: 
     - 10/01/2025 (dentro do ciclo Jan)
     - 01/02/2025 (dentro do ciclo Jan)
     - 07/02/2025 (fora do ciclo Jan, no ciclo Fev)

3. **Visualize no calendário:**
   - Acesse Transações
   - Veja o calendário mostrando o ciclo completo
   - Navegue entre ciclos usando os botões

## ✅ Benefícios

- ✨ Visualização realista da fatura do cartão
- 📅 Calendário adaptado ao ciclo do cartão
- 🔄 Navegação entre ciclos de fatura
- 💡 Interface intuitiva com dicas de uso
- 📊 Estatísticas precisas por período de fatura

## 🐛 Possíveis Problemas

### Banco sem configuração de ciclo
Se `start_date` ou `end_date` forem `null`, o sistema usará:
- Dia 1 como início
- Último dia do mês como fim

### Solução
Configure o ciclo de fatura no cadastro do banco/cartão.

## 🔜 Próximas Melhorias

- [ ] Permitir configurar o dia usando um número ao invés de data completa
- [ ] Adicionar campo para dia de vencimento da fatura
- [ ] Mostrar quanto falta para fechar a fatura atual
- [ ] Alertas de gastos próximos ao limite
- [ ] Histórico de faturas anteriores

---

**Desenvolvido com ❤️ para FlowManager**
