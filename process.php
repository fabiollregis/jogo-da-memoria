<?php
session_start();
require_once 'game_data.php';

if (isset($_POST['start_game'])) {
    $_SESSION['game_started'] = true;
    $_SESSION['player1_name'] = $_POST['player1'];
    $_SESSION['player2_name'] = $_POST['player2'];
    $_SESSION['current_player'] = 1;
    $_SESSION['player1_score'] = 0;
    $_SESSION['player2_score'] = 0;
    $_SESSION['matched_pairs'] = 0;
    
    // Embaralha os jogadores para criar o tabuleiro
    $game_players = $players;
    shuffle($game_players);
    $_SESSION['game_players'] = $game_players;
    
    header('Location: index.php');
    exit;
}

if (isset($_POST['card_click'])) {
    header('Content-Type: application/json');
    $response = [
        'status' => 'success',
        'current_player' => $_SESSION['current_player'],
        'player1_score' => $_SESSION['player1_score'],
        'player2_score' => $_SESSION['player2_score']
    ];
    
    echo json_encode($response);
    exit;
}

if (isset($_POST['reset_game'])) {
    $_SESSION['current_player'] = 1;
    $_SESSION['player1_score'] = 0;
    $_SESSION['player2_score'] = 0;
    $_SESSION['matched_pairs'] = 0;
    
    // Embaralha os jogadores novamente
    $game_players = $players;
    shuffle($game_players);
    $_SESSION['game_players'] = $game_players;
    
    header('Location: index.php');
    exit;
}

if (isset($_POST['new_players'])) {
    // Limpa todas as variáveis de sessão
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// Se chegou aqui, requisição inválida
http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
?>
