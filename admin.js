// Funções para manipular o modal
function closeModal() {
    document.getElementById('playerModal').style.display = 'none';
    
    // Limpa o formulário
    document.getElementById('playerForm').reset();
    document.getElementById('imagePreview').style.display = 'none';
}

// Função para limpar dados
function clearData() {
    if (confirm('⚠️ Tem certeza que deseja limpar todos os dados? Esta ação não pode ser desfeita!')) {
        window.location.href = 'admin_process.php?action=clear_data';
    }
}

// Função para excluir jogador
function deletePlayer(index) {
    if (confirm('Tem certeza que deseja excluir este card?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'admin_process.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const indexInput = document.createElement('input');
        indexInput.type = 'hidden';
        indexInput.name = 'player_index';
        indexInput.value = index;
        
        form.appendChild(actionInput);
        form.appendChild(indexInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Função para editar jogador
function editPlayer(data) {
    const modal = document.getElementById('playerModal');
    const formAction = document.getElementById('formAction');
    const playerIndex = document.getElementById('playerIndex');
    const currentImage = document.getElementById('currentImage');
    const nameInput = document.getElementById('nameInput');
    const ratingInput = document.getElementById('ratingInput');
    const imagePreview = document.getElementById('imagePreview');
    
    document.getElementById('modalTitle').textContent = 'Editar Card';
    formAction.value = 'edit';
    playerIndex.value = data.index;
    currentImage.value = data.image || '';
    nameInput.value = data.name || '';
    ratingInput.value = data.rating || 0;
    
    if (data.image) {
        imagePreview.src = data.image;
        imagePreview.style.display = 'block';
    } else {
        imagePreview.style.display = 'none';
    }
    
    modal.style.display = 'block';
}

// Função para adicionar novo jogador
function addPlayer() {
    const modal = document.getElementById('playerModal');
    const formAction = document.getElementById('formAction');
    const playerIndex = document.getElementById('playerIndex');
    const currentImage = document.getElementById('currentImage');
    const nameInput = document.getElementById('nameInput');
    const ratingInput = document.getElementById('ratingInput');
    const imagePreview = document.getElementById('imagePreview');
    
    document.getElementById('modalTitle').textContent = 'Adicionar Novo Card';
    formAction.value = 'add';
    playerIndex.value = '';
    currentImage.value = '';
    nameInput.value = '';
    ratingInput.value = 0;
    imagePreview.style.display = 'none';
    
    modal.style.display = 'block';
}

// Função para fechar o modal
function closeModal() {
    document.getElementById('playerModal').style.display = 'none';
}

// Função para mostrar preview de imagem
function showImagePreview(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
}

// Preview de imagem
document.getElementById('imageInput').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

// Event listeners quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Theme switcher
    const themeToggle = document.getElementById('themeToggle');
    const html = document.documentElement;
    
    themeToggle.addEventListener('change', () => {
        if (themeToggle.checked) {
            html.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        } else {
            html.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        }
    });
    
    // Configurar tema inicial
    const savedTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', savedTheme);
    themeToggle.checked = savedTheme === 'dark';
    
    // Preview de imagem para cards
    const imageInput = document.getElementById('imageInput');
    imageInput.addEventListener('change', function() {
        showImagePreview(this, 'imagePreview');
    });
    
    // Preview de imagem para verso do card
    const backIconInput = document.getElementById('backIconInput');
    if (backIconInput) {
        backIconInput.addEventListener('change', function() {
            showImagePreview(this, 'backIconPreview');
        });
    }
    
    // Botão adicionar
    const addBtn = document.getElementById('addPlayerBtn');
    if (addBtn) {
        addBtn.addEventListener('click', addPlayer);
    }
    
    // Botão limpar dados
    const clearBtn = document.getElementById('clearDataBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearData);
    }
    
    // Botões de excluir
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            if (index !== null) {
                deletePlayer(index);
            }
        });
    });
    
    // Editar jogador
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const playerCard = this.closest('.player-card');
            const playerIndex = this.dataset.index;
            const playerName = playerCard.querySelector('h3').textContent;
            const playerRating = playerCard.querySelector('.rating').textContent;
            const playerImage = playerCard.querySelector('img').src;

            editPlayer({ index: playerIndex, name: playerName, rating: playerRating, image: playerImage });
        });
    });

    // Fechar modal
    const closeBtn = document.querySelector('.close');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }
    
    // Fechar modal ao clicar fora
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('playerModal');
        if (e.target === modal) {
            closeModal();
        }
    });
});
