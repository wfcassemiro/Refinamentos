<?php
session_start(); 
require_once __DIR__ . '/../../config/database.php';

// --- Funções de Acesso (Devem ser garantidas no ambiente de produção) ---
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
if (!function_exists('hasVideotecaAccess')) {
    function hasVideotecaAccess() {
        return (isset($_SESSION['role']) && ($_SESSION['role'] === 'subscriber' || $_SESSION['role'] === 'admin'));
    }
}
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
    }
}

// Redirecionamento de acesso
if (!isLoggedIn() || !hasVideotecaAccess()) {
    header("Location: /planos.php");
    exit;
}

// --- Configurações da Página ---
$page_title = 'Live Stream - Translators101';
$page_description = 'Assista às palestras ao vivo da Translators101';

// 1. BUSCAR EMBED CODE DO BANCO DE DADOS
$live_embed_code = '';
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'live_embed_code'");
    $stmt->execute();
    $result = $stmt->fetch();
    if ($result) {
        $live_embed_code = $result['setting_value'];
    }
} catch (PDOException $e) {
    $live_embed_code = ''; 
}

// Live está ativa se temos embed code
$is_live_active = !empty(trim($live_embed_code));
$current_user_is_admin = isAdmin();


// 2. BUSCAR PRÓXIMAS TRANSMISSÕES (incluindo lecture_time)
$upcomingLectures = [];
try {
    $stmt = $pdo->query("
        SELECT id, title, speaker, description, image_path, announcement_date, lecture_time
        FROM upcoming_announcements 
        WHERE is_active = 1 
        AND announcement_date >= CURDATE()
        ORDER BY announcement_date ASC
        LIMIT 3
    ");
    $upcomingLectures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $upcomingLectures = [];
}


include __DIR__ . '/../vision/includes/head.php';
include __DIR__ . '/../vision/includes/header.php';
include __DIR__ . '/../vision/includes/sidebar.php';
?>

<div class="main-content">
    <div class="glass-hero">
    <div class="hero-content">
    <h1><i class="fas fa-broadcast-tower"></i> Live Stream Translators101</h1>
    <p>Participe e interaja</p>
    <?php if ($is_live_active): ?>
    <div class="live-status live-active">
    <i class="fas fa-circle pulse"></i> AO VIVO
    </div>
    <?php else: ?>
    <div class="live-status live-offline">
    <i class="fas fa-circle"></i> OFFLINE
    </div>
    <?php endif; ?>
    </div>
    </div>

    <div class="live-container">
    <div class="player-section">
    <div class="video-card player-card">
    <?php if ($is_live_active): ?>
    <div class="live-player">
    <div class="player-container">
    <?php echo $live_embed_code; ?>
    </div>
    <div class="player-controls">
    <button class="control-btn" onclick="toggleFullscreen()">
    <i class="fas fa-expand"></i> Tela Cheia
    </button>
    <button class="control-btn" onclick="togglePictureInPicture()">
    <i class="fas fa-external-link-alt"></i> PiP
    </button>
    </div>
    </div>
    <?php else: ?>
    <div class="offline-player">
    <div class="offline-content">
    <i class="fas fa-video-slash"></i>
    <h3>Transmissão Offline</h3>
    <p>No momento não há transmissões ao vivo.</p>
    <p>Fique atento às nossas redes sociais para saber quando a próxima live começará!</p>
    <div class="social-links">
    <a href="#" class="social-link"><i class="fab fa-instagram"></i> Instagram</a>
    <a href="#" class="social-link"><i class="fab fa-youtube"></i> YouTube</a>
    <a href="#" class="social-link"><i class="fab fa-linkedin"></i> LinkedIn</a>
    </div>
    </div>
    </div>
    <?php endif; ?>
    </div>
    </div>

    <div class="chat-section">
    <div class="video-card chat-card">
    <div class="chat-header">
    <h3><i class="fas fa-comments"></i> Chat da Live</h3>
    <div class="chat-controls">
    <button class="control-btn small" onclick="toggleChat()" title="Minimizar Chat">
    <i class="fas fa-minus"></i>
    </button>
    <?php if ($current_user_is_admin): ?>
    <button class="control-btn small" onclick="clearChat()" title="Limpar Chat">
    <i class="fas fa-broom"></i>
    </button>
    <?php endif; ?>
    </div>
    </div>
    
    <div class="chat-messages" id="chatMessages">
    <div class="system-message">
    <i class="fas fa-info-circle"></i>
    Bem-vindo ao chat da live! Seja respeitoso com outros participantes.
    </div>
    <div id="chatMessagesContainer"></div>
    </div>
    
    <?php if ($is_live_active): ?>
    <div class="chat-input-container">
    <form id="chatForm" method="post" action="chat-save.php">
    <div class="chat-input-group">
    <input type="text" id="chatInput" name="message" placeholder="Digite sua mensagem..." maxlength="500" autocomplete="off" required>
    <button type="submit" class="send-btn">
    <i class="fas fa-paper-plane"></i>
    </button>
    </div>
    </form>
    </div>
    <?php else: ?>
    <div class="chat-input-container">
    <div class="chat-offline-message">
    <i class="fas fa-info-circle"></i>
    O chat estará disponível quando a transmissão estiver ao vivo.
    </div>
    </div>
    <?php endif; ?>
    </div>
    </div>
    </div>

    <?php if (isAdmin()): ?>
    <div class="video-card overlay-preview">
    <div class="card-header">
    <h2><i class="fas fa-tv"></i> Pré-visualização do Overlay</h2>
    <button type="button" class="cta-btn" onclick="clearOverlay()" style="padding: 8px 16px; font-size: 0.9rem;">
    <i class="fas fa-trash"></i> Remover da Tela
    </button>
    </div>
    <div id="overlayMessage" class="overlay-content">
    <div class="empty-state">
    <i class="fas fa-tv"></i>
    <p>Nenhuma mensagem selecionada para o overlay</p>
    </div>
    </div>
    </div>
    <?php endif; ?>

    <div class="video-card schedule-card">
        <h2><i class="fas fa-calendar-alt"></i> Próximas Transmissões</h2>
        <p class="schedule-instruction instruction-highlight">Baixe o arquivo de convite e clique nele para incluir um lembrete em sua agenda.</p>
        
        <div class="lectures-grid" id="lecturesContainer">
            <?php if (!empty($upcomingLectures)): ?>
                <?php 
                date_default_timezone_set('America/Sao_Paulo');

                foreach ($upcomingLectures as $lecture): 
                    $announcementDate = $lecture['announcement_date'] ?? '';
                    $lectureTime = $lecture['lecture_time'] ?? '';
                    
                    $startHour = '19:00:00'; // Default Dia de Semana
                    $duration = 120; // 2 horas (18h-20h)
                    
                    if (!empty($lectureTime) && strpos($lectureTime, '-') !== false) {
                        // Se o formato estiver como "HH:mm - HH:mm"
                        list($start_time, $end_time) = explode(' - ', $lectureTime);
                        $startHour = trim($start_time) . ':00';
                        
                        // Calculando a duração em minutos (opcional, mas bom para precisão)
                        try {
                            $dt_start = new DateTime($announcementDate . ' ' . $start_time);
                            $dt_end = new DateTime($announcementDate . ' ' . $end_time);
                            $interval = $dt_start->diff($dt_end);
                            $duration = $interval->h * 60 + $interval->i;
                            if ($duration <= 0) $duration = 120; // Garante mínimo de 2h se houver erro
                        } catch (Exception $e) {
                            $duration = 120;
                        }
                    } else if (!empty($lectureTime)) {
                        // Se o formato for apenas HH:mm
                         $startHour = trim($lectureTime) . ':00';
                         $duration = 120;
                    } else {
                        // Lógica de fallback se lectureTime estiver vazio
                        try {
                            $dayOfWeek = (new DateTime($announcementDate))->format('N'); // 1 (Seg) - 7 (Dom)
                            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) { // Segunda a Sexta
                                $startHour = '18:00:00';
                                $duration = 120; // 18:00h - 20:00h
                            } elseif ($dayOfWeek == 6) { // Sábado
                                $startHour = '10:00:00';
                                $duration = 120; // 10:00h - 12:00h
                            } else { // Domingo ou inválido
                                $startHour = '19:00:00';
                                $duration = 120;
                            }
                        } catch (Exception $e) {
                            $startHour = '19:00:00';
                            $duration = 120;
                        }
                    }

                    // Reconstruindo as datas/horas com o horário definido
                    $dateTimeStart = new DateTime($announcementDate . ' ' . $startHour);
                    $dateTimeEnd = clone $dateTimeStart;
                    $dateTimeEnd->modify("+{$duration} minutes");

                    $formattedDate = $dateTimeStart->format('d \d\e F, Y');
                    $monthNames = [
                        'January' => 'Janeiro', 'Maret' => 'Março', 'February' => 'Fevereiro', 'March' => 'Março', 
                        'April' => 'Abril', 'May' => 'Maio', 'June' => 'Junho', 
                        'July' => 'Julho', 'August' => 'Agosto', 'September' => 'Setembro', 
                        'October' => 'Outubro', 'November' => 'Novembro', 'December' => 'Dezembro'
                    ];
                    $formattedDate = str_replace(array_keys($monthNames), array_values($monthNames), $formattedDate);

                    // Gerar dados para o link ICS (JSON)
                    $icsData = [
                        'title' => $lecture['title'] ?? 'Palestra T101',
                        'description' => $lecture['description'] ?? 'Sem descrição.',
                        'start' => $dateTimeStart->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z'), 
                        'end' => $dateTimeEnd->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z'), 
                        'reminder' => 30, // 30 minutos antes
                    ];
                ?>
                <div class="lecture-card">
                    <div class="lecture-image-container">
                        <img src="<?php echo htmlspecialchars($lecture['image_path'] ?? '/images/palestra-placeholder.jpg'); ?>" alt="Palestra" class="lecture-image">
                    </div>
                    <div class="lecture-info">
                        <div class="lecture-date"><?php echo htmlspecialchars($formattedDate); ?></div>
                        <h4 class="lecture-title"><?php echo htmlspecialchars($lecture['title'] ?? 'Título a Definir'); ?></h4>
                        <div class="lecture-speaker">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($lecture['speaker'] ?? 'Palestrante'); ?></span>
                        </div>
                        <p class="lecture-summary">
                            <?php echo htmlspecialchars($lecture['description'] ?? 'Breve descrição...'); ?>
                        </p>
                    </div>
                    <div class="schedule-actions-bottom">
                        <a href="#" 
                            class="cta-btn btn-reminder" 
                            data-ics='<?php echo htmlspecialchars(json_encode($icsData), ENT_QUOTES, 'UTF-8'); ?>'
                            onclick="generateIcs(event)">
                            <i class="fas fa-calendar-plus"></i> Adicionar à agenda
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #ccc; text-align: center; padding: 20px; grid-column: 1 / -1;">Nenhuma transmissão futura agendada no momento. Fique de olho!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
:root {
    --brand-purple: #8e44ad;
    --brand-purple-dark: #5e3370;
    --brand-purple-light: #a569bd;
    --accent-gold: #f39c12;
    --accent-green: #27ae60;
    --accent-red: #e74c3c;
    --text-primary: #ffffff;
    --text-secondary: #f0f0f0;
    --text-muted: #d4d4d4;
    --glass-bg: rgba(255, 255, 255, 0.05);
    --glass-border: rgba(255, 255, 255, 0.15);
}
.cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    font-size: 1.1rem;
    font-weight: bold;
    border-radius: 30px;
    background: var(--brand-purple);
    color: #fff;
    text-decoration: none;
    box-shadow: 0 6px 18px rgba(142, 68, 173, 0.6);
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
    border: none;
    cursor: pointer;
}
.cta-btn:hover {
    background: var(--brand-purple-dark);
}

/* Live Stream Specific Styles */
.live-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    margin-bottom: 40px;
    align-items: stretch; 
}

.player-section {
    min-height: 0;
    display: flex;
    flex-direction: column;
}

.player-card {
    flex: 1; 
    display: flex;
    flex-direction: column;
}

.live-player {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.player-container {
    flex: 1;
    min-height: 400px;
    background: #000;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
}

.player-container iframe {
    width: 100%;
    height: 100%;
    border: none;
}

.player-controls {
    display: flex;
    gap: 12px;
    padding: 16px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid var(--glass-border);
}

.control-btn {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    color: var(--text-light);
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.control-btn:hover {
    background: var(--brand-purple);
    color: #fff;
    transform: translateY(-2px);
}

.control-btn.small {
    padding: 6px 10px;
    font-size: 0.8rem;
}

.chat-section {
    min-height: 0;
    display: flex;
    flex-direction: column;
}

.chat-card {
    flex: 1; 
    display: flex;
    flex-direction: column;
    height: 100%; 
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid var(--glass-border);
    background: rgba(142, 68, 173, 0.1);
}

.chat-header h3 {
    margin: 0;
    color: #fff;
    font-size: 1.1rem;
}

.chat-controls {
    display: flex;
    gap: 8px;
}

.chat-messages {
    flex: 1; 
    padding: 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.chat-message {
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--glass-border);
    border-radius: 12px;
    padding: 12px;
    backdrop-filter: blur(10px);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    0% { opacity: 0; transform: translateY(20px); }
    50% { opacity: 0.5; }
    100% { opacity: 1; transform: translateY(0); }
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.username {
    color: var(--brand-purple);
    font-weight: 600;
    font-size: 0.9rem;
}

.timestamp {
    color: #999;
    font-size: 0.8rem;
}

.message-content {
    color: var(--text-light);
    font-size: 0.9rem;
    line-height: 1.4;
}

.message-actions {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid var(--glass-border);
}

.btn-overlay {
    background: rgba(52, 152, 219, 0.2);
    border: 1px solid rgba(52, 152, 219, 0.3);
    color: #3498db;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-overlay:hover {
    background: rgba(52, 152, 219, 0.3);
    transform: translateY(-1px);
}

.system-message {
    background: rgba(46, 204, 113, 0.2);
    border: 1px solid rgba(46, 204, 113, 0.3);
    color: #2ecc71;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    font-size: 0.9rem;
}

.chat-offline-message {
    background: rgba(149, 165, 166, 0.2);
    border: 1px solid rgba(149, 165, 166, 0.3);
    color: #95a5a6;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    font-size: 0.9rem;
}

.chat-input-container {
    padding: 16px;
    border-top: 1px solid var(--glass-border);
    background: rgba(0,0,0,0.1);
    margin-top: auto; 
}

.chat-input-group {
    display: flex;
    gap: 12px;
    align-items: center;
}

.chat-input-group input {
    flex: 1;
    padding: 12px;
    border-radius: 20px;
    border: 1px solid var(--glass-border);
    background: rgba(255,255,255,0.06);
    color: var(--text-light);
    font-size: 0.9rem;
}

.chat-input-group input:focus {
    outline: none;
    border-color: var(--brand-purple);
    box-shadow: 0 0 0 2px rgba(142, 68, 173, 0.2);
}

.send-btn {
    background: var(--brand-purple);
    border: none;
    color: #fff;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.send-btn:hover {
    background: var(--brand-purple-dark);
    transform: scale(1.1);
}

.live-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    margin-top: 20px;
}

.live-active {
    background: rgba(46, 204, 113, 0.2);
    border: 1px solid rgba(46, 204, 113, 0.3);
    color: #2ecc71;
}

.live-offline {
    background: rgba(149, 165, 166, 0.2);
    border: 1px solid rgba(149, 165, 166, 0.3);
    color: #95a5a6;
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.offline-player {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #2c2c2e, #1c1c1e);
    border-radius: 12px;
    min-height: 400px;
}

.offline-content {
    text-align: center;
    padding: 40px;
}

.offline-content i {
    font-size: 4rem;
    color: #666;
    margin-bottom: 20px;
}

.offline-content h3 {
    color: #fff;
    margin-bottom: 16px;
    font-size: 1.5rem;
}

.offline-content p {
    color: #ccc;
    margin-bottom: 12px;
    line-height: 1.5;
}

.social-links {
    display: flex;
    gap: 16px;
    justify-content: center;
    margin-top: 24px;
}

.social-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    color: var(--text-light);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: var(--brand-purple);
    color: #fff;
    transform: translateY(-2px);
}

.overlay-preview {
    margin-top: 24px;
}

.overlay-content {
    min-height: 100px;
    background: rgba(0,0,0,0.3);
    border-radius: 8px;
    padding: 20px;
    border: 2px dashed var(--glass-border);
}

/* Estilos para a seção de Próximas Transmissões */

/* Instrução - Corrigido tamanho e negrito */
.schedule-instruction {
    text-align: center;
    color: var(--accent-gold);
    margin-top: 15px;
    margin-bottom: 30px;
    font-size: 1.15rem; /* Revertido para o tamanho anterior que o usuário aprovou */
    font-weight: 600; 
    line-height: 1.5;
}
.instruction-highlight {
    font-size: 1.25rem; /* Aumentado o destaque da instrução */
}


/* Grid de Palestras (3 colunas) */
.lectures-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin: 30px 0;
}

/* Card Individual de Palestra */
.lecture-card {
    border: 2px solid transparent;
    border-radius: 20px;
    padding: 20px;
    background: rgba(255,255,255,0.08);
    transition: box-shadow 0.28s ease, border-color 0.28s ease;
    cursor: default;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.lecture-card:hover {
    border-color: var(--brand-purple);
    box-shadow: 0 10px 20px rgba(142, 68, 173, 0.3);
    transform: none;
}

.lecture-image-container {
    position: relative;
    width: 100%;
    padding-bottom: 56.25%; /* 16:9 */
    overflow: hidden;
    border-radius: 12px;
}

.lecture-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.lecture-info {
    padding-top: 16px;
    flex-grow: 1;
}

.lecture-date {
    display: inline-block;
    background: var(--brand-purple); 
    color: #ffffff;
    padding: 8px 14px;
    border-radius: 14px;
    font-size: 0.95rem;
    font-weight: 700;
    margin-bottom: 12px;
}

.lecture-title {
    font-size: 1.2rem;
    color: var(--text-primary);
    margin: 8px 0 10px;
    font-weight: 700;
    line-height: 1.25rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    max-height: calc(1.25rem * 3); 
}

.lecture-speaker {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 800;
    font-size: 1.15rem;            
    color: var(--brand-purple-dark); 
    margin-bottom: 10px;
}

.lecture-speaker i {
    color: var(--accent-gold);
}

.lecture-summary {
    color: var(--text-secondary);
    font-size: 0.95rem;
    line-height: 1.4rem;
    display: -webkit-box;
    -webkit-line-clamp: 5;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    max-height: calc(1.4rem * 5);
    margin: 0;
}

.schedule-actions-bottom {
    margin-top: 20px;
    text-align: center;
}

.btn-reminder {
    background: linear-gradient(135deg, var(--accent-green), #229954);
    width: 100%;
    justify-content: center;
    font-size: 1rem;
    padding: 12px 20px;
    box-shadow: 0 4px 15px rgba(39, 174, 96, 0.4);
}

.btn-reminder:hover {
    background: var(--accent-green);
}


/* Responsive */
@media (max-width: 1200px) {
    .lectures-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 768px) {
    .live-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    .lectures-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
const CURRENT_USER_IS_ADMIN = <?php echo isAdmin() ? 'true' : 'false'; ?>;
const CURRENT_USER_NAME = '<?php echo addslashes($_SESSION['user_name'] ?? $_SESSION['nome'] ?? 'Você'); ?>';

let chatMessages = [];

document.addEventListener('DOMContentLoaded', function() {
    initChatLogic();
});

function initChatLogic() {
    const chatForm = document.getElementById('chatForm');
    
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('chatInput');
            const msg = input.value.trim();
            if (!msg) return;

            input.disabled = true;
            const sendBtn = chatForm.querySelector('.send-btn');
            const originalContent = sendBtn.innerHTML;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            const endpoint = 'chat-save.php';
            const formData = new FormData(chatForm);
            
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    const newMessage = {
                        id: Date.now(),
                        user_name: CURRENT_USER_NAME,
                        message: msg,
                        created_at: new Date().toISOString(),
                        isOwn: true,
                        isAdmin: CURRENT_USER_IS_ADMIN
                    };
                    
                    chatMessages.push(newMessage);
                    addMessageToChat(newMessage);
                    input.value = '';
                } else {
                    console.error('Erro ao enviar mensagem:', response.statusText);
                    alert('Erro ao enviar mensagem. Tente novamente.');
                }
            })
            .catch(error => {
                console.error('Erro de rede:', error);
                alert('Erro de conexão. Verifique sua rede.');
            })
            .finally(() => {
                input.disabled = false;
                sendBtn.innerHTML = originalContent;
                scrollChatToBottom();
            });
        });
    }
    
    loadRealMessagesFromApi();
}

function loadRealMessagesFromApi() {
    addInitialMessages(); 
}

function addInitialMessages() {
    const container = document.getElementById('chatMessagesContainer');
    if (container.children.length === 0) {
        const initialMessages = [
            { id: 1, user_name: 'Sistema', message: 'Chat da live iniciado!', created_at: new Date(Date.now() - 30000).toISOString(), isSystem: true },
            { id: 2, user_name: 'Admin', message: 'Bem-vindos à transmissão ao vivo! Em breve começaremos.', created_at: new Date(Date.now() - 120000).toISOString(), isAdmin: true }
        ];
        
        initialMessages.forEach(msg => {
            chatMessages.push(msg);
            addMessageToChat(msg);
        });
        scrollChatToBottom();
    }
}


function addMessageToChat(msg) {
    const container = document.getElementById('chatMessagesContainer');
    const messageElement = document.createElement('div');
    messageElement.className = 'chat-message';
    
    const time = new Date(msg.created_at).toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
    
    let overlayButton = '';
    if (CURRENT_USER_IS_ADMIN && !msg.isSystem) {
        overlayButton = `
        <div class="message-actions">
        <button class="btn-overlay" data-id="${msg.id}">
        <i class="fas fa-tv"></i> Exibir na Tela
        </button>
        </div>
        `;
    }
    
    messageElement.innerHTML = `
    <div class="message-header">
    <strong class="username">${escapeHtml(msg.user_name)}</strong>
    <small class="timestamp">[${time}]</small>
    </div>
    <div class="message-content">
    ${escapeHtml(msg.message)}
    </div>
    ${overlayButton}
    `;
    
    container.appendChild(messageElement);
    
    if (overlayButton) {
        addOverlayButtonEvents();
    }
}

function addOverlayButtonEvents() {
    document.querySelectorAll('.btn-overlay:not([data-bound])').forEach(btn => {
        btn.setAttribute('data-bound', 'true');
        btn.addEventListener('click', function() {
            const messageText = this.closest('.chat-message').querySelector('.message-content').textContent;
            const username = this.closest('.chat-message').querySelector('.username').textContent;
            
            const overlayContent = document.getElementById('overlayMessage');
            if (overlayContent) {
                overlayContent.innerHTML = `
                <div class="overlay-message" style="background: rgba(142, 68, 173, 0.2); border: 1px solid rgba(142, 68, 173, 0.3); padding: 16px; border-radius: 8px; color: #fff;">
                <strong style="color: var(--brand-purple);">${username}:</strong> ${messageText}
                </div>
                `;
            }
            
            // Feedback visual
            this.innerHTML = '<i class="fas fa-check"></i> Enviado!';
            this.style.background = 'rgba(46, 204, 113, 0.3)';
            this.style.color = '#2ecc71';
            
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-tv"></i> Exibir na Tela';
                this.style.background = 'rgba(52, 152, 219, 0.2)';
                this.style.color = '#3498db';
            }, 2000);
        });
    });
}

function scrollChatToBottom() {
    const chatContainer = document.getElementById('chatMessages');
    setTimeout(() => {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }, 50);
}

function escapeHtml(unsafe) {
    return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function toggleChat() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages.style.display === 'none') {
        chatMessages.style.display = 'flex';
    } else {
        chatMessages.style.display = 'none';
    }
}

function clearChat() {
    if (confirm('Limpar todas as mensagens do chat?')) {
        chatMessages = [];
        document.getElementById('chatMessagesContainer').innerHTML = '';
    }
}

function clearOverlay() {
    const overlayContent = document.getElementById('overlayMessage');
    if (overlayContent) {
        overlayContent.innerHTML = `
        <div class="empty-state">
        <i class="fas fa-tv"></i>
        <p>Nenhuma mensagem selecionada para o overlay</p>
        </div>
        `;
    }
}

function toggleFullscreen() {
    const player = document.querySelector('.player-container iframe');
    if (player && player.requestFullscreen) {
        player.requestFullscreen();
    } else {
        alert('Seu navegador não suporta tela cheia ou não há player ativo.');
    }
}

function togglePictureInPicture() {
    alert('Funcionalidade Picture-in-Picture em desenvolvimento. Será implementada em breve!');
}

/**
 * Função para escapar o conteúdo ICS para UTF-8.
 * Substitui vírgulas e ponto-e-vírgulas (delimitadores ICS) e newlines.
 * @param {string} value - A string a ser escapada
 * @returns {string} - A string escapada
 */
function escapeIcs(value) {
    if (!value) return '';
    return value
        .replace(/\\/g, '\\\\')
        .replace(/,/g, '\\,')
        .replace(/;/g, '\\;')
        .replace(/\n/g, '\\n')
        .replace(/\r/g, ''); 
}

/**
 * Função para gerar e baixar o arquivo .ICS, corrigindo caracteres especiais.
 * Usamos a propriedade CHARSET=UTF-8 e o escapeIcs.
 * @param {Event} e - O evento de clique
 */
function generateIcs(e) {
    e.preventDefault();
    const btn = e.currentTarget;
    // O atributo data-ics já foi codificado em JSON com htmlspecialchars no PHP,
    // mas o JSON.parse() o decodifica de volta para JavaScript.
    const icsData = JSON.parse(btn.getAttribute('data-ics'));
    
    // Extrair dados e garantir que todos os campos sejam escapados
    const title = escapeIcs(icsData.title || "Evento Translators101");
    const description = escapeIcs(icsData.description || "Palestra Exclusiva da Translators101");
    const start = icsData.start; 
    const end = icsData.end; 
    const reminder = icsData.reminder; 

    // Formato VCALENDAR
    const BEGIN = 'BEGIN:VCALENDAR';
    const VERSION = 'VERSION:2.0';
    const PRODID = 'PRODID:-//Translators101//Live Reminder//EN';
    const CALSCALE = 'CALSCALE:GREGORIAN';
    const METHOD = 'METHOD:PUBLISH';
    const END = 'END:VCALENDAR';
    
    // Adicionar CHARSET=UTF-8 nas propriedades para compatibilidade com acentos
    const DTSTART = `DTSTART:${start}`;
    const DTEND = `DTEND:${end}`;
    const SUMMARY = `SUMMARY;CHARSET=UTF-8:${title}`;
    const DESCRIPTION_ICS = `DESCRIPTION;CHARSET=UTF-8:${description}`;
    const UID = `UID:${Date.now()}-${Math.random().toString(36).substring(2, 9)}@translators101.com.br`;
    const DTSTAMP = `DTSTAMP:${new Date().toISOString().replace(/[-:]|\.\d{3}/g, '')}Z`;
    const LOCATION = 'LOCATION;CHARSET=UTF-8:Assista na Videoteca: [Seu Link Aqui]';

    // Configuração do Alarme (Reminder)
    const ALARM = `
BEGIN:VALARM
ACTION:DISPLAY
DESCRIPTION;CHARSET=UTF-8:Lembrete: ${title}
TRIGGER:-PT${reminder}M
END:VALARM
`;

    const VEVENT = `
BEGIN:VEVENT
${DTSTART}
${DTEND}
${SUMMARY}
${DESCRIPTION_ICS}
${LOCATION}
${UID}
${DTSTAMP}
${ALARM}
END:VEVENT
`;

    const icsContent = `${BEGIN}\r\n${VERSION}\r\n${PRODID}\r\n${CALSCALE}\r\n${METHOD}\r\n${VEVENT}\r\n${END}`;

    const filename = `Lembrete_${title.replace(/[^a-zA-Z0-9]/g, '_').substring(0, 30)}.ics`;
    
    // Usar 'text/calendar;charset=utf-8' no tipo do Blob é crucial
    const blob = new Blob([icsContent.replace(/\n/g, '\r\n')], { type: 'text/calendar;charset=utf-8' });

    // Cria um link temporário para download
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Feedback visual para o usuário
    btn.innerHTML = '<i class="fas fa-check"></i> Lembrete Baixado!';
    btn.style.background = 'linear-gradient(135deg, #2ecc71, #229954)';
    setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-calendar-plus"></i> Adicionar à agenda';
        btn.style.background = 'linear-gradient(135deg, var(--accent-green), #229954)';
    }, 2000);
}
</script>

<?php include __DIR__ . '/../vision/includes/footer.php'; ?>