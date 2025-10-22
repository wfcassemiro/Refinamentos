<?php
/**
 * Funções para gerenciamento de reuniões do Zoom
 * 
 * ESCOPOS NECESSÁRIOS (Formato Granular):
 * - meeting:write:meeting:admin   (Criar reuniões)
 * - meeting:read:meeting:admin    (Ler informações de reuniões)
 * - meeting:update:meeting:admin  (Atualizar reuniões)
 * - meeting:delete:meeting:admin  (Deletar reuniões)
 * - user:read:user:admin          (Ler informações de usuários - necessário para host)
 * 
 * IMPORTANTE: Use o formato granular com repetição do recurso!
 * Exemplo: meeting:write:meeting:admin (NÃO meeting:write:admin)
 */

require_once 'zoom_config.php';
require_once 'zoom_auth.php';

/**
 * Obter informações do usuário Zoom (para usar como host)
 * ESCOPO NECESSÁRIO: user:read:user:admin
 */
function getZoomUser() {
    // Primeiro, tentar listar usuários da conta
    $result = zoomApiRequest('/users?status=active&page_size=1');
    
    if ($result['success'] && !empty($result['data']['users'])) {
        // Retornar o primeiro usuário ativo
        return $result['data']['users'][0];
    }
    
    // Se falhar, tentar /users/me
    $result = zoomApiRequest('/users/me');
    
    if ($result['success']) {
        return $result['data'];
    }
    
    return null;
}

/**
 * Criar uma nova reunião no Zoom
 * ESCOPO NECESSÁRIO: meeting:write:meeting:admin
 */
function createZoomMeeting($topic, $startTime, $duration, $agenda = '', $timezone = 'America/Sao_Paulo') {
    $user = getZoomUser();
    
    // Se não conseguir pegar o usuário, usar 'me' como fallback
    if (!$user) {
        $userId = 'me';
    } else {
        $userId = $user['id'];
    }
    
    // Formatar data/hora para ISO 8601
    $startTimeFormatted = date('Y-m-d\TH:i:s', strtotime($startTime));
    
    $meetingData = [
        'topic' => $topic,
        'type' => 2, // Reunião agendada
        'start_time' => $startTimeFormatted,
        'duration' => (int)$duration,
        'timezone' => $timezone,
        'agenda' => $agenda,
        'settings' => [
            'host_video' => true,
            'participant_video' => true,
            'join_before_host' => false,
            'mute_upon_entry' => true,
            'watermark' => false,
            'use_pmi' => false,
            'approval_type' => 0, // Aprovação automática
            'audio' => 'both',
            'auto_recording' => 'none',
            'waiting_room' => true,
            'allow_multiple_devices' => true
        ]
    ];
    
    $result = zoomApiRequest('/users/' . $userId . '/meetings', 'POST', $meetingData);
    
    if ($result['success']) {
        // Salvar no banco de dados
        $meeting = $result['data'];
        saveMeetingToDatabase($meeting);
        
        return [
            'success' => true,
            'meeting' => $meeting
        ];
    }
    
    return $result;
}

/**
 * Obter informações de uma reunião existente
 * ESCOPO NECESSÁRIO: meeting:read:meeting:admin
 */
function getZoomMeeting($meetingId) {
    $result = zoomApiRequest('/meetings/' . $meetingId);
    
    if ($result['success']) {
        // Atualizar no banco de dados
        saveMeetingToDatabase($result['data']);
        
        return [
            'success' => true,
            'meeting' => $result['data']
        ];
    }
    
    return $result;
}

/**
 * Listar todas as reuniões do usuário
 * ESCOPO NECESSÁRIO: meeting:read:meeting:admin
 */
function listZoomMeetings($type = 'scheduled') {
    $user = getZoomUser();
    
    // Se não conseguir pegar o usuário, usar 'me' como fallback
    if (!$user) {
        $userId = 'me';
    } else {
        $userId = $user['id'];
    }
    
    $result = zoomApiRequest('/users/' . $userId . '/meetings?type=' . $type);
    
    return $result;
}

/**
 * Deletar uma reunião do Zoom
 * ESCOPO NECESSÁRIO: meeting:delete:meeting:admin
 */
function deleteZoomMeeting($meetingId) {
    $result = zoomApiRequest('/meetings/' . $meetingId, 'DELETE');
    
    if ($result['success']) {
        // Remover do banco de dados
        deleteMeetingFromDatabase($meetingId);
        
        return [
            'success' => true,
            'message' => 'Reunião deletada com sucesso'
        ];
    }
    
    return $result;
}

/**
 * Atualizar uma reunião existente
 * ESCOPO NECESSÁRIO: meeting:update:meeting:admin
 */
function updateZoomMeeting($meetingId, $data) {
    $result = zoomApiRequest('/meetings/' . $meetingId, 'PATCH', $data);
    
    if ($result['success']) {
        // Atualizar no banco de dados
        $meeting = getZoomMeeting($meetingId);
        if ($meeting['success']) {
            saveMeetingToDatabase($meeting['meeting']);
        }
        
        return [
            'success' => true,
            'message' => 'Reunião atualizada com sucesso'
        ];
    }
    
    return $result;
}

/**
 * Salvar reunião no banco de dados local
 */
function saveMeetingToDatabase($meeting) {
    $pdo = getDbConnection();
    
    $sql = "INSERT INTO zoom_meetings 
            (meeting_id, topic, start_time, duration, timezone, join_url, start_url, password, agenda, host_id, status) 
            VALUES 
            (:meeting_id, :topic, :start_time, :duration, :timezone, :join_url, :start_url, :password, :agenda, :host_id, :status)
            ON DUPLICATE KEY UPDATE
            topic = VALUES(topic),
            start_time = VALUES(start_time),
            duration = VALUES(duration),
            timezone = VALUES(timezone),
            join_url = VALUES(join_url),
            start_url = VALUES(start_url),
            password = VALUES(password),
            agenda = VALUES(agenda),
            status = VALUES(status)";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':meeting_id' => $meeting['id'],
            ':topic' => $meeting['topic'],
            ':start_time' => date('Y-m-d H:i:s', strtotime($meeting['start_time'])),
            ':duration' => $meeting['duration'],
            ':timezone' => $meeting['timezone'] ?? 'America/Sao_Paulo',
            ':join_url' => $meeting['join_url'],
            ':start_url' => $meeting['start_url'] ?? '',
            ':password' => $meeting['password'] ?? '',
            ':agenda' => $meeting['agenda'] ?? '',
            ':host_id' => $meeting['host_id'] ?? '',
            ':status' => $meeting['status'] ?? 'scheduled'
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Erro ao salvar reunião no BD: " . $e->getMessage());
        return false;
    }
}

/**
 * Deletar reunião do banco de dados local
 */
function deleteMeetingFromDatabase($meetingId) {
    $pdo = getDbConnection();
    
    try {
        $stmt = $pdo->prepare("UPDATE zoom_meetings SET is_active = 0 WHERE meeting_id = :meeting_id");
        $stmt->execute([':meeting_id' => $meetingId]);
        return true;
    } catch (PDOException $e) {
        error_log("Erro ao deletar reunião do BD: " . $e->getMessage());
        return false;
    }
}

/**
 * Obter reuniões ativas do banco de dados
 * Suporta filtragem por padrão S##E## (Season/Episode)
 */
function getActiveMeetingsFromDatabase($limit = 10, $filterSeasonEpisode = false) {
    $pdo = getDbConnection();
    
    try {
        $sql = "SELECT * FROM zoom_meetings WHERE is_active = 1 AND start_time >= NOW()";
        
        // Filtrar por formato S##E## (compatível com MySQL e MariaDB)
        if ($filterSeasonEpisode) {
            // Usando LIKE para maior compatibilidade
            $sql .= " AND (topic LIKE 'S__E__%' OR topic LIKE 'S__E__-%')";
        }
        
        $sql .= " ORDER BY start_time ASC LIMIT :limit";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $results = $stmt->fetchAll();
        
        // Filtro adicional em PHP para garantir formato exato S##E##
        if ($filterSeasonEpisode && !empty($results)) {
            $results = array_filter($results, function($meeting) {
                return preg_match('/^S[0-9]{2}E[0-9]{2}/', $meeting['topic']);
            });
        }
        
        return array_values($results); // Reindexar array
    } catch (PDOException $e) {
        error_log("Erro ao buscar reuniões do BD: " . $e->getMessage());
        return [];
    }
}

/**
 * Obter reunião atual (acontecendo agora)
 */
function getCurrentMeeting() {
    $pdo = getDbConnection();
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM zoom_meetings 
            WHERE is_active = 1 
            AND start_time <= NOW() 
            AND DATE_ADD(start_time, INTERVAL duration MINUTE) >= NOW()
            ORDER BY start_time DESC 
            LIMIT 1
        ");
        $stmt->execute();
        
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Erro ao buscar reunião atual: " . $e->getMessage());
        return null;
    }
}

/**
 * Extrair Meeting ID de um link do Zoom
 */
function extractMeetingIdFromUrl($url) {
    // Padrões comuns: 
    // https://zoom.us/j/1234567890
    // https://us05web.zoom.us/j/1234567890?pwd=xxxxx
    
    if (preg_match('/\/j\/(\d+)/', $url, $matches)) {
        return $matches[1];
    }
    
    // Se for apenas o ID
    if (is_numeric($url)) {
        return $url;
    }
    
    return null;
}

/**
 * Adicionar reunião existente pelo ID ou URL
 * ESCOPO NECESSÁRIO: meeting:read:meeting:admin
 */
function addExistingMeeting($meetingIdOrUrl) {
    $meetingId = extractMeetingIdFromUrl($meetingIdOrUrl);
    
    if (!$meetingId) {
        return [
            'success' => false,
            'error' => 'ID ou URL da reunião inválido'
        ];
    }
    
    return getZoomMeeting($meetingId);
}

?>