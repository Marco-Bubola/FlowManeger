<div class="min-h-screen w-full ">
    <!-- Header Modernizado (com passos para visual similar ao create-product) -->
    <x-sales-header
        title="Nova Transação"
        description="Adicione uma nova transação ao seu livro caixa"
        :back-route="route('cashbook.index')"
        :current-step="$currentStep ?? 1"
        :steps="[
            [
                'title' => 'Informações',
                'description' => 'Detalhes da transação',
                'icon' => 'bi-info-circle',
                'gradient' => 'from-emerald-500 to-teal-500',
                'connector_gradient' => 'from-emerald-500 to-teal-500'
            ],
            [
                'title' => 'Anexo',
                'description' => 'Adicionar comprovante ou arquivo',
                'icon' => 'bi-paperclip',
                'gradient' => 'from-blue-500 to-indigo-500'
            ]
        ]" />

    <!-- Conteúdo Principal (estrutura espelhada ao create-product) -->
    <div class="relative flex-1 overflow-y-auto">
        <form wire:submit.prevent="save" class="">
            <div class="px-8  space-y-6 h-full flex flex-col">

                <div class="flex-1 space-y-6 animate-fadeIn">
                    @if($currentStep == 1)
                    <!-- Card Container Principal (Step 1) -->
                    <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-3xl p-8 shadow-2xl shadow-slate-200/50 dark:shadow-slate-900/50 border border-white/20 dark:border-slate-700/50 w-full">
                        <!-- Seção superior: Value / Cliente / Data (3 colunas) -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                            <!-- Valor (usando componente de moeda único, sem duplicar label/icones) -->
                            <div class="group space-y-4">
                                <x-currency-input
                                    name="value"
                                    id="value"
                                    wireModel="value"
                                    label="Valor"
                                    icon="bi-currency-dollar"
                                    iconColor="green"
                                    :required="true"
                                    width="w-full"
                                />
                            </div>

                            <!-- Cliente -->
                            <div class="group space-y-4">
                                <label for="client_id" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-300">
                                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-cyan-400 to-blue-600 rounded-xl mr-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="bi bi-person text-white"></i>
                                    </div>
                                    Cliente
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="bi bi-person text-slate-400 group-hover:text-indigo-500 transition-colors duration-300"></i>
                                    </div>
                                    <select wire:model="client_id"
                                            class="w-full pl-14 pr-4 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                            {{ $errors->has('client_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500/20 hover:border-indigo-300' }}
                                            focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl">
                                        <option value="">Selecione um cliente</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('client_id')
                                <div class="flex items-center mt-3 p-3 bg-red-50/80 dark:bg-red-900/30 rounded-xl border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                                    <p class="text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>

                            <!-- Data -->
                            <div class="group space-y-4">
                                <label for="date" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl mr-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="bi bi-calendar text-white"></i>
                                    </div>
                                    Data
                                </label>
                                <input wire:model="date" type="date"
                                       class="w-full pl-14 pr-4 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                       {{ $errors->has('date') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500/20 hover:border-blue-300' }}
                                       focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl">
                                @error('date')
                                <div class="flex items-center mt-3 p-3 bg-red-50/80 dark:bg-red-900/30 rounded-xl border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                                    <p class="text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Descrição ocupando largura total -->
                        <div class="grid grid-cols-1 gap-6 mb-6">
                            <div class="group space-y-4">
                                <label for="description" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-300">
                                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-xl mr-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="bi bi-card-text text-white"></i>
                                    </div>
                                    Descrição
                                </label>
                                <div class="relative">
                                    <textarea wire:model="description"
                                              id="description"
                                              rows="4"
                                              class="w-full pl-14 pr-4 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 resize-none
                                              {{ $errors->has('description') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500/20 hover:border-indigo-300' }}
                                              focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl"
                                              placeholder="Descrição da transação"></textarea>
                                </div>
                                @error('description')
                                <div class="flex items-center mt-3 p-3 bg-red-50/80 dark:bg-red-900/30 rounded-xl border border-red-200 dark:border-red-800 backdrop-blur-sm">
                                    <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3"></i>
                                    <p class="text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Grid inferior: Categoria / Tipo / Cofrinho -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div>
                                <label for="category_id" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
                                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl mr-4 shadow-lg">
                                        <i class="bi bi-tags-fill text-white"></i>
                                    </div>
                                    Categoria
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="bi bi-tags-fill text-slate-400 group-hover:text-purple-500 transition-colors duration-300"></i>
                                    </div>
                                    <select wire:model="category_id"
                                            class="w-full pl-14 pr-4 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                            {{ $errors->has('category_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-purple-500 focus:ring-purple-500/20 hover:border-purple-300' }}
                                            focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl">
                                        <option value="">Selecione uma categoria</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id_category }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="type_id" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-300">
                                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-xl mr-4 shadow-lg">
                                        <i class="bi bi-exchange-alt text-white"></i>
                                    </div>
                                    Tipo
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="bi bi-exchange-alt text-slate-400 group-hover:text-indigo-500 transition-colors duration-300"></i>
                                    </div>
                                    <select wire:model="type_id"
                                            class="w-full pl-14 pr-4 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                            {{ $errors->has('type_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500/20 hover:border-indigo-300' }}
                                            focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl">
                                        <option value="">Selecione um tipo</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->id_type }}">{{ $type->desc_type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('type_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="cofrinho_id" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors duration-300">
                                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-amber-400 to-yellow-500 rounded-xl mr-4 shadow-lg">
                                        <i class="bi bi-piggy-bank text-white"></i>
                                    </div>
                                    Cofrinho
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="bi bi-piggy-bank text-slate-400 group-hover:text-amber-500 transition-colors duration-300"></i>
                                    </div>
                                    <select wire:model="cofrinho_id"
                                            class="w-full pl-14 pr-4 py-4 border-2 rounded-2xl bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm text-slate-900 dark:text-slate-100 placeholder-slate-400
                                            {{ $errors->has('cofrinho_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-200 dark:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500/20 hover:border-indigo-300' }}
                                            focus:ring-4 focus:outline-none transition-all duration-300 shadow-lg hover:shadow-xl">
                                        <option value="">Selecione um cofrinho</option>
                                        @foreach($cofrinhos as $cofrinho)
                                            <option value="{{ $cofrinho->id }}">{{ $cofrinho->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('cofrinho_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($currentStep == 2)
                        <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-xl rounded-3xl p-8 shadow-2xl shadow-slate-200/50 dark:shadow-slate-900/50 border border-white/20 dark:border-slate-700/50 w-full">
                            <h3 class="text-2xl font-bold mb-4">Anexo / Comprovante</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-200 mb-2">Anexo</label>
                                    <input wire:model="attachment" type="file" class="w-full" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    @error('attachment') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Botões de Ação Modernizados (fora do card, igual ao create-product) -->
                    <x-action-buttons-new
                        :show-back="$currentStep > 1"
                        :show-next="$currentStep < 2"
                        :show-save="$currentStep == 2"
                        :show-cancel="true"
                        back-action="previousStep"
                        next-action="nextStep"
                        save-action="save"
                        :cancel-route="route('cashbook.index')"
                        save-text="Salvar Transação"
                        loading-text="Salvando..."
                    />
                </div>
            </div>
        </form>
    </div>

    <!-- Estilos Customizados (copiados do create-product para manter animações e efeitos) -->
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideInRight { from { opacity: 0; transform: translateX(50px);} to { opacity:1; transform: translateX(0);} }
        @keyframes slideInLeft { from { opacity: 0; transform: translateX(-50px);} to { opacity:1; transform: translateX(0);} }
        @keyframes float { 0%,100% { transform: translateY(0px) rotate(0deg);} 33% { transform: translateY(-10px) rotate(1deg);} 66% { transform: translateY(-5px) rotate(-1deg);} }
        @keyframes shimmer { 0% { background-position: -1000px 0; } 100% { background-position: 1000px 0; } }
        .animate-fadeIn { animation: fadeIn 0.8s ease-out forwards; }
        .animate-slideInRight { animation: slideInRight 0.6s ease-out forwards; }
        .animate-slideInLeft { animation: slideInLeft 0.6s ease-out forwards; }
        .animate-float { animation: float 3s ease-in-out infinite; }
        .animate-shimmer { background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); background-size: 200% 100%; animation: shimmer 2s infinite; }
        .group:hover .group-hover\:animate-glow { box-shadow: 0 0 20px rgba(59, 130, 246, 0.5); }
        .step-transition { transition: all 0.5s cubic-bezier(0.4,0,0.2,1); }
        .glassmorphism { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
        .gradient-animate { background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab); background-size: 400% 400%; animation: gradientShift 15s ease infinite; }
        @keyframes gradientShift { 0%{ background-position:0% 50%; }50%{ background-position:100% 50%; }100%{ background-position:0% 50%; } }
    </style>
</div>
