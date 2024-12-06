<?php
session_start();

// Se o jogo já foi configurado, redireciona para a página inicial
if (isset($_SESSION['game_configured']) && $_SESSION['game_configured']) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuração do Jogo da Memória</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="wizard.css">
</head>
<body>
    <div class="wizard-container">
        <div class="wizard-header">
            <h1>⚙️ Configuração do Jogo</h1>
            <div class="wizard-progress">
                <div class="progress-step active" data-step="1">1</div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="2">2</div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="3">3</div>
            </div>
        </div>

        <form id="wizardForm" action="wizard_process.php" method="POST" enctype="multipart/form-data">
            <!-- Step 1: Configurações Básicas -->
            <div class="wizard-step" data-step="1">
                <h2>Configurações Básicas</h2>
                <div class="form-group">
                    <label for="gameTitle">Título do Jogo</label>
                    <input type="text" id="gameTitle" name="gameTitle" required 
                           placeholder="Ex: Jogo da Memória - Futebol Europeu">
                </div>
                <div class="form-group">
                    <label for="cardPairs">Número de Pares de Cards</label>
                    <select id="cardPairs" name="cardPairs" required>
                        <option value="6">6 pares (12 cards)</option>
                        <option value="8" selected>8 pares (16 cards)</option>
                        <option value="10">10 pares (20 cards)</option>
                        <option value="12">12 pares (24 cards)</option>
                        <option value="16">16 pares (32 cards)</option>
                    </select>
                </div>
            </div>

            <!-- Step 2: Upload da Capa do Card -->
            <div class="wizard-step hidden" data-step="2">
                <h2>Capa do Card</h2>
                <div class="form-group">
                    <label for="cardBack">Imagem da Capa do Card</label>
                    <div class="upload-area" id="cardBackUpload">
                        <input type="file" id="cardBack" name="cardBack" accept="image/*" required>
                        <div class="preview-container">
                            <img id="cardBackPreview" src="#" alt="Preview da capa">
                        </div>
                        <p class="upload-info">Arraste uma imagem ou clique para selecionar</p>
                    </div>
                    <p class="help-text">Recomendado: 300x400 pixels, formato PNG ou JPG</p>
                </div>
            </div>

            <!-- Step 3: Upload dos Cards -->
            <div class="wizard-step hidden" data-step="3">
                <h2>Cards do Jogo</h2>
                <div class="form-group">
                    <label>Upload dos Cards</label>
                    <div class="upload-area" id="cardsUpload">
                        <input type="file" id="cardImages" name="cardImages[]" accept="image/*" multiple required>
                        <div class="preview-grid" id="cardsPreview"></div>
                        <p class="upload-info">Arraste as imagens ou clique para selecionar</p>
                    </div>
                    <p class="help-text">Selecione <span id="requiredPairs">8</span> imagens para os cards</p>
                </div>
            </div>

            <div class="wizard-buttons">
                <button type="button" id="prevStep" class="btn secondary hidden">Anterior</button>
                <button type="button" id="nextStep" class="btn primary">Próximo</button>
                <button type="submit" id="finishWizard" class="btn primary hidden">Finalizar</button>
            </div>
        </form>
    </div>

    <script src="wizard.js"></script>
</body>
</html>
