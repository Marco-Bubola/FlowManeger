@props(['cashbookDays', 'invoiceDays'])

<div x-data="calendar" class="bg-slate-800 rounded-xl p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
            Calendário de Atividades
        </h3>
        <div class="flex items-center gap-2">
            <button @click="previousMonth" class="p-2 rounded-md bg-slate-700 hover:bg-slate-600">
                <i class="fas fa-chevron-left"></i>
            </button>
            <span x-text="monthNames[month] + ' ' + year" class="text-lg font-semibold text-white"></span>
            <button @click="nextMonth" class="p-2 rounded-md bg-slate-700 hover:bg-slate-600">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-7 gap-2 text-center">
        <template x-for="day in dayNames" :key="day">
            <div class="text-xs font-medium text-slate-400" x-text="day"></div>
        </template>
    </div>

    <div class="grid grid-cols-7 gap-2 mt-2">
        <template x-for="blankday in blankdays">
            <div class="p-2"></div>
        </template>
        <template x-for="day in daysInMonth" :key="day">
            <div @click="$wire.showDayDetails(new Date(year, month, day).toISOString().split('T')[0])"
                 class="p-2 rounded-md text-center cursor-pointer"
                 :class="{
                    'bg-blue-500 text-white': '{{ $selectedDate }}' === new Date(year, month, day).toISOString().split('T')[0],
                    'bg-slate-700': '{{ $selectedDate }}' !== new Date(year, month, day).toISOString().split('T')[0]
                 }">
                <span x-text="day"></span>
                <div class="flex justify-center mt-1">
                    <template x-if="hasCashbookActivity(day)">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    </template>
                    <template x-if="hasInvoiceActivity(day)">
                        <div class="w-2 h-2 bg-red-500 rounded-full ml-1"></div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('calendar', () => ({
            month: new Date().getMonth(),
            year: new Date().getFullYear(),
            dayNames: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            daysInMonth: [],
            blankdays: [],
            cashbookDays: @json($cashbookDays),
            invoiceDays: @json($invoiceDays),

            init() {
                this.getDaysInMonth();
            },

            getDaysInMonth() {
                const days = new Date(this.year, this.month + 1, 0).getDate();
                const firstDayOfMonth = new Date(this.year, this.month, 1).getDay();

                this.daysInMonth = Array.from({ length: days }, (_, i) => i + 1);
                this.blankdays = Array.from({ length: firstDayOfMonth });
            },

            previousMonth() {
                if (this.month === 0) {
                    this.month = 11;
                    this.year--;
                } else {
                    this.month--;
                }
                this.getDaysInMonth();
            },

            nextMonth() {
                if (this.month === 11) {
                    this.month = 0;
                    this.year++;
                } else {
                    this.month++;
                }
                this.getDaysInMonth();
            },

            hasCashbookActivity(day) {
                return this.cashbookDays.includes(day) && this.month === new Date().getMonth() && this.year === new Date().getFullYear();
            },

            hasInvoiceActivity(day) {
                return this.invoiceDays.includes(day) && this.month === new Date().getMonth() && this.year === new Date().getFullYear();
            }
        }));
    });
</script>
