<?php
session_start();

// Inclui o arquivo de configuração do banco de dados
require_once __DIR__ . '/../../config/database.php';

// Verificar se o usuário está logado
$is_logged_in = isset($_SESSION['user_id']);
$current_user_is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
$user_name = $_SESSION['user_name'] ?? 'Usuário';

// Se não estiver logado, redirecionar para o login
if (!$is_logged_in) {
    header('Location: /login.php');
    exit();
}

// Buscar o código de embed da live stream da página de admin
$live_embed_code = '';
try {
    $stmt = $pdo->query("SELECT embed_code FROM live_stream_settings WHERE id = 1 LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $live_embed_code = $result['embed_code'] ?? '';
} catch (Exception $e) {
    // Código de embed padrão para fallback
    $live_embed_code = '<iframe width="100%" height="400" src="https://www.youtube.com/embed/live_stream?channel=UC_x5XG1OV2P6uZZ5FSM9Ttw" frameborder="0" allowfullscreen></iframe>';
}

// Determinar se a live está ativa baseado na presença do código embed
$is_live_active = !empty($live_embed_code);

// Buscar próximas palestras para a Agenda T101
$upcomingLectures = [];
try {
    $stmt = $pdo->query("
        SELECT id, title, speaker, description, image_path, announcement_date, lecture_time
        FROM upcoming_announcements
        WHERE is_active = 1
        AND announcement_date >= CURDATE()
        ORDER BY announcement_date ASC, display_order ASC
        LIMIT 5
    ");
    $upcomingLectures = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fallback de dados em caso de erro
    $upcomingLectures = [];
}

// Configurações da página
$page_title = 'Live Stream - Translators101';
$page_description = 'Acompanhe as transmissões ao vivo do Translators101 com palestras exclusivas dos melhores profissionais do mercado.';

include __DIR__ . '/../vision/includes/head.php';
?>

<?php include __DIR__ . '/../vision/includes/header.php'; ?>

<?php include __DIR__ . '/../vision/includes/sidebar.php'; ?>

<div class="main-content">
    <!-- Hero Section -->
    <div class="glass-hero">
        <div class="hero-content">
            <h1><i class="fas fa-video"></i> Live Stream T101</h1>
            <div class="live-status <?php echo $is_live_active ? 'live' : 'offline'; ?>">
                <i class="fas fa-circle"></i>
                <span><?php echo $is_live_active ? 'AO VIVO' : 'OFFLINE'; ?></span>
            </div>
        </div>
    </div>

    <!-- Container Principal da Live -->
    <div class="live-container">
        <!-- Seção do Player -->
        <div class="player-section">
            <?php if ($is_live_active): ?>
            <div class="live-player">
                <div class="player-container">
                    <?php echo $live_embed_code; ?>
                </div>
                <div class="player-controls">
                    <button class="control-btn" onclick="toggleFullscreen()" title="Tela Cheia">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button class="control-btn" onclick="togglePiP()" title="Picture in Picture">
                        <i class="fas fa-external-link-alt"></i>
                    </button>
                </div>
            </div>
            <?php else: ?>
            <div class="offline-player">
                <div class="offline-content">
                    <i class="fas fa-video-slash"></i>
                    <h3>Transmissão Offline</h3>
                    <p>No momento não temos nenhuma transmissão ao vivo.</p>
                    <p>Confira nossa agenda abaixo para saber quando será a próxima live!</p>
                    
                    <div class="social-links">
                        <a href="#" class="social-btn">
                            <i class="fab fa-youtube"></i> YouTube
                        </a>
                        <a href="#" class="social-btn">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>
                        <a href="#" class="social-btn">
                            <i class="fab fa-linkedin"></i> LinkedIn
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Seção do Chat -->
        <div class="chat-section">
            <div class="chat-header">
                <h3><i class="fas fa-comments"></i> Chat ao Vivo</h3>
                <div class="chat-controls">
                    <button class="control-btn" onclick="toggleChat()" title="Minimizar Chat">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button class="control-btn" onclick="clearChat()" title="Limpar Chat">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <div class="system-message">
                    <i class="fas fa-info-circle"></i>
                    <span>Bem-vindo ao chat da Translators101! Seja respeitoso e mantenha as discussões relacionadas ao conteúdo.</span>
                </div>
                
                <div class="chat-message">
                    <div class="message-author">Sistema</div>
                    <div class="message-content">Chat inicializado. Aguardando mensagens...</div>
                    <div class="message-time"><?php echo date('H:i'); ?></div>
                </div>
            </div>
            
            <?php if ($is_live_active): ?>
            <div class="chat-input-container">
                <input type="text" id="chatInput" placeholder="Digite sua mensagem..." maxlength="500">
                <button id="sendMessage" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Preview para Administradores -->
    <?php if ($current_user_is_admin): ?>
    <div class="video-card overlay-preview">
        <h2><i class="fas fa-eye"></i> Preview de Overlay</h2>
        <div class="overlay-content">
            <div class="overlay-message">
                <p>Esta é uma prévia de como as mensagens aparecem na transmissão</p>
                <button class="btn btn-primary" onclick="sendOverlay()">
                    <i class="fas fa-broadcast-tower"></i> Enviar para Overlay
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Agenda T101 -->
    <div class="video-card agenda-t101-card">
        <h2><i class="fas fa-calendar-check"></i> Agenda T101</h2>
        
        <div class="agenda-instruction">
            Baixe o arquivo de convite e clique nele para incluir o evento em sua agenda.
        </div>
        
        <?php if (count($upcomingLectures) > 0): ?>
        <div class="agenda-grid">
            <?php foreach ($upcomingLectures as $lecture): 
                // Processar data e horário
                $announcementDate = $lecture['announcement_date'] ?? date('Y-m-d');
                $lectureTime = $lecture['lecture_time'] ?? '19:00:00';
                
                $dateTime = new DateTime($announcementDate . ' ' . $lectureTime);
                $formattedDay = $dateTime->format('d');
                $formattedMonth = $dateTime->format('M');
                $formattedTime = $dateTime->format('H:i');
                
                // Traduzir meses
                $monthNames = [
                    'Jan' => 'JAN', 'Feb' => 'FEV', 'Mar' => 'MAR', 'Apr' => 'ABR',
                    'May' => 'MAI', 'Jun' => 'JUN', 'Jul' => 'JUL', 'Aug' => 'AGO',
                    'Sep' => 'SET', 'Oct' => 'OUT', 'Nov' => 'NOV', 'Dec' => 'DEZ'
                ];
                $formattedMonth = $monthNames[$formattedMonth] ?? $formattedMonth;
            ?>
            <div class="agenda-item">
                <div class="agenda-date">
                    <div class="day"><?php echo $formattedDay; ?></div>
                    <div class="month"><?php echo $formattedMonth; ?></div>
                </div>
                <div class="agenda-info">
                    <h4><?php echo htmlspecialchars($lecture['title']); ?></h4>
                    <p><i class="fas fa-clock"></i> <?php echo $formattedTime; ?>h</p>
                    <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($lecture['speaker']); ?></p>
                    <?php if (!empty($lecture['description'])): ?>
                    <p class="agenda-description"><?php echo htmlspecialchars(substr($lecture['description'], 0, 100) . '...'); ?></p>
                    <?php endif; ?>
                </div>
                <div class="agenda-actions">
                    <button class="btn btn-outline" onclick="downloadICS('<?php echo htmlspecialchars($lecture['id']); ?>')">
                        <i class="fas fa-calendar-plus"></i> Adicionar à Agenda
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="no-events">
            <i class="fas fa-calendar-times"></i>
            <p>Nenhum evento agendado no momento.</p>
            <p>Fique de olho nas nossas redes sociais para novidades!</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Próximas Transmissões (seção original) -->
    <div class="video-card schedule-card">
        <h2><i class="fas fa-calendar-alt"></i> Próximas Transmissões</h2>
        <div class="schedule-grid">
            <div class="schedule-item">
                <div class="schedule-date">
                    <div class="day">15</div>
                    <div class="month">DEZ</div>
                </div>
                <div class="schedule-info">
                    <h4>Tradução Técnica: Desafios e Soluções</h4>
                    <p><i class="fas fa-clock"></i> 19:00 - 21:00</p>
                    <p><i class="fas fa-user"></i> Prof. Maria Silva</p>
                </div>
                <div class="schedule-actions">
                    <button class="btn btn-outline">
                        <i class="fas fa-bell"></i> Lembrete
                    </button>
                </div>
            </div>

            <div class="schedule-item">
                <div class="schedule-date">
                    <div class="day">22</div>
                    <div class="month">DEZ</div>
                </div>
                <div class="schedule-info">
                    <h4>Workshop: Ferramentas de CAT</h4>
                    <p><i class="fas fa-clock"></i> 14:00 - 17:00</p>
                    <p><i class="fas fa-user"></i> Dr. João Santos</p>
                </div>
                <div class="schedule-actions">
                    <button class="btn btn-outline">
                        <i class="fas fa-bell"></i> Lembrete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../vision/includes/footer.php'; ?>

<style>
/* Estilos para a página de Live Stream */
:root {
    --brand-purple: #8e44ad;
    --brand-purple-dark: #5e3370;
    --accent-gold: #f39c12;
    --accent-green: #27ae60;
    --accent-red: #e74c3c;
    --text-primary: #ffffff;
    --text-secondary: #f0f0f0;
    --text-muted: #d4d4d4;
    --glass-bg: rgba(255, 255, 255, 0.05);
    --glass-border: rgba(255, 255, 255, 0.15);
}

body {
    background: linear-gradient(135deg, #1e1e1e 0%, #2d1b3d 100%);
    color: var(--text-primary);
    min-height: 100vh;
}

.main-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

/* Hero Section */
.glass-hero {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 30px;
    text-align: center;
}

.hero-content h1 {
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: var(--text-primary);
}

.live-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.live-status.live {
    background: linear-gradient(135deg, var(--accent-red), #c0392b);
    animation: pulse 2s infinite;
}

.live-status.offline {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-muted);
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Container Principal da Live */
.live-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

/* Player Section */
.player-section {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    overflow: hidden;
}

.live-player {
    position: relative;
}

.player-container {
    width: 100%;
    aspect-ratio: 16/9;
    background: #000;
}

.player-container iframe {
    width: 100%;
    height: 100%;
    border: none;
}

.player-controls {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 15px;
    background: rgba(0, 0, 0, 0.7);
}

.offline-player {
    aspect-ratio: 16/9;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(142, 68, 173, 0.1), rgba(0, 0, 0, 0.3));
}

.offline-content {
    text-align: center;
    padding: 40px;
}

.offline-content i {
    font-size: 4rem;
    color: var(--text-muted);
    margin-bottom: 20px;
}

.offline-content h3 {
    color: var(--text-primary);
    margin-bottom: 15px;
}

.offline-content p {
    color: var(--text-secondary);
    margin-bottom: 10px;
}

.social-links {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 25px;
}

.social-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.social-btn:hover {
    background: var(--brand-purple);
    transform: translateY(-2px);
}

/* Chat Section */
.chat-section {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    height: fit-content;
    max-height: 600px;
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid var(--glass-border);
}

.chat-header h3 {
    color: var(--text-primary);
    margin: 0;
}

.chat-controls {
    display: flex;
    gap: 10px;
}

.control-btn {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--glass-border);
    color: var(--text-muted);
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.control-btn:hover {
    background: rgba(255, 255, 255, 0.15);
    color: var(--text-primary);
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    max-height: 400px;
}

.system-message {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 15px;
    background: rgba(39, 174, 96, 0.1);
    border-left: 3px solid var(--accent-green);
    border-radius: 8px;
    margin-bottom: 15px;
}

.system-message i {
    color: var(--accent-green);
    margin-top: 2px;
}

.chat-message {
    margin-bottom: 15px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
}

.message-author {
    font-weight: 600;
    color: var(--accent-gold);
    margin-bottom: 5px;
}

.message-content {
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.message-time {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.chat-input-container {
    display: flex;
    padding: 20px;
    border-top: 1px solid var(--glass-border);
    gap: 10px;
}

#chatInput {
    flex: 1;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--glass-border);
    border-radius: 10px;
    padding: 12px 15px;
    color: var(--text-primary);
    font-size: 1rem;
}

#chatInput:focus {
    outline: none;
    border-color: var(--brand-purple);
    background: rgba(255, 255, 255, 0.15);
}

#sendMessage {
    background: linear-gradient(135deg, var(--brand-purple), var(--brand-purple-dark));
    border: none;
    color: white;
    padding: 12px 15px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

#sendMessage:hover {
    background: linear-gradient(135deg, var(--brand-purple-dark), var(--brand-purple));
    transform: translateY(-1px);
}

/* Video Cards */
.video-card {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
}

.video-card h2 {
    color: var(--text-primary);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Agenda T101 Específico */
.agenda-t101-card h2 {
    color: var(--accent-gold);
}

.agenda-instruction {
    background: linear-gradient(135deg, rgba(39, 174, 96, 0.1), rgba(142, 68, 173, 0.1));
    border-left: 4px solid var(--accent-green);
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    color: var(--text-secondary);
    font-weight: 500;
}

.agenda-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.agenda-item {
    display: flex;
    align-items: center;
    gap: 25px;
    padding: 25px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--glass-border);
    border-radius: 15px;
    transition: all 0.3s ease;
}

.agenda-item:hover {
    background: rgba(255, 255, 255, 0.08);
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(142, 68, 173, 0.2);
}

.agenda-date {
    text-align: center;
    min-width: 70px;
    background: linear-gradient(135deg, var(--brand-purple), var(--brand-purple-dark));
    border-radius: 12px;
    padding: 15px 10px;
}

.agenda-date .day {
    font-size: 2.2rem;
    font-weight: bold;
    color: white;
    line-height: 1;
    margin-bottom: 5px;
}

.agenda-date .month {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8);
    text-transform: uppercase;
    font-weight: 600;
}

.agenda-info {
    flex: 1;
}

.agenda-info h4 {
    color: var(--text-primary);
    margin-bottom: 10px;
    font-size: 1.2rem;
    font-weight: 600;
}

.agenda-info p {
    color: var(--text-secondary);
    margin: 6px 0;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.agenda-info p i {
    color: var(--accent-gold);
    width: 16px;
}

.agenda-description {
    color: var(--text-muted) !important;
    font-style: italic;
    margin-top: 8px !important;
}

.agenda-actions {
    min-width: 180px;
}

.no-events {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
}

.no-events i {
    font-size: 3rem;
    margin-bottom: 20px;
    color: var(--text-muted);
}

.no-events p {
    margin-bottom: 10px;
}

/* Schedule Section Original */
.schedule-grid {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.schedule-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--glass-border);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.schedule-item:hover {
    background: rgba(255, 255, 255, 0.08);
    transform: translateY(-2px);
}

.schedule-date {
    text-align: center;
    min-width: 60px;
}

.schedule-date .day {
    font-size: 2rem;
    font-weight: bold;
    color: var(--brand-purple);
    line-height: 1;
}

.schedule-date .month {
    font-size: 0.8rem;
    color: var(--text-muted);
    text-transform: uppercase;
}

.schedule-info {
    flex: 1;
}

.schedule-info h4 {
    color: var(--text-primary);
    margin-bottom: 8px;
    font-size: 1.1rem;
}

.schedule-info p {
    color: var(--text-secondary);
    margin: 4px 0;
    font-size: 0.9rem;
}

.schedule-actions {
    min-width: 120px;
}

/* Botões */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 18px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    font-size: 0.9rem;
}

.btn-outline {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--glass-border);
    color: var(--text-primary);
}

.btn-outline:hover {
    background: var(--brand-purple);
    border-color: var(--brand-purple);
    transform: translateY(-1px);
}

.btn-primary {
    background: linear-gradient(135deg, var(--brand-purple), var(--brand-purple-dark));
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, var(--brand-purple-dark), var(--brand-purple));
    transform: translateY(-1px);
}

/* Overlay Preview */
.overlay-preview {
    border: 2px dashed var(--accent-gold);
    background: rgba(243, 156, 18, 0.1);
}

.overlay-content {
    text-align: center;
    padding: 20px;
}

.overlay-message {
    background: rgba(0, 0, 0, 0.8);
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

/* Responsive */
@media (max-width: 1024px) {
    .live-container {
        grid-template-columns: 1fr;
    }
    
    .agenda-item {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .agenda-actions {
        width: 100%;
    }
    
    .agenda-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .main-content {
        padding: 15px;
    }
    
    .glass-hero {
        padding: 30px 20px;
    }
    
    .hero-content h1 {
        font-size: 2rem;
    }
    
    .agenda-item,
    .schedule-item {
        padding: 20px 15px;
    }
    
    .chat-section {
        max-height: 400px;
    }
}
</style>

<script>
// Variáveis globais para o chat
let chatMessages = [];
let userName = '<?php echo addslashes($user_name); ?>';

// Funções do Player
function toggleFullscreen() {
    const playerContainer = document.querySelector('.player-container');
    if (playerContainer) {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            playerContainer.requestFullscreen();
        }
    }
}

function togglePiP() {
    const video = document.querySelector('.player-container video');
    if (video && 'pictureInPictureEnabled' in document) {
        if (document.pictureInPictureElement) {
            document.exitPictureInPicture();
        } else {
            video.requestPictureInPicture();
        }
    }
}

// Funções do Chat
function toggleChat() {
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.querySelector('.chat-input-container');
    
    if (chatMessages.style.display === 'none') {
        chatMessages.style.display = 'block';
        if (chatInput) chatInput.style.display = 'flex';
    } else {
        chatMessages.style.display = 'none';
        if (chatInput) chatInput.style.display = 'none';
    }
}

function clearChat() {
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.innerHTML = `
        <div class="system-message">
            <i class="fas fa-info-circle"></i>
            <span>Chat limpo pelo usuário.</span>
        </div>
    `;
}

function sendMessage() {
    const chatInput = document.getElementById('chatInput');
    const message = chatInput.value.trim();
    
    if (message) {
        addMessageToChat(userName, message);
        chatInput.value = '';
    }
}

function addMessageToChat(author, content) {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message';
    
    const now = new Date();
    const timeString = now.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
    
    messageDiv.innerHTML = `
        <div class="message-author">${author}</div>
        <div class="message-content">${content}</div>
        <div class="message-time">${timeString}</div>
    `;
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Event listener para Enter no chat
document.addEventListener('DOMContentLoaded', function() {
    const chatInput = document.getElementById('chatInput');
    if (chatInput) {
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }
});

// Função para gerar e baixar arquivo ICS
function downloadICS(lectureId) {
    // Buscar dados da palestra via AJAX
    fetch(`get_lecture_data.php?id=${lectureId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                generateICSFile(data.lecture);
            } else {
                alert('Erro ao buscar dados da palestra: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao gerar arquivo ICS');
        });
}

function generateICSFile(lecture) {
    const startDate = new Date(lecture.announcement_date + 'T' + lecture.lecture_time);
    const endDate = new Date(startDate.getTime() + 90 * 60000); // 90 minutos depois
    const alarmDate = new Date(startDate.getTime() - 30 * 60000); // 30 minutos antes
    
    // Formatação de data para ICS (YYYYMMDDTHHMMSSZ)
    function formatDateForICS(date) {
        return date.getUTCFullYear().toString() +
               (date.getUTCMonth() + 1).toString().padStart(2, '0') +
               date.getUTCDate().toString().padStart(2, '0') + 'T' +
               date.getUTCHours().toString().padStart(2, '0') +
               date.getUTCMinutes().toString().padStart(2, '0') +
               date.getUTCSeconds().toString().padStart(2, '0') + 'Z';
    }
    
    const icsContent = `BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Translators101//Live Stream//PT
CALSCALE:GREGORIAN
METHOD:PUBLISH
BEGIN:VEVENT
UID:t101-${lecture.id}@translators101.com
DTSTART:${formatDateForICS(startDate)}
DTEND:${formatDateForICS(endDate)}
SUMMARY:${lecture.title}
DESCRIPTION:Palestra com ${lecture.speaker}\\n\\n${lecture.description}\\n\\nAcesse: https://v.translators101.com/live
LOCATION:Live Stream - Translators101
ORGANIZER;CN=Translators101:mailto:contato@translators101.com
STATUS:CONFIRMED
TRANSP:OPAQUE
BEGIN:VALARM
TRIGGER:-PT30M
ACTION:DISPLAY
DESCRIPTION:Lembrete: ${lecture.title} em 30 minutos
END:VALARM
END:VEVENT
END:VCALENDAR`;
    
    // Criar e baixar o arquivo
    const blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `translators101-${lecture.title.toLowerCase().replace(/[^a-z0-9]/g, '-')}.ics`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
}

// Função para overlay (admin)
function sendOverlay() {
    alert('Funcionalidade de overlay será implementada em versão futura.');
}

// Auto-refresh do chat (simulação)
setInterval(function() {
    // Aqui você pode implementar a lógica para buscar novas mensagens via AJAX
    // Por enquanto, vamos apenas simular atividade
}, 5000);
</script>

<!-- Script para geração de ICS - arquivo separado -->
<script>
// Dados das palestras em PHP convertidos para JavaScript
const lecturesData = <?php echo json_encode($upcomingLectures); ?>;

// Função alternativa para gerar ICS sem AJAX (usando dados já carregados)
function downloadICS(lectureId) {
    const lecture = lecturesData.find(l => l.id === lectureId);
    
    if (!lecture) {
        alert('Palestra não encontrada');
        return;
    }
    
    const startDateTime = new Date(lecture.announcement_date + 'T' + lecture.lecture_time);
    const endDateTime = new Date(startDateTime.getTime() + 90 * 60000); // 90 minutos
    
    // Função para formatar data no padrão ICS
    function toICSDate(date) {
        return date.getUTCFullYear() +
               String(date.getUTCMonth() + 1).padStart(2, '0') +
               String(date.getUTCDate()).padStart(2, '0') + 'T' +
               String(date.getUTCHours()).padStart(2, '0') +
               String(date.getUTCMinutes()).padStart(2, '0') +
               String(date.getUTCSeconds()).padStart(2, '0') + 'Z';
    }
    
    const icsContent = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Translators101//Agenda T101//PT',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'BEGIN:VEVENT',
        `UID:t101-${lecture.id}@translators101.com`,
        `DTSTART:${toICSDate(startDateTime)}`,
        `DTEND:${toICSDate(endDateTime)}`,
        `SUMMARY:${lecture.title}`,
        `DESCRIPTION:Palestra com ${lecture.speaker}\\n\\n${lecture.description.replace(/\n/g, '\\n')}\\n\\nAcesse: https://v.translators101.com/live`,
        'LOCATION:Live Stream - Translators101',
        'ORGANIZER;CN=Translators101:mailto:contato@translators101.com',
        'STATUS:CONFIRMED',
        'TRANSP:OPAQUE',
        'BEGIN:VALARM',
        'TRIGGER:-PT30M',
        'ACTION:DISPLAY',
        `DESCRIPTION:Lembrete: ${lecture.title} em 30 minutos`,
        'END:VALARM',
        'END:VEVENT',
        'END:VCALENDAR'
    ].join('\r\n');
    
    // Download do arquivo
    const blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `t101-${lecture.title.toLowerCase().replace(/[^\w\s-]/g, '').replace(/\s+/g, '-')}.ics`;
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    // Feedback para o usuário
    const button = event.target.closest('.btn');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Baixado!';
    button.style.background = 'var(--accent-green)';
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.style.background = '';
    }, 2000);
}
</script>