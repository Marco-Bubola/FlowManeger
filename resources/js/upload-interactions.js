// Script para interatividade adicional na página de upload
document.addEventListener('DOMContentLoaded', function() {

    // Função para criar partículas animadas
    function createParticles() {
        const container = document.querySelector('.min-h-screen');
        if (!container) return;

        for (let i = 0; i < 10; i++) {
            setTimeout(() => {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + 'vw';
                particle.style.animationDelay = Math.random() * 3 + 's';
                container.appendChild(particle);

                // Remove partícula após animação
                setTimeout(() => {
                    if (particle.parentNode) {
                        particle.parentNode.removeChild(particle);
                    }
                }, 3000);
            }, i * 500);
        }
    }

    // Criar partículas a cada 5 segundos
    createParticles();
    setInterval(createParticles, 5000);

    // Drag and Drop functionality
    const dropZone = document.querySelector('#file')?.closest('div');
    if (dropZone) {
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const fileInput = document.querySelector('#file');
                if (fileInput) {
                    // Trigger Livewire file update
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(files[0]);
                    fileInput.files = dataTransfer.files;

                    // Dispatch change event
                    fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });
    }

    // Animação de digitação para o título
    function typeWriter(element, text, speed = 100) {
        let i = 0;
        element.innerHTML = '';

        function typing() {
            if (i < text.length) {
                element.innerHTML += text.charAt(i);
                i++;
                setTimeout(typing, speed);
            }
        }

        typing();
    }

    // Observador de interseção para animações
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-slide-in-up');
                entry.target.style.opacity = '1';
            }
        });
    }, observerOptions);

    // Observar elementos para animação
    document.querySelectorAll('.group').forEach(el => {
        el.style.opacity = '0';
        observer.observe(el);
    });

    // Efeito de hover nos botões
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });

        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Contador animado para transações
    function animateCounter(element, target, duration = 2000) {
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current);
        }, 16);
    }

    // Aplicar contador animado se houver transações
    const transactionCounter = document.querySelector('[data-transaction-count]');
    if (transactionCounter) {
        const count = parseInt(transactionCounter.dataset.transactionCount);
        animateCounter(transactionCounter, count);
    }

    // Efeito de ripple nos botões
    function createRipple(event) {
        const button = event.currentTarget;
        const circle = document.createElement('span');
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;

        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${event.clientX - button.offsetLeft - radius}px`;
        circle.style.top = `${event.clientY - button.offsetTop - radius}px`;
        circle.classList.add('ripple');

        const ripple = button.getElementsByClassName('ripple')[0];
        if (ripple) {
            ripple.remove();
        }

        button.appendChild(circle);
    }

    // Adicionar efeito ripple aos botões
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', createRipple);
    });

    // Theme toggle animation
    function initThemeToggle() {
        const html = document.documentElement;
        const currentTheme = localStorage.getItem('theme') || 'light';

        if (currentTheme === 'dark') {
            html.classList.add('dark');
        }

        // Se houver um botão de tema, adicionar funcionalidade
        const themeToggle = document.querySelector('[data-theme-toggle]');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                html.classList.toggle('dark');
                const isDark = html.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');

                // Animação de transição
                document.body.style.transition = 'background-color 0.3s ease';
                setTimeout(() => {
                    document.body.style.transition = '';
                }, 300);
            });
        }
    }

    initThemeToggle();

    // Validação visual em tempo real
    const fileInput = document.querySelector('#file');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const validTypes = ['application/pdf', 'text/csv'];
                const maxSize = 10 * 1024 * 1024; // 10MB

                let isValid = true;
                let message = '';

                if (!validTypes.includes(file.type)) {
                    isValid = false;
                    message = 'Tipo de arquivo inválido. Use apenas PDF ou CSV.';
                }

                if (file.size > maxSize) {
                    isValid = false;
                    message = 'Arquivo muito grande. Máximo 10MB.';
                }

                // Mostrar feedback visual
                const feedback = document.createElement('div');
                feedback.className = `mt-2 p-3 rounded-lg ${isValid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
                feedback.textContent = isValid ? `✅ ${file.name} está pronto!` : `❌ ${message}`;

                // Remove feedback anterior
                const existingFeedback = this.parentNode.querySelector('.file-feedback');
                if (existingFeedback) {
                    existingFeedback.remove();
                }

                feedback.classList.add('file-feedback');
                this.parentNode.appendChild(feedback);

                // Remove feedback após 5 segundos
                setTimeout(() => {
                    if (feedback.parentNode) {
                        feedback.parentNode.removeChild(feedback);
                    }
                }, 5000);
            }
        });
    }

    // Progress bar simulado durante upload
    function showUploadProgress() {
        const progressContainer = document.createElement('div');
        progressContainer.className = 'fixed top-0 left-0 w-full z-50';
        progressContainer.innerHTML = `
            <div class="h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 transform scale-x-0 origin-left transition-transform duration-3000 ease-out"></div>
        `;

        document.body.appendChild(progressContainer);

        // Animar barra de progresso
        setTimeout(() => {
            progressContainer.querySelector('div').style.transform = 'scaleX(1)';
        }, 100);

        // Remover após 3 segundos
        setTimeout(() => {
            if (progressContainer.parentNode) {
                progressContainer.parentNode.removeChild(progressContainer);
            }
        }, 3000);
    }

    // Adicionar progresso nos formulários
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', showUploadProgress);
    });
});

// CSS para o efeito ripple
const rippleCSS = `
    .ripple {
        position: absolute;
        border-radius: 50%;
        transform: scale(0);
        animation: ripple 600ms linear;
        background-color: rgba(255, 255, 255, 0.6);
        pointer-events: none;
    }

    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;

// Adicionar CSS para ripple
const style = document.createElement('style');
style.textContent = rippleCSS;
document.head.appendChild(style);
