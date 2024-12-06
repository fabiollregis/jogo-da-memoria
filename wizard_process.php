<?php
session_start();

function handleError($message) {
    http_response_code(400);
    echo $message;
    exit;
}

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError('Método não permitido');
}

// Valida e processa os dados básicos
$gameTitle = filter_input(INPUT_POST, 'gameTitle', FILTER_SANITIZE_STRING);
$cardPairs = filter_input(INPUT_POST, 'cardPairs', FILTER_VALIDATE_INT);

if (!$gameTitle || !$cardPairs) {
    handleError('Dados básicos inválidos');
}

// Processa o upload da capa do card
if (!isset($_FILES['cardBack']) || $_FILES['cardBack']['error'] !== UPLOAD_ERR_OK) {
    handleError('Erro no upload da capa do card');
}

$cardBackFile = $_FILES['cardBack'];
$cardBackExt = strtolower(pathinfo($cardBackFile['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png'];

if (!in_array($cardBackExt, $allowedExtensions)) {
    handleError('Formato de arquivo não permitido para a capa');
}

// Move a capa do card para o diretório correto
$cardBackPath = 'images/card-back.' . $cardBackExt;
if (!move_uploaded_file($cardBackFile['tmp_name'], $cardBackPath)) {
    handleError('Erro ao salvar a capa do card');
}

// Processa os cards do jogo
if (!isset($_FILES['cardImages'])) {
    handleError('Nenhum card foi enviado');
}

$cardImages = $_FILES['cardImages'];
$totalCards = count($cardImages['name']);

if ($totalCards !== $cardPairs) {
    handleError("Número incorreto de cards. Esperado: $cardPairs, Recebido: $totalCards");
}

// Limpa o diretório de imagens dos players
$playerImagesDir = 'player_images';
array_map('unlink', glob("$playerImagesDir/*"));

// Processa cada imagem dos cards
$cardData = [];
require_once 'db_config.php';
$conn = connectDB();

// Limpa a tabela de players
$conn->query("TRUNCATE TABLE players");

for ($i = 0; $i < $totalCards; $i++) {
    if ($cardImages['error'][$i] !== UPLOAD_ERR_OK) {
        handleError('Erro no upload do card ' . ($i + 1));
    }

    $ext = strtolower(pathinfo($cardImages['name'][$i], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        handleError('Formato de arquivo não permitido para o card ' . ($i + 1));
    }

    $fileName = uniqid('card_') . '.' . $ext;
    $filePath = "$playerImagesDir/$fileName";

    if (!move_uploaded_file($cardImages['tmp_name'][$i], $filePath)) {
        handleError('Erro ao salvar o card ' . ($i + 1));
    }

    $playerName = pathinfo($cardImages['name'][$i], PATHINFO_FILENAME);
    
    // Define o rating baseado no nome do jogo
    $ratings = [
        'cs_go' => 91,
        'deadside' => 85,
        'efootball' => 88,
        'fortnite' => 90,
        'free fire' => 86,
        'Gran Turismo' => 92,
        'GTA-V' => 95,
        'Midnight Club' => 87,
        'Minecraft' => 93,
        'planta-zomb' => 84,
        'Project Zomboid' => 88,
        'PUBG' => 89,
        'Sonic' => 86,
        'street' => 90,
        'stumble guys' => 83,
        'valorant' => 92
    ];
    
    $rating = isset($ratings[$playerName]) ? $ratings[$playerName] : 85;
    
    // Salva no banco de dados
    $stmt = $conn->prepare("INSERT INTO players (name, image_path, rating) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $playerName, $filePath, $rating);
    if (!$stmt->execute()) {
        handleError('Erro ao salvar o card no banco de dados: ' . $conn->error);
    }
    $stmt->close();

    $cardData[] = [
        'name' => $playerName,
        'image' => $filePath,
        'rating' => $rating
    ];
}

// Atualiza as configurações do jogo no banco de dados
$stmt = $conn->prepare("INSERT INTO game_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
$stmt->bind_param("ss", $settingKey, $settingValue);

$settingKey = 'card_back';
$settingValue = $cardBackPath;
$stmt->execute();

$conn->close();

// Atualiza o arquivo de configuração
$configContent = "<?php\n";
$configContent .= "// Configurações do jogo\n";
$configContent .= "\$gameTitle = " . var_export($gameTitle, true) . ";\n";
$configContent .= "\$backIcon = " . var_export($cardBackPath, true) . ";\n";
$configContent .= "\$cardPairs = " . var_export($cardPairs, true) . ";\n";
$configContent .= "?>";

if (!file_put_contents('config.php', $configContent)) {
    handleError('Erro ao salvar as configurações');
}

// Atualiza o arquivo de dados do jogo
$gameDataContent = "<?php\n";
$gameDataContent .= "// Dados dos cards do jogo\n";
$gameDataContent .= "\$players = " . var_export($cardData, true) . ";\n";
$gameDataContent .= "?>";

if (!file_put_contents('game_data.php', $gameDataContent)) {
    handleError('Erro ao salvar os dados do jogo');
}

// Marca o jogo como configurado
$_SESSION['game_configured'] = true;

// Retorna sucesso
http_response_code(200);
echo 'Configuração concluída com sucesso!';
