<?php
session_start();
require_once 'db_config.php';

// Função para processar upload de imagem
function handleImageUpload($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $uploadDir = 'player_images/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($file['name']);
    $targetFile = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return $targetFile;
    }
    return false;
}

// Função para remover imagem
function removeImage($imagePath) {
    if (!empty($imagePath) && file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// Verificar se há uma ação definida
$action = $_POST['action'] ?? $_GET['action'] ?? '';
if (empty($action)) {
    $_SESSION['admin_message'] = '❌ Ação não especificada';
    header('Location: admin.php');
    exit;
}

$conn = connectDB();

switch ($action) {
    case 'add':
        try {
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                throw new Exception('Nome é obrigatório');
            }
            
            $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
            if ($rating < 0 || $rating > 100) {
                throw new Exception('Avaliação deve estar entre 0 e 100');
            }
            
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $image_path = handleImageUpload($_FILES['image']);
                if (!$image_path) {
                    throw new Exception('Erro ao fazer upload da imagem');
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO players (name, rating, image_path) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $name, $rating, $image_path);
            
            if (!$stmt->execute()) {
                throw new Exception('Erro ao adicionar card');
            }
            
            $_SESSION['admin_message'] = '✅ Card adicionado com sucesso!';
        } catch (Exception $e) {
            if (isset($image_path) && !empty($image_path)) {
                removeImage($image_path);
            }
            $_SESSION['admin_message'] = '❌ ' . $e->getMessage();
        }
        break;
        
    case 'edit':
        try {
            $id = isset($_POST['player_id']) ? (int)$_POST['player_id'] : 0;
            if ($id <= 0) {
                throw new Exception('Card não encontrado');
            }
            
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                throw new Exception('Nome é obrigatório');
            }
            
            $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
            if ($rating < 0 || $rating > 100) {
                throw new Exception('Avaliação deve estar entre 0 e 100');
            }
            
            // Buscar imagem atual
            $stmt = $conn->prepare("SELECT image_path FROM players WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $current_image = $result->fetch_assoc()['image_path'] ?? '';
            
            // Se uma nova imagem foi enviada
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $new_image = handleImageUpload($_FILES['image']);
                if ($new_image) {
                    removeImage($current_image);
                    $current_image = $new_image;
                }
            }
            
            $stmt = $conn->prepare("UPDATE players SET name = ?, rating = ?, image_path = ? WHERE id = ?");
            $stmt->bind_param("sisi", $name, $rating, $current_image, $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Erro ao atualizar card');
            }
            
            $_SESSION['admin_message'] = '✅ Card atualizado com sucesso!';
        } catch (Exception $e) {
            $_SESSION['admin_message'] = '❌ ' . $e->getMessage();
        }
        break;
        
    case 'delete':
        try {
            $id = isset($_POST['player_id']) ? (int)$_POST['player_id'] : 0;
            if ($id <= 0) {
                throw new Exception('Card não encontrado');
            }
            
            // Buscar imagem antes de deletar
            $stmt = $conn->prepare("SELECT image_path FROM players WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $image_path = $result->fetch_assoc()['image_path'] ?? '';
            
            // Deletar o registro
            $stmt = $conn->prepare("DELETE FROM players WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Erro ao remover card');
            }
            
            // Remover a imagem
            removeImage($image_path);
            
            $_SESSION['admin_message'] = '✅ Card removido com sucesso!';
        } catch (Exception $e) {
            $_SESSION['admin_message'] = '❌ ' . $e->getMessage();
        }
        break;

    case 'clear_data':
        try {
            // Buscar todas as imagens
            $result = $conn->query("SELECT image_path FROM players");
            while ($row = $result->fetch_assoc()) {
                removeImage($row['image_path']);
            }
            
            // Limpar tabela de jogadores
            $conn->query("TRUNCATE TABLE players");
            
            // Buscar e remover imagem do verso do card
            $result = $conn->query("SELECT setting_value FROM game_settings WHERE setting_key = 'card_back'");
            $cardBack = $result->fetch_assoc()['setting_value'] ?? '';
            removeImage($cardBack);
            
            // Resetar verso do card
            $conn->query("UPDATE game_settings SET setting_value = '' WHERE setting_key = 'card_back'");
            
            // Limpar sessão de configuração
            unset($_SESSION['game_configured']);
            
            $_SESSION['admin_message'] = '✅ Todos os dados foram limpos com sucesso!';
        } catch (Exception $e) {
            $_SESSION['admin_message'] = '❌ ' . $e->getMessage();
        }
        break;
        
    case 'update_card_back':
        try {
            if (!isset($_FILES['card_back']) || $_FILES['card_back']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Selecione uma imagem para o verso do card');
            }
            
            // Buscar imagem atual do verso
            $result = $conn->query("SELECT setting_value FROM game_settings WHERE setting_key = 'card_back'");
            $current_back = $result->fetch_assoc()['setting_value'] ?? '';
            
            // Upload da nova imagem
            $new_back = handleImageUpload($_FILES['card_back']);
            if (!$new_back) {
                throw new Exception('Erro ao fazer upload da imagem');
            }
            
            // Remover imagem antiga
            removeImage($current_back);
            
            // Atualizar no banco
            $stmt = $conn->prepare("UPDATE game_settings SET setting_value = ? WHERE setting_key = 'card_back'");
            $stmt->bind_param("s", $new_back);
            
            if (!$stmt->execute()) {
                throw new Exception('Erro ao atualizar verso do card');
            }
            
            $_SESSION['admin_message'] = '✅ Verso do card atualizado com sucesso!';
        } catch (Exception $e) {
            $_SESSION['admin_message'] = '❌ ' . $e->getMessage();
        }
        break;
        
    case 'toggle_theme':
        try {
            // Buscar configuração atual do tema
            $result = $conn->query("SELECT setting_value FROM game_settings WHERE setting_key = 'dark_mode'");
            $currentTheme = $result->fetch_assoc()['setting_value'] ?? '0';
            
            // Alternar entre modo claro e escuro
            $newTheme = $currentTheme === '1' ? '0' : '1';
            
            // Atualizar no banco de dados
            $stmt = $conn->prepare("UPDATE game_settings SET setting_value = ? WHERE setting_key = 'dark_mode'");
            $stmt->bind_param("s", $newTheme);
            
            if (!$stmt->execute()) {
                throw new Exception('Erro ao atualizar tema');
            }
            
            echo 'success';
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo $e->getMessage();
            exit;
        }
        break;
        
    default:
        $_SESSION['admin_message'] = '❌ Ação inválida';
}

$conn->close();
header('Location: admin.php');
exit;
