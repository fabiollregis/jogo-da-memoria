document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('wizardForm');
    const steps = document.querySelectorAll('.wizard-step');
    const progressSteps = document.querySelectorAll('.progress-step');
    const progressLines = document.querySelectorAll('.progress-line');
    const prevButton = document.getElementById('prevStep');
    const nextButton = document.getElementById('nextStep');
    const finishButton = document.getElementById('finishWizard');
    const cardPairsSelect = document.getElementById('cardPairs');
    const requiredPairsSpan = document.getElementById('requiredPairs');
    
    let currentStep = 1;

    // Atualiza o número de pares necessários quando o select muda
    cardPairsSelect.addEventListener('change', (e) => {
        requiredPairsSpan.textContent = e.target.value;
    });

    // Preview da capa do card
    const cardBackInput = document.getElementById('cardBack');
    const cardBackPreview = document.getElementById('cardBackPreview');

    cardBackInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                cardBackPreview.style.display = 'block';
                cardBackPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Preview dos cards
    const cardImagesInput = document.getElementById('cardImages');
    const cardsPreview = document.getElementById('cardsPreview');

    cardImagesInput.addEventListener('change', (e) => {
        cardsPreview.innerHTML = '';
        const files = Array.from(e.target.files);
        const requiredPairs = parseInt(cardPairsSelect.value);

        if (files.length !== requiredPairs) {
            alert(`Por favor, selecione exatamente ${requiredPairs} imagens para os cards.`);
            cardImagesInput.value = '';
            return;
        }

        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.createElement('img');
                img.src = e.target.result;
                cardsPreview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });

    // Drag and drop para as áreas de upload
    const uploadAreas = document.querySelectorAll('.upload-area');
    
    uploadAreas.forEach(area => {
        area.addEventListener('dragover', (e) => {
            e.preventDefault();
            area.style.borderColor = 'var(--primary-color)';
        });

        area.addEventListener('dragleave', (e) => {
            e.preventDefault();
            area.style.borderColor = 'var(--border-color)';
        });

        area.addEventListener('drop', (e) => {
            e.preventDefault();
            area.style.borderColor = 'var(--border-color)';
            const input = area.querySelector('input[type="file"]');
            const dt = e.dataTransfer;
            input.files = dt.files;
            input.dispatchEvent(new Event('change'));
        });
    });

    // Navegação entre steps
    function updateStep(direction) {
        const newStep = currentStep + direction;
        if (newStep < 1 || newStep > steps.length) return;

        // Validação do step atual antes de avançar
        if (direction > 0 && !validateCurrentStep()) return;

        steps[currentStep - 1].classList.add('hidden');
        steps[newStep - 1].classList.remove('hidden');

        progressSteps[currentStep - 1].classList.remove('active');
        progressSteps[newStep - 1].classList.add('active');

        if (direction > 0) {
            progressSteps[currentStep - 1].classList.add('completed');
            progressLines[currentStep - 1]?.classList.add('completed');
        } else {
            progressSteps[currentStep].classList.remove('completed');
            progressLines[currentStep - 1]?.classList.remove('completed');
        }

        currentStep = newStep;
        updateButtons();
    }

    function validateCurrentStep() {
        const currentStepElement = steps[currentStep - 1];
        const inputs = currentStepElement.querySelectorAll('input, select');
        
        for (const input of inputs) {
            if (input.hasAttribute('required') && !input.value) {
                alert('Por favor, preencha todos os campos obrigatórios.');
                return false;
            }
        }

        if (currentStep === 3) {
            const files = cardImagesInput.files;
            const requiredPairs = parseInt(cardPairsSelect.value);
            if (files.length !== requiredPairs) {
                alert(`Por favor, selecione exatamente ${requiredPairs} imagens para os cards.`);
                return false;
            }
        }

        return true;
    }

    function updateButtons() {
        prevButton.classList.toggle('hidden', currentStep === 1);
        nextButton.classList.toggle('hidden', currentStep === steps.length);
        finishButton.classList.toggle('hidden', currentStep !== steps.length);
    }

    prevButton.addEventListener('click', () => updateStep(-1));
    nextButton.addEventListener('click', () => updateStep(1));

    // Submissão do formulário
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!validateCurrentStep()) return;

        const formData = new FormData(form);
        try {
            const response = await fetch('wizard_process.php', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                window.location.href = 'index.php';
            } else {
                const error = await response.text();
                alert('Erro ao salvar as configurações: ' + error);
            }
        } catch (error) {
            alert('Erro ao enviar o formulário: ' + error.message);
        }
    });
});
