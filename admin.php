<?php
session_start();
require_once 'db_config.php';

$conn = connectDB();

// Buscar todos os jogadores
$players = [];
$result = $conn->query("SELECT * FROM players ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $players[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'rating' => $row['rating'],
        'image' => $row['image_path']
    ];
}

// Buscar verso do card e modo escuro
$result = $conn->query("SELECT setting_key, setting_value FROM game_settings WHERE setting_key IN ('card_back', 'dark_mode')");
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$cardBack = $settings['card_back'] ?? '';
$darkMode = $settings['dark_mode'] ?? '0';

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?php echo $darkMode === '1' ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração - Jogo da Memória</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff;
            --danger-color: #dc3545;
            --success-color: #28a745;
            --border-radius: 8px;
            --transition: all 0.3s ease;
        }

        :root[data-theme="light"] {
            --bg-color: #f5f5f5;
            --card-bg: #ffffff;
            --text-color: #333333;
            --border-color: #dddddd;
            --modal-overlay: rgba(0, 0, 0, 0.5);
        }

        :root[data-theme="dark"] {
            --bg-color: #1a1a1a;
            --card-bg: #2d2d2d;
            --text-color: #ffffff;
            --border-color: #404040;
            --modal-overlay: rgba(0, 0, 0, 0.7);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: var(--transition);
            min-height: 100vh;
            position: relative;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .admin-header h1 {
            font-size: 2rem;
            font-weight: 700;
        }

        .admin-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn.primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn.danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn.success {
            background-color: var(--success-color);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Estilo específico para o botão Voltar ao Jogo no modo dark */
        :root[data-theme="dark"] .btn:not(.primary):not(.danger):not(.success) {
            background-color: #4a4a4a;
            color: #ffffff;
        }

        .players-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .player-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .player-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .player-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
        }

        .player-info {
            text-align: center;
            margin-bottom: 1rem;
        }

        .player-info h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .rating {
            font-size: 1.1rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .player-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--modal-overlay);
            z-index: 1000;
        }

        .modal-content {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            margin: 2rem auto;
            position: relative;
        }

        .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .image-preview-wrapper {
            margin-top: 1rem;
        }

        .preview {
            max-width: 200px;
            margin-top: 1rem;
        }

        .preview img {
            width: 100%;
            height: auto;
            border-radius: var(--border-radius);
        }

        .message {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            background-color: var(--primary-color);
            color: white;
            opacity: 1;
            transition: opacity 0.5s ease;
        }

        .back-icon-section {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .back-icon-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .image-preview-container {
            max-width: 200px;
        }

        .theme-switch {
            position: fixed;
            left: 20px;
            top: 20px;
            z-index: 1000;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--primary-color);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .theme-label {
            position: absolute;
            right: -30px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                text-align: center;
            }

            .admin-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="theme-switch">
        <label class="switch">
            <input type="checkbox" id="themeToggle" <?php echo $darkMode === '1' ? 'checked' : ''; ?>>
            <span class="slider round"></span>
            <span class="theme-label"><?php echo $darkMode === '1' ? '☀️' : '🌙'; ?></span>
        </label>
    </div>

    <div class="admin-container">
        <div class="admin-header">
            <h1>⚙️ Painel Administrativo</h1>
            <div class="admin-actions">
                <a href="wizard.php" class="btn primary">🎮 Configurar Jogo</a>
                <a href="index.php" class="btn">↩️ Voltar ao Jogo</a>
                <button id="clearDataBtn" class="btn danger">🗑️ Limpar Dados</button>
            </div>
        </div>

        <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="message" id="messageBox">
            <?php 
            echo $_SESSION['admin_message'];
            unset($_SESSION['admin_message']);
            ?>
        </div>
        <?php endif; ?>

        <div class="admin-content">
            <div class="section-header">
                <h2>Cards do Jogo</h2>
                <button class="btn primary" id="addPlayerBtn">➕ Adicionar Novo Card</button>
            </div>

            <div class="players-grid">
                <?php foreach ($players as $player): ?>
                <div class="player-card">
                    <img src="<?php echo htmlspecialchars($player['image']); ?>" 
                         alt="<?php echo htmlspecialchars($player['name']); ?>"
                         class="player-image">
                    <div class="player-info">
                        <h3><?php echo htmlspecialchars($player['name']); ?></h3>
                        <p class="rating">⭐ <?php echo $player['rating']; ?></p>
                    </div>
                    <div class="player-actions">
                        <button class="btn primary" onclick="editPlayer(<?php 
                            echo htmlspecialchars(json_encode([
                                'id' => $player['id'],
                                'name' => $player['name'],
                                'rating' => $player['rating'],
                                'image' => $player['image']
                            ]));
                        ?>)">✏️ Editar</button>
                        <button class="btn danger" onclick="deletePlayer(<?php echo $player['id']; ?>)">🗑️ Excluir</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Modal para adicionar/editar jogador -->
            <div id="playerModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2 id="modalTitle">Adicionar Novo Card</h2>
                    <form id="playerForm" action="admin_process.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="player_id" id="playerId" value="">
                        
                        <div class="form-group">
                            <label for="nameInput">Nome:</label>
                            <input type="text" id="nameInput" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="ratingInput">Avaliação (0-100):</label>
                            <input type="number" id="ratingInput" name="rating" min="0" max="100" value="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="imageInput">Imagem:</label>
                            <div class="image-preview-wrapper">
                                <input type="file" id="imageInput" name="image" accept="image/*">
                                <div class="preview">
                                    <img id="imagePreview" src="" alt="Preview">
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn success">💾 Salvar</button>
                            <button type="button" class="btn danger" onclick="closeModal()">❌ Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Seção de configuração do verso do card -->
            <div class="back-icon-section">
                <h2>🎴 Verso do Card</h2>
                <form action="admin_process.php" method="POST" enctype="multipart/form-data" class="back-icon-form">
                    <input type="hidden" name="action" value="update_card_back">
                    <div class="form-group">
                        <label for="backIconInput">Imagem do verso:</label>
                        <div class="image-preview-container">
                            <input type="file" id="backIconInput" name="card_back" accept="image/*" required>
                            <div class="preview">
                                <?php if (!empty($cardBack) && file_exists($cardBack)): ?>
                                    <img id="backIconPreview" src="<?php echo htmlspecialchars($cardBack); ?>" alt="Verso atual">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn primary">💾 Atualizar Verso</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Funções do Modal
        const modal = document.getElementById('playerModal');
        const closeBtn = document.querySelector('.close');
        const addPlayerBtn = document.getElementById('addPlayerBtn');
        const clearDataBtn = document.getElementById('clearDataBtn');
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('imagePreview');
        const backIconInput = document.getElementById('backIconInput');
        const backIconPreview = document.getElementById('backIconPreview');
        const themeToggle = document.getElementById('themeToggle');

        function openModal() {
            modal.style.display = 'block';
        }

        function closeModal() {
            modal.style.display = 'none';
            resetForm();
        }

        function resetForm() {
            document.getElementById('playerForm').reset();
            document.getElementById('formAction').value = 'add';
            document.getElementById('playerId').value = '';
            document.getElementById('modalTitle').textContent = 'Adicionar Novo Card';
            imagePreview.src = '';
        }

        function editPlayer(player) {
            document.getElementById('modalTitle').textContent = 'Editar Card';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('playerId').value = player.id;
            document.getElementById('nameInput').value = player.name;
            document.getElementById('ratingInput').value = player.rating;
            imagePreview.src = player.image;
            openModal();
        }

        function deletePlayer(playerId) {
            if (confirm('⚠️ Tem certeza que deseja excluir este card?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin_process.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';

                const playerIdInput = document.createElement('input');
                playerIdInput.type = 'hidden';
                playerIdInput.name = 'player_id';
                playerIdInput.value = playerId;

                form.appendChild(actionInput);
                form.appendChild(playerIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Preview de imagem
        function handleImagePreview(input, preview) {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        // Alternar tema
        themeToggle.addEventListener('change', function() {
            fetch('admin_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=toggle_theme'
            })
            .then(response => response.text())
            .then(() => {
                document.documentElement.setAttribute('data-theme', this.checked ? 'dark' : 'light');
                document.querySelector('.theme-label').textContent = this.checked ? '☀️' : '🌙';
            })
            .catch(error => console.error('Erro:', error));
        });

        // Event Listeners
        addPlayerBtn.addEventListener('click', () => {
            resetForm();
            openModal();
        });

        closeBtn.addEventListener('click', closeModal);

        clearDataBtn.addEventListener('click', function() {
            if (confirm('⚠️ Tem certeza que deseja limpar todos os dados do jogo? Esta ação não pode ser desfeita.')) {
                window.location.href = 'admin_process.php?action=clear_data';
            }
        });

        handleImagePreview(imageInput, imagePreview);
        handleImagePreview(backIconInput, backIconPreview);

        // Fechar modal ao clicar fora
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Auto-hide mensagem
        const messageBox = document.getElementById('messageBox');
        if (messageBox) {
            setTimeout(() => {
                messageBox.style.opacity = '0';
                setTimeout(() => messageBox.remove(), 500);
            }, 3000);
        }
    </script>
</body>
</html>
