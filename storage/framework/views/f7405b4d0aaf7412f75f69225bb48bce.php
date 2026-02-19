<?php if (isset($component)) { $__componentOriginald275691d15a0a68ca98ac956f9920812 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald275691d15a0a68ca98ac956f9920812 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app.sidebar','data' => ['title' => $title ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title ?? null)]); ?>



  <?php if (isset($component)) { $__componentOriginal95c5505ccad18880318521d2bba3eac7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95c5505ccad18880318521d2bba3eac7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::main','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::main'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    

    <script>
      window.Promise ||
        document.write(
          '<script src="https://cdn.jsdelivr.net/npm/promise-polyfill@8/dist/polyfill.min.js"><\/script>'
        )
      window.Promise ||
        document.write(
          '<script src="https://cdn.jsdelivr.net/npm/eligrey-classlist-js-polyfill@1.2.20171210/classList.min.js"><\/script>'
        )
      window.Promise ||
        document.write(
          '<script src="https://cdn.jsdelivr.net/npm/findindex_polyfill_mdn"><\/script>'
        )
    </script>


    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


    <link rel="stylesheet" href="<?php echo e(asset('assets/css/icon-category.css')); ?>">
    
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/icon-overrides.css')); ?>">
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo $__env->yieldPushContent('styles'); ?>
    <?php echo e($slot); ?>


    <!-- Componente de Notificação de Download -->
    <?php echo $__env->make('components.download-notification', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Sistema de Notificações Global -->
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('components.notifications');

$__html = app('livewire')->mount($__name, $__params, 'lw-1286055422-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

    <!-- Sistema de Notificações de Achievements -->
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('components.achievement-notifier');

$__html = app('livewire')->mount($__name, $__params, 'lw-1286055422-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>


    <!-- Sistema de Notificações Global -->
    <script>
        // Sistema de Notificações Global
        window.notifications = {
            // Função principal para mostrar notificações
            show: function(type, message, duration = 5000) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type, message, duration }
                }));
            },

            // Funções de conveniência
            success: function(message, duration = 5000) {
                this.show('success', message, duration);
            },

            error: function(message, duration = 7000) {
                this.show('error', message, duration);
            },

            warning: function(message, duration = 6000) {
                this.show('warning', message, duration);
            },

            info: function(message, duration = 5000) {
                this.show('info', message, duration);
            }
        };

        // Aliases globais para facilitar o uso
        window.notifySuccess = window.notifications.success.bind(window.notifications);
        window.notifyError = window.notifications.error.bind(window.notifications);
        window.notifyWarning = window.notifications.warning.bind(window.notifications);
        window.notifyInfo = window.notifications.info.bind(window.notifications);

        // Função global para redirecionamento após delay
        window.redirectAfterDelay = function(url, delay = 1500) {
            setTimeout(() => {
                window.location.href = url;
            }, delay);
        };

        // Escuta eventos do Livewire para redirecionamento
        document.addEventListener('DOMContentLoaded', function() {
            // Listener para redirecionamento após delay vindo do Livewire
            if (window.Livewire) {
                document.addEventListener('livewire:init', () => {
                    Livewire.on('redirect-after-delay', (event) => {
                        window.redirectAfterDelay(event.url, event.delay);
                    });
                });
            }
        });
    </script>

    <!-- Script para interceptar cliques em botões de exportar PDF -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Interceptar cliques em botões de exportar PDF
            document.addEventListener('click', function(e) {
                // Verificar se é um botão de exportar PDF
                if (e.target.closest('[wire\\:click*="exportPdf"]')) {
                    // Disparar evento imediatamente
                    window.dispatchEvent(new CustomEvent('download-started', {
                        detail: {
                            message: 'Preparando geração do PDF...'
                        }
                    }));
                }
            });
        });
    </script>

    <?php echo $__env->yieldContent('scripts'); ?>
   <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95c5505ccad18880318521d2bba3eac7)): ?>
<?php $attributes = $__attributesOriginal95c5505ccad18880318521d2bba3eac7; ?>
<?php unset($__attributesOriginal95c5505ccad18880318521d2bba3eac7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95c5505ccad18880318521d2bba3eac7)): ?>
<?php $component = $__componentOriginal95c5505ccad18880318521d2bba3eac7; ?>
<?php unset($__componentOriginal95c5505ccad18880318521d2bba3eac7); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald275691d15a0a68ca98ac956f9920812)): ?>
<?php $attributes = $__attributesOriginald275691d15a0a68ca98ac956f9920812; ?>
<?php unset($__attributesOriginald275691d15a0a68ca98ac956f9920812); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald275691d15a0a68ca98ac956f9920812)): ?>
<?php $component = $__componentOriginald275691d15a0a68ca98ac956f9920812; ?>
<?php unset($__componentOriginald275691d15a0a68ca98ac956f9920812); ?>
<?php endif; ?>
<?php /**PATH C:\projetos\FlowManeger\resources\views/components/layouts/app.blade.php ENDPATH**/ ?>