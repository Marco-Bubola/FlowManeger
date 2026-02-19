<div x-data="notificationManager()"
     x-init="init()"
     @notify.window="addNotification($event.detail)"
     class="fixed top-4 right-4 z-50 space-y-3 max-w-sm w-full">

    <!-- Renderizar notificações do Livewire -->
    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div x-data="{
                show: false,
                id: '<?php echo e($notification['id']); ?>',
                type: '<?php echo e($notification['type']); ?>',
                message: '<?php echo e(addslashes($notification['message'])); ?>',
                duration: <?php echo e($notification['duration']); ?>

             }"
             x-init="
                show = true;
                if (duration > 0) {
                    setTimeout(() => {
                        show = false;
                        setTimeout(() => window.livewire?.emit('removeNotification', id), 300);
                    }, duration);
                }
             "
             x-show="show"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="notification-item"
             role="alert">

            <!-- Success Notification -->
            <div x-show="type === 'success'"
                 class="flex items-center p-4 bg-white dark:bg-neutral-800 border-l-4 border-green-500 rounded-lg shadow-lg">
                <div class="flex items-center justify-center w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg mr-3">
                    <i class="bi bi-check-circle-fill text-green-600 dark:text-green-400 text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-green-800 dark:text-green-200">Sucesso!</h3>
                    <p class="text-sm text-green-700 dark:text-green-300" x-text="message"></p>
                </div>
                <button @click="show = false; setTimeout(() => window.livewire?.emit('removeNotification', id), 300)"
                        class="ml-4 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200">
                    <i class="bi bi-x text-lg"></i>
                </button>
            </div>

            <!-- Error Notification -->
            <div x-show="type === 'error'"
                 class="flex items-center p-4 bg-white dark:bg-neutral-800 border-l-4 border-red-500 rounded-lg shadow-lg">
                <div class="flex items-center justify-center w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg mr-3">
                    <i class="bi bi-exclamation-triangle-fill text-red-600 dark:text-red-400 text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-red-800 dark:text-red-200">Erro!</h3>
                    <p class="text-sm text-red-700 dark:text-red-300" x-text="message"></p>
                </div>
                <button @click="show = false; setTimeout(() => window.livewire?.emit('removeNotification', id), 300)"
                        class="ml-4 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                    <i class="bi bi-x text-lg"></i>
                </button>
            </div>

            <!-- Warning Notification -->
            <div x-show="type === 'warning'"
                 class="flex items-center p-4 bg-white dark:bg-neutral-800 border-l-4 border-yellow-500 rounded-lg shadow-lg">
                <div class="flex items-center justify-center w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg mr-3">
                    <i class="bi bi-exclamation-triangle text-yellow-600 dark:text-yellow-400 text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-yellow-800 dark:text-yellow-200">Atenção!</h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300" x-text="message"></p>
                </div>
                <button @click="show = false; setTimeout(() => window.livewire?.emit('removeNotification', id), 300)"
                        class="ml-4 text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-200">
                    <i class="bi bi-x text-lg"></i>
                </button>
            </div>

            <!-- Info Notification -->
            <div x-show="type === 'info'"
                 class="flex items-center p-4 bg-white dark:bg-neutral-800 border-l-4 border-blue-500 rounded-lg shadow-lg">
                <div class="flex items-center justify-center w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg mr-3">
                    <i class="bi bi-info-circle-fill text-blue-600 dark:text-blue-400 text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-blue-800 dark:text-blue-200">Informação</h3>
                    <p class="text-sm text-blue-700 dark:text-blue-300" x-text="message"></p>
                </div>
                <button @click="show = false; setTimeout(() => window.livewire?.emit('removeNotification', id), 300)"
                        class="ml-4 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                    <i class="bi bi-x text-lg"></i>
                </button>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Container para notificações JavaScript -->
    <template x-for="notification in jsNotifications" :key="notification.id">
        <div x-data="{ show: false }"
             x-init="
                show = true;
                if (notification.duration > 0) {
                    setTimeout(() => {
                        show = false;
                        setTimeout(() => removeJsNotification(notification.id), 300);
                    }, notification.duration);
                }
             "
             x-show="show"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="notification-item"
             role="alert">

            <!-- Success Notification -->
            <div x-show="notification.type === 'success'"
                 class="flex items-center p-4 bg-white dark:bg-neutral-800 border-l-4 border-green-500 rounded-lg shadow-lg">
                <div class="flex items-center justify-center w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg mr-3">
                    <i class="bi bi-check-circle-fill text-green-600 dark:text-green-400 text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-green-800 dark:text-green-200">Sucesso!</h3>
                    <p class="text-sm text-green-700 dark:text-green-300" x-text="notification.message"></p>
                </div>
                <button @click="show = false; setTimeout(() => removeJsNotification(notification.id), 300)"
                        class="ml-4 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200">
                    <i class="bi bi-x text-lg"></i>
                </button>
            </div>

            <!-- Error Notification -->
            <div x-show="notification.type === 'error'"
                 class="flex items-center p-4 bg-white dark:bg-neutral-800 border-l-4 border-red-500 rounded-lg shadow-lg">
                <div class="flex items-center justify-center w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg mr-3">
                    <i class="bi bi-exclamation-triangle-fill text-red-600 dark:text-red-400 text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-red-800 dark:text-red-200">Erro!</h3>
                    <p class="text-sm text-red-700 dark:text-red-300" x-text="notification.message"></p>
                </div>
                <button @click="show = false; setTimeout(() => removeJsNotification(notification.id), 300)"
                        class="ml-4 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                    <i class="bi bi-x text-lg"></i>
                </button>
            </div>

            <!-- Warning Notification -->
            <div x-show="notification.type === 'warning'"
                 class="flex items-center p-4 bg-white dark:bg-neutral-800 border-l-4 border-yellow-500 rounded-lg shadow-lg">
                <div class="flex items-center justify-center w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg mr-3">
                    <i class="bi bi-exclamation-triangle text-yellow-600 dark:text-yellow-400 text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-yellow-800 dark:text-yellow-200">Atenção!</h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300" x-text="notification.message"></p>
                </div>
                <button @click="show = false; setTimeout(() => removeJsNotification(notification.id), 300)"
                        class="ml-4 text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-200">
                    <i class="bi bi-x text-lg"></i>
                </button>
            </div>

            <!-- Info Notification -->
            <div x-show="notification.type === 'info'"
                 class="flex items-center p-4 bg-white dark:bg-neutral-800 border-l-4 border-blue-500 rounded-lg shadow-lg">
                <div class="flex items-center justify-center w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg mr-3">
                    <i class="bi bi-info-circle-fill text-blue-600 dark:text-blue-400 text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-blue-800 dark:text-blue-200">Informação</h3>
                    <p class="text-sm text-blue-700 dark:text-blue-300" x-text="notification.message"></p>
                </div>
                <button @click="show = false; setTimeout(() => removeJsNotification(notification.id), 300)"
                        class="ml-4 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                    <i class="bi bi-x text-lg"></i>
                </button>
            </div>
        </div>
    </template>
</div>

<script>
function notificationManager() {
    return {
        jsNotifications: [],

        init() {
            // Escuta eventos globais de notificação
            window.addEventListener('notify', (event) => {
                this.addNotification(event.detail);
            });
        },

        addNotification(notification) {
            const newNotification = {
                id: notification.id || Date.now() + Math.random(),
                type: notification.type || 'info',
                message: notification.message || '',
                duration: notification.duration || 5000
            };

            this.jsNotifications.push(newNotification);
        },

        removeJsNotification(id) {
            this.jsNotifications = this.jsNotifications.filter(n => n.id !== id);
        }
    }
}

// Função global para notificações JavaScript
window.notify = function(type, message, duration = 5000) {
    window.dispatchEvent(new CustomEvent('notify', {
        detail: { type, message, duration }
    }));
};

// Funções de conveniência
window.notifySuccess = function(message, duration = 5000) {
    window.notify('success', message, duration);
};

window.notifyError = function(message, duration = 7000) {
    window.notify('error', message, duration);
};

window.notifyWarning = function(message, duration = 6000) {
    window.notify('warning', message, duration);
};

window.notifyInfo = function(message, duration = 5000) {
    window.notify('info', message, duration);
};
</script>
<?php /**PATH C:\projetos\FlowManeger\resources\views/livewire/components/notifications.blade.php ENDPATH**/ ?>