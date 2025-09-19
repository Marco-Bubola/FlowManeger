@props(['client'])

<div class="bg-gradient-to-r from-blue-50 via-white to-indigo-50 dark:from-blue-900/20 dark:via-zinc-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300">
    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <div class="p-2 bg-blue-500/10 rounded-xl">
            <i class="bi bi-person-circle text-blue-500 text-xl"></i>
        </div>
        Cliente
    </h3>

    <div class="space-y-3">
        <div class="flex items-center gap-3 p-3 bg-white/50 dark:bg-zinc-800/50 rounded-xl">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <i class="bi bi-person-badge text-blue-600 dark:text-blue-400 text-lg"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Nome</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $client->name }}</p>
            </div>
        </div>

        @if($client->email)
        <div class="flex items-center gap-3 p-3 bg-white/50 dark:bg-zinc-800/50 rounded-xl">
            <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                <i class="bi bi-envelope text-green-600 dark:text-green-400 text-lg"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Email</p>
                <p class="text-base text-gray-700 dark:text-gray-300">{{ $client->email }}</p>
            </div>
        </div>
        @endif

        @if($client->phone)
        <div class="flex items-center gap-3 p-3 bg-white/50 dark:bg-zinc-800/50 rounded-xl">
            <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                <i class="bi bi-telephone text-purple-600 dark:text-purple-400 text-lg"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Telefone</p>
                <p class="text-base text-gray-700 dark:text-gray-300">{{ $client->phone }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
