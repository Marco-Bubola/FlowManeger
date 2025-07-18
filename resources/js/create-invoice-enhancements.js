document.addEventListener('DOMContentLoaded', function() {
    // Adicionar efeitos de foco melhorados nos inputs
    const inputs = document.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('ring-4', 'ring-blue-500/20');
        });

        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('ring-4', 'ring-blue-500/20');
        });
    });

    // Animação no botão de submit
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            if (this.closest('form').checkValidity()) {
                this.classList.add('btn-loading');
                this.innerHTML = `
                    <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Salvando...
                `;
                this.disabled = true;
            }
        });
    }

    // Mask para o campo de valor
    const valueInput = document.getElementById('value');
    if (valueInput) {
        valueInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2);
            value = value.replace('.', ',');
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = value;
        });
    }

    // Auto-complete para descrição baseado na categoria
    const categorySelect = document.getElementById('category_id');
    const descriptionInput = document.getElementById('description');

    if (categorySelect && descriptionInput) {
        const categoryDescriptions = {
            // Você pode adicionar sugestões baseadas nas categorias
            'alimentação': ['Almoço', 'Jantar', 'Lanche', 'Supermercado'],
            'transporte': ['Combustível', 'Uber', 'Ônibus', 'Estacionamento'],
            'educação': ['Curso', 'Livro', 'Material escolar', 'Mensalidade'],
            'saúde': ['Consulta médica', 'Medicamento', 'Exame', 'Plano de saúde']
        };

        categorySelect.addEventListener('change', function() {
            const selectedText = this.options[this.selectedIndex].text.toLowerCase();
            if (categoryDescriptions[selectedText] && !descriptionInput.value) {
                descriptionInput.placeholder = `Ex: ${categoryDescriptions[selectedText][0]}`;
            }
        });
    }

    // Validação em tempo real
    const requiredFields = document.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (!this.value) {
                this.classList.add('border-red-500');
                this.classList.remove('border-gray-200');
            } else {
                this.classList.remove('border-red-500');
                this.classList.add('border-green-500');
                setTimeout(() => {
                    this.classList.remove('border-green-500');
                    this.classList.add('border-gray-200');
                }, 2000);
            }
        });
    });

    // Adicionar suporte para teclas de atalho
    document.addEventListener('keydown', function(e) {
        // Ctrl + S para salvar
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            submitBtn?.click();
        }

        // Escape para cancelar
        if (e.key === 'Escape') {
            const cancelBtn = document.querySelector('a[href*="invoices.index"]');
            if (cancelBtn) {
                cancelBtn.click();
            }
        }
    });

    // Adicionar tooltip aos ícones
    const iconContainers = document.querySelectorAll('.group .w-8.h-8');
    iconContainers.forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            const label = this.closest('.group').querySelector('label span');
            if (label) {
                icon.title = `Campo: ${label.textContent}`;
            }
        });
    });
});
