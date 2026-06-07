<?php

namespace App\Livewire\Conquistas;

use App\Models\DailyHabitCompletion;
use App\Models\Goal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class InsightsView extends Component
{
    public array $adherence = [];     // últimos 30 dias: conclusões por dia
    public array $goalsByMonth = [];   // metas concluídas por mês (6 meses)
    public array $kpis = [];

    public function mount(): void
    {
        $this->compute();
    }

    public function compute(): void
    {
        $userId = Auth::id();

        // Adesão de hábitos — últimos 30 dias
        $start = Carbon::today()->subDays(29);
        $rows = DailyHabitCompletion::where('user_id', $userId)
            ->where('completion_date', '>=', $start)
            ->selectRaw('DATE(completion_date) as d, COUNT(*) as total')
            ->groupBy('d')->pluck('total', 'd')->all();

        $labels = []; $data = [];
        for ($i = 0; $i < 30; $i++) {
            $day = $start->copy()->addDays($i);
            $labels[] = $day->format('d/m');
            $data[] = (int) ($rows[$day->toDateString()] ?? 0);
        }
        $this->adherence = ['labels' => $labels, 'data' => $data];

        // Metas concluídas por mês — 6 meses
        $mStart = Carbon::today()->startOfMonth()->subMonths(5);
        $goalRows = Goal::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $mStart)
            ->selectRaw("DATE_FORMAT(completed_at, '%Y-%m') as m, COUNT(*) as total")
            ->groupBy('m')->pluck('total', 'm')->all();

        $mLabels = []; $mData = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $mStart->copy()->addMonths($i);
            $mLabels[] = $month->translatedFormat('M/y');
            $mData[] = (int) ($goalRows[$month->format('Y-m')] ?? 0);
        }
        $this->goalsByMonth = ['labels' => $mLabels, 'data' => $mData];

        // KPIs rápidos
        $totalCompletions30 = array_sum($data);
        $this->kpis = [
            'completions_30' => $totalCompletions30,
            'avg_per_day'    => round($totalCompletions30 / 30, 1),
            'goals_done_6m'  => array_sum($mData),
            'active_goals'   => Goal::where('user_id', $userId)->whereNull('completed_at')->where('is_archived', false)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.conquistas.insights-view');
    }
}
