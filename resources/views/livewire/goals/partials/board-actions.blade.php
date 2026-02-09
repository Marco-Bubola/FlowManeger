<div class="flex items-center gap-2">
    <a href="{{ route('goals.create', ['boardId' => $boardId ?? ($board->id ?? null)]) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl font-semibold shadow-lg transition-all duration-300">
        <i class="fas fa-plus mr-2"></i> Nova Meta
    </a>
</div>
