const players = [
    { 
        name: 'Erling Haaland', 
        rating: 91, 
        image: 'images/haaland.jpg'
    },
    { 
        name: 'Kylian Mbappé', 
        rating: 91, 
        image: 'images/mbappe.jpg'
    },
    { 
        name: 'Kevin De Bruyne', 
        rating: 91, 
        image: 'images/debruyne.jpg'
    },
    { 
        name: 'Mohamed Salah', 
        rating: 89, 
        image: 'images/salah.jpg'
    },
    { 
        name: 'Virgil van Dijk', 
        rating: 89, 
        image: 'images/vandijk.jpg'
    },
    { 
        name: 'Thibaut Courtois', 
        rating: 90, 
        image: 'images/courtois.jpg'
    },
    { 
        name: 'Robert Lewandowski', 
        rating: 90, 
        image: 'images/lewandowski.jpg'
    },
    { 
        name: 'Luka Modric', 
        rating: 88, 
        image: 'images/modric.jpg'
    }
];

document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    const themeLabel = document.querySelector('.theme-label');
    
    // Carrega o tema salvo
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    themeToggle.checked = savedTheme === 'dark';
    themeLabel.textContent = savedTheme === 'dark' ? '☀️' : '🌙';
    
    // Alterna o tema
    themeToggle.addEventListener('change', function() {
        const theme = this.checked ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        themeLabel.textContent = theme === 'dark' ? '☀️' : '🌙';
    });
    
    initializeGame();
});

let currentPlayer = 1;
let player1Score = 0;
let player2Score = 0;
let flippedCards = [];
let matchedPairs = 0;
let canFlip = true;

function initializeGame() {
    currentPlayer = parseInt(document.querySelector('#playerTurn').dataset.currentPlayer) || 1;
    player1Score = parseInt(document.querySelector('#player1Score .score').textContent) || 0;
    player2Score = parseInt(document.querySelector('#player2Score .score').textContent) || 0;
    flippedCards = [];
    matchedPairs = 0;
    canFlip = false; // Impede que as cartas sejam viradas durante o preview

    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.classList.remove('flipped', 'matched');
        card.addEventListener('click', handleCardClick);
    });

    updateCurrentPlayer();
    
    // Mostra todas as cartas no início
    cards.forEach(card => card.classList.add('flipped'));
    
    // Após 1 segundo, vira todas as cartas de volta
    setTimeout(() => {
        cards.forEach(card => card.classList.remove('flipped'));
        canFlip = true; // Permite que as cartas sejam viradas após o preview
    }, 1000);
}

function handleCardClick(event) {
    if (!canFlip) return; // Impede cliques durante o preview
    
    const card = event.currentTarget;
    
    // Não permite clicar em cartas já viradas ou combinadas
    if (card.classList.contains('matched') || card.classList.contains('flipped')) {
        return;
    }
    
    // Não permite virar mais de duas cartas
    if (flippedCards.length >= 2) {
        return;
    }
    
    // Vira a carta
    card.classList.add('flipped');
    flippedCards.push(card);
    
    // Verifica se duas cartas foram viradas
    if (flippedCards.length === 2) {
        checkMatch();
    }
}

function checkMatch() {
    const [card1, card2] = flippedCards;
    const match = card1.dataset.player === card2.dataset.player;

    if (match) {
        // Se as cartas combinam
        card1.classList.add('matched');
        card2.classList.add('matched');
        
        // Atualiza o placar
        if (currentPlayer === 1) {
            player1Score++;
            document.querySelector('#player1Score .score').textContent = player1Score;
        } else {
            player2Score++;
            document.querySelector('#player2Score .score').textContent = player2Score;
        }
        
        matchedPairs++;
        flippedCards = [];
        
        // Verifica se o jogo acabou
        if (matchedPairs === document.querySelectorAll('.card').length / 2) {
            setTimeout(showGameOver, 500);
        }
    } else {
        // Se as cartas não combinam, espera um pouco e desvira
        setTimeout(() => {
            card1.classList.remove('flipped');
            card2.classList.remove('flipped');
            flippedCards = [];
            // Troca o jogador apenas quando erra
            currentPlayer = currentPlayer === 1 ? 2 : 1;
            updateCurrentPlayer();
        }, 1500);
    }
    
    // Atualiza o servidor
    updateServer(match);
}

function updateCurrentPlayer() {
    const playerTurn = document.querySelector('#playerTurn');
    const player1Score = document.querySelector('#player1Score');
    const player2Score = document.querySelector('#player2Score');
    
    // Remove a classe 'current' de ambos os jogadores
    player1Score.classList.remove('current');
    player2Score.classList.remove('current');
    
    // Adiciona a classe 'current' ao jogador atual
    if (currentPlayer === 1) {
        player1Score.classList.add('current');
    } else {
        player2Score.classList.add('current');
    }
    
    const currentPlayerName = document.querySelector(`#player${currentPlayer}Score .player-name`).textContent.trim();
    playerTurn.textContent = `Vez de: ${currentPlayerName}`;
    playerTurn.dataset.currentPlayer = currentPlayer;
}

function showGameOver() {
    const player1Name = document.querySelector('#player1Score .player-name').textContent.trim();
    const player2Name = document.querySelector('#player2Score .player-name').textContent.trim();
    
    let winnerName;
    if (player1Score > player2Score) {
        winnerName = player1Name;
    } else if (player2Score > player1Score) {
        winnerName = player2Name;
    } else {
        winnerName = "Empate!";
    }
    
    // Criar o elemento de vitória
    const victoryScreen = document.createElement('div');
    victoryScreen.className = 'victory-screen';
    
    const winnerText = document.createElement('div');
    winnerText.className = 'winner-text';
    winnerText.textContent = winnerName;
    
    const scoreText = document.createElement('div');
    scoreText.className = 'score-text';
    if (winnerName !== "Empate!") {
        scoreText.textContent = `Venceu com ${Math.max(player1Score, player2Score)} pares!`;
    }
    
    victoryScreen.appendChild(winnerText);
    victoryScreen.appendChild(scoreText);
    document.body.appendChild(victoryScreen);

    // Adicionar efeito de fogos de artifício
    const duration = 15 * 1000;
    const animationEnd = Date.now() + duration;
    const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

    function randomInRange(min, max) {
        return Math.random() * (max - min) + min;
    }

    const interval = setInterval(function() {
        const timeLeft = animationEnd - Date.now();

        if (timeLeft <= 0) {
            return clearInterval(interval);
        }

        const particleCount = 50 * (timeLeft / duration);
        
        // Fogos de artifício em posições aleatórias
        confetti({
            ...defaults,
            particleCount,
            origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
        });
        confetti({
            ...defaults,
            particleCount,
            origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
        });
    }, 250);

    // Remover a tela de vitória após alguns segundos
    setTimeout(() => {
        victoryScreen.remove();
    }, duration);
}

function updateServer(match) {
    const formData = new FormData();
    formData.append('card_click', '1');
    formData.append('match', match ? '1' : '0');
    formData.append('current_player', currentPlayer);
    
    fetch('process.php', {
        method: 'POST',
        body: formData
    })
    .catch(error => console.error('Erro:', error));
}

function resetGame() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'process.php';
    
    const resetInput = document.createElement('input');
    resetInput.type = 'hidden';
    resetInput.name = 'reset_game';
    resetInput.value = '1';
    
    form.appendChild(resetInput);
    document.body.appendChild(form);
    form.submit();
}

function newPlayers() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'process.php';
    
    const newPlayersInput = document.createElement('input');
    newPlayersInput.type = 'hidden';
    newPlayersInput.name = 'new_players';
    newPlayersInput.value = '1';
    
    form.appendChild(newPlayersInput);
    document.body.appendChild(form);
    form.submit();
}
