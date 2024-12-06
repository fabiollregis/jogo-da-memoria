<?php
session_start();

// Verifica se o jogo foi configurado
if (!isset($_SESSION['game_configured']) && !file_exists('game_data.php')) {
    header('Location: wizard.php');
    exit;
}

require_once 'game_data.php';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogo da Memória - Futebol Europeu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
</head>
<body>
    <div class="theme-switch">
        <label class="switch">
            <input type="checkbox" id="themeToggle">
            <span class="slider round"></span>
            <span class="theme-label">🌙</span>
        </label>
    </div>

    <div class="admin-link">
        <a href="admin.php" class="admin-button">⚙️ Admin</a>
    </div>

    <div class="container">
        <?php if (!isset($_SESSION['game_started'])): ?>
        <div class="player-setup" id="playerSetup">
            <h2>⚽ Jogo da Memória</h2>
            <div class="player-inputs">
                <form action="process.php" method="POST">
                    <input type="text" name="player1" placeholder="Nome do Jogador 1" required>
                    <input type="text" name="player2" placeholder="Nome do Jogador 2" required>
                    <button type="submit" name="start_game" id="startGame">Começar Jogo</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="game-container" id="gameContainer">
            <div class="score-board">
                <div class="player-score <?php echo $_SESSION['current_player'] == 1 ? 'current' : ''; ?>" id="player1Score">
                    <div class="player-info">
                        <span class="player-name"><?php echo htmlspecialchars($_SESSION['player1_name']); ?></span>
                        <span class="score"><?php echo $_SESSION['player1_score']; ?></span>
                    </div>
                </div>
                <div class="player-turn" id="playerTurn" data-current-player="<?php echo $_SESSION['current_player']; ?>">
                    Vez de: <?php echo htmlspecialchars($_SESSION['current_player'] == 1 ? $_SESSION['player1_name'] : $_SESSION['player2_name']); ?>
                </div>
                <div class="player-score <?php echo $_SESSION['current_player'] == 2 ? 'current' : ''; ?>" id="player2Score">
                    <div class="player-info">
                        <span class="player-name"><?php echo htmlspecialchars($_SESSION['player2_name']); ?></span>
                        <span class="score"><?php echo $_SESSION['player2_score']; ?></span>
                    </div>
                </div>
            </div>
            <div class="game-controls">
                <form action="process.php" method="POST" class="control-buttons">
                    <button type="submit" name="reset_game" class="control-btn">Reiniciar Jogo</button>
                    <button type="submit" name="new_players" class="control-btn">Novos Jogadores</button>
                </form>
            </div>
            <div class="memory-game" id="memoryGame">
                <?php include 'render_cards.php'; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <script src="script.js"></script>
</body>
</html>
