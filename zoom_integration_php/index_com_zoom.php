<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/zoom_functions.php';

// Funções de Acesso
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
    }
}
if (!function_exists('hasVideotecaAccess')) {
    function hasVideotecaAccess() {
        return isLoggedIn();
    }
}

// Verificar acesso
if (!isLoggedIn() || !hasVideotecaAccess()) {
    header("Location: /planos.php");
    exit;
}

$page_title = 'Live Stream - Translators101';
$page_description = 'Assista às palestras ao vivo da Translators101';

// Buscar embed code do banco (FALLBACK)
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

// NOVA LÓGICA: Verificar se há reunião do Zoom programada para transmitir
$zoomMeetingToShow = null;
$showZoomLive = false;

// Verificar configuração manual do admin (tabela: zoom_meetings, campo: show_live)
try {
    $stmt = $pdo->prepare("
        SELECT * FROM zoom_meetings
        WHERE is_active = 1
        AND show_live = 1
        ORDER BY start_time DESC
        LIMIT 1
    ");
    $stmt->execute();
    $zoomMeetingToShow = $stmt->fetch();
    
    if ($zoomMeetingToShow) {
        $showZoomLive = true;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar reunião Zoom para exibir: " . $e->getMessage());
}

// Determinar o que exibir
if ($showZoomLive && $zoomMeetingToShow) {
    $is_live_active = true;
    $meeting_type = 'zoom';
} else if (!empty(trim($live_embed_code))) {
    $is_live_active = true;
    $meeting_type = 'embed';
} else {
    $is_live_active = false;
    $meeting_type = 'none';
}

$current_user_is_admin = isAdmin();

// Buscar PRÓXIMAS palestras/anúncios
$upcomingLectures = [];
try {
    $stmt = $pdo->query("
        SELECT id, title, speaker, description, image_path, announcement_date, lecture_time
        FROM upcoming_announcements
        WHERE is_active = 1
        AND announcement_date >= CURDATE()
        ORDER BY announcement_date ASC, display_order ASC
        LIMIT 3
    ");
    $upcomingLectures = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $upcomingLectures = [];
}

// NOVA: Buscar próximas reuniões do Zoom com formato S##E##
$upcomingZoomMeetings = [];
try {
    $stmt = $pdo->query("
        SELECT * FROM zoom_meetings
        WHERE is_active = 1
        AND start_time >= NOW()
        AND topic REGEXP '^S[0-9]{2}E[0-9]{2}'
        ORDER BY start_time ASC
        LIMIT 6
    ");
    $upcomingZoomMeetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao buscar reuniões Zoom futuras: " . $e->getMessage());
    $upcomingZoomMeetings = [];
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
                    <?php if ($meeting_type === 'zoom'): ?>
                        <!-- EXIBIR REUNIÃO DO ZOOM -->
                        <div class="zoom-live-info">
                            <div class="zoom-badge">
                                <i class="fab fa-zoom"></i> Zoom Meeting
                            </div>
                            <h3><?php echo htmlspecialchars($zoomMeetingToShow['topic']); ?></h3>
                            <div class="zoom-details">
                                <div class="zoom-detail-item">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($zoomMeetingToShow['start_time'])); ?>
                                </div>
                                <div class="zoom-detail-item">
                                    <i class="fas fa-hourglass-half"></i>
                                    <?php echo $zoomMeetingToShow['duration']; ?> minutos
                                </div>
                            </div>
                            <?php if (!empty($zoomMeetingToShow['agenda'])): ?>
                                <p class="zoom-agenda"><?php echo htmlspecialchars($zoomMeetingToShow['agenda']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="live-player zoom-player">
                            <div class="player-container">
                                <iframe 
                                    src="<?php echo htmlspecialchars($zoomMeetingToShow['join_url']); ?>" 
                                    allow="microphone; camera; fullscreen"
                                    allowfullscreen
                                    style="width: 100%; height: 100%; border: none;">
                                </iframe>
                            </div>
                            <div class="player-controls">
                                <a href="<?php echo htmlspecialchars($zoomMeetingToShow['join_url']); ?>" 
                                   target="_blank" 
                                   class="control-btn zoom-join-btn">
                                    <i class="fas fa-external-link-alt"></i> Abrir no Zoom
                                </a>
                                <button class="control-btn" onclick="toggleFullscreen()">
                                    <i class="fas fa-expand"></i> Tela Cheia
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- EXIBIR EMBED PADRÃO -->
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
                    <?php endif; ?>
                <?php else: ?>
                    <!-- OFFLINE -->
                    <div class="offline-player">
                        <div class="offline-content">
                            <i class="fas fa-video-slash"></i>
                            <h3>Transmissão Offline</h3>
                            <p>No momento não há transmissões ao vivo.</p>
                            <p>Confira as próximas reuniões agendadas abaixo!</p>
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

    <!-- NOVA SEÇÃO: Próximas Reuniões Zoom (S##E##) -->
    <?php if (!empty($upcomingZoomMeetings)): ?>
        <div class="video-card schedule-card zoom-schedule">
            <h2><i class="fab fa-zoom"></i> Próximos Episódios no Zoom</h2>
            <p class="schedule-instruction instruction-highlight">Reuniões agendadas com formato S##E## (Season/Episode)</p>
            
            <div class="lectures-grid" id="zoomMeetingsContainer">
                <?php foreach ($upcomingZoomMeetings as $meeting): ?>
                    <div class="lecture-card zoom-meeting-card">
                        <div class="zoom-episode-badge">
                            <?php
                            // Extrair S##E## do título
                            preg_match('/^(S[0-9]{2}E[0-9]{2})/', $meeting['topic'], $matches);
                            $episode = $matches[1] ?? 'EPISÓDIO';
                            ?>
                            <span class="episode-tag"><?php echo htmlspecialchars($episode); ?></span>
                        </div>
                        <div class="lecture-info">
                            <div class="lecture-datetime">
                                <div class="lecture-date">
                                    <?php echo date('d/m/Y', strtotime($meeting['start_time'])); ?>
                                </div>
                                <div class="lecture-time">
                                    <?php echo date('H:i', strtotime($meeting['start_time'])); ?>h
                                </div>
                            </div>
                            <h4 class="lecture-title"><?php echo htmlspecialchars($meeting['topic']); ?></h4>
                            <div class="lecture-speaker">
                                <i class="fas fa-video"></i>
                                <span>Reunião Zoom</span>
                            </div>
                            <?php if (!empty($meeting['agenda'])): ?>
                                <p class="lecture-summary">
                                    <?php echo htmlspecialchars($meeting['agenda']); ?>
                                </p>
                            <?php endif; ?>
                            <div class="zoom-meeting-info">
                                <div class="info-badge">
                                    <i class="fas fa-clock"></i> <?php echo $meeting['duration']; ?> min
                                </div>
                                <?php if (!empty($meeting['password'])): ?>
                                    <div class="info-badge">
                                        <i class="fas fa-lock"></i> Protegida
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="schedule-actions-bottom">
                            <hr class="agenda-divider">
                            <a href="<?php echo htmlspecialchars($meeting['join_url']); ?>" 
                               target="_blank" 
                               class="cta-btn btn-zoom-join" 
                               data-testid="zoom-join-<?php echo $meeting['meeting_id']; ?>">
                                <i class="fab fa-zoom"></i> Entrar na Reunião
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Agenda T101 Original (upcoming_announcements) -->
    <?php if (!empty($upcomingLectures)): ?>
        <div class="video-card schedule-card">
            <h2><i class="fas fa-calendar-alt"></i> Agenda T101</h2>
            <p class="schedule-instruction instruction-highlight">Baixe o arquivo de convite e clique nele para incluir um lembrete em sua agenda.</p>
            
            <div class="lectures-grid" id="lecturesContainer">
                <?php 
                date_default_timezone_set('America/Sao_Paulo');
                foreach ($upcomingLectures as $lecture): 
                    $announcementDate = $lecture['announcement_date'] ?? '';
                    $lectureTime = $lecture['lecture_time'] ?? '19:00:00';
                    $defaultDuration = 90;

                    try {
                        $dateTimeStart = new DateTime($announcementDate . ' ' . $lectureTime);
                    } catch (Exception $e) {
                        $dateTimeStart = new DateTime($announcementDate . ' 19:00:00');
                    }
                    
                    $dateTimeEnd = clone $dateTimeStart;
                    $dateTimeEnd->modify("+{$defaultDuration} minutes");

                    $formattedDate = $dateTimeStart->format('d \d\e F, Y');
                    $formattedTime = $dateTimeStart->format('H:i');
                    $monthNames = [
                        'January' => 'Janeiro', 'February' => 'Fevereiro', 'March' => 'Março',
                        'April' => 'Abril', 'May' => 'Maio', 'June' => 'Junho',
                        'July' => 'Julho', 'August' => 'Agosto', 'September' => 'Setembro',
                        'October' => 'Outubro', 'November' => 'Novembro', 'December' => 'Dezembro'
                    ];
                    $formattedDate = str_replace(array_keys($monthNames), array_values($monthNames), $formattedDate);

                    $eventData = [
                        'title' => $lecture['title'] ?? 'Palestra T101',
                        'description' => $lecture['description'] ?? 'Sem descrição.',
                        'speaker' => $lecture['speaker'] ?? 'Palestrante',
                        'start' => $dateTimeStart->format('Ymd\THis'), 
                        'end' => $dateTimeEnd->format('Ymd\THis'),
                        'start_utc' => $dateTimeStart->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z'),
                        'end_utc' => $dateTimeEnd->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z'),
                        'reminder' => 30,
                    ];
                ?>
                <div class="lecture-card">
                    <div class="lecture-image-container">
                        <img src="<?php echo htmlspecialchars($lecture['image_path'] ?? '/images/palestra-placeholder.jpg'); ?>" alt="Palestra" class="lecture-image">
                    </div>
                    <div class="lecture-info">
                        <div class="lecture-datetime">
                            <div class="lecture-date"><?php echo htmlspecialchars($formattedDate); ?></div>
                            <div class="lecture-time"><?php echo htmlspecialchars($formattedTime); ?>h</div>
                        </div>
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
                        <hr class="agenda-divider">
                        <h4 class="agenda-title">Incluir na minha agenda</h4>
                        <div class="agenda-buttons-container">
                            <a href="#" 
                               class="cta-btn btn-agenda btn-google-cal" 
                               data-event='<?php echo htmlspecialchars(json_encode($eventData), ENT_QUOTES, 'UTF-8'); ?>'
                               onclick="generateGoogleCalendarLink(event)">
                                <i class="fab fa-google"></i> Google
                            </a>
                            <a href="#" 
                               class="cta-btn btn-agenda btn-apple-cal" 
                               data-event='<?php echo htmlspecialchars(json_encode($eventData), ENT_QUOTES, 'UTF-8'); ?>'
                               onclick="generateIcs(event)">
                                <i class="fab fa-apple"></i> Apple
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>