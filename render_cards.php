<?php
if (!isset($_SESSION['game_players'])) {
    $_SESSION['game_players'] = $players;
    shuffle($_SESSION['game_players']);
}

// Carrega a configuração do verso dos cards
require_once 'config.php';

// Duplica os jogadores para criar pares
$game_cards = array_merge($_SESSION['game_players'], $_SESSION['game_players']);
shuffle($game_cards);

foreach ($game_cards as $index => $player) {
    // Verifica se a imagem existe e ajusta o caminho se necessário
    $image_path = ltrim($player['image'], './');
    ?>
    <div class="card" data-player="<?php echo htmlspecialchars($player['name']); ?>" data-index="<?php echo $index; ?>">
        <div class="card-front">
            <img src="<?php echo htmlspecialchars($image_path); ?>" 
                 alt="<?php echo htmlspecialchars($player['name']); ?>"
                 onerror="this.onerror=null; this.src='images/default-card.jpg';">
            <div class="player-name">
                <?php echo htmlspecialchars($player['name']); ?>
                <?php if (isset($player['rating']) && $player['rating'] > 0): ?>
                    <span class="rating"><?php echo $player['rating']; ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-back">
            <img src="<?php echo htmlspecialchars($backIcon); ?>" 
                 alt="Verso do card"
                 onerror="this.onerror=null; this.src='images/default-back.jpg';">
        </div>
    </div>
    <?php
}
?>
