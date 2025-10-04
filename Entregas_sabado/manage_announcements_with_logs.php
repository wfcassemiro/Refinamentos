<?php
// Habilitar logs detalhados
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/manage_announcements.log');

// Log de início
error_log("=== INÍCIO DA REQUISIÇÃO ===");
error_log("Timestamp: " . date('Y-m-d H:i:s'));
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Query String: " . ($_SERVER['QUERY_STRING'] ?? 'VAZIA'));
error_log("POST Data: " . print_r($_POST, true));
error_log("GET Data: " . print_r($_GET, true));

session_start();

// Log da sessão
error_log("Sessão - user_id: " . ($_SESSION['user_id'] ?? 'NÃO DEFINIDO'));
error_log("Sessão - is_admin: " . ($_SESSION['is_admin'] ?? 'NÃO DEFINIDO'));

// Verificar se é admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    error_log("ERRO: Acesso negado - usuário não é admin");
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit();
}

error_log("SUCCESS: Usuário autorizado como admin");

require_once 'config/database.php';

error_log("SUCCESS: Database incluído");

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
error_log("Processando method: $method");

try {
    switch ($method) {
        case 'POST':
            error_log("=== PROCESSANDO POST ===");
            
            $announcementId = $_POST['lectureId'] ?? null;
            $speaker = $_POST['lectureSpeaker'] ?? '';
            $title = $_POST['lectureTitle'] ?? '';
            $announcementDate = $_POST['lectureDate'] ?? '';
            $description = $_POST['lectureSummary'] ?? '';
            $lectureTime = $_POST['lectureTime'] ?? '';
            
            error_log("Dados recebidos:");
            error_log("- ID: $announcementId");
            error_log("- Title: $title");
            error_log("- Speaker: $speaker");
            error_log("- Date: $announcementDate");
            error_log("- Time: $lectureTime");
            
            // Upload da imagem
            $imagePath = '';
            if (isset($_FILES['lectureImage']) && $_FILES['lectureImage']['error'] === UPLOAD_ERR_OK) {
                error_log("Processando upload de imagem...");
                // ... lógica de upload ...
            } else {
                error_log("Nenhuma imagem para upload");
            }
            
            if (empty($announcementId) || strpos($announcementId, 'default-') === 0) {
                error_log("Inserindo novo anúncio...");
                $newId = bin2hex(random_bytes(16));
                $stmt = $pdo->prepare("
                    INSERT INTO upcoming_announcements (id, title, speaker, announcement_date, lecture_time, description, image_path, display_order) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, (SELECT IFNULL(MAX(display_order), 0) + 1 FROM upcoming_announcements AS ua))
                ");
                $stmt->execute([$newId, $title, $speaker, $announcementDate, $lectureTime, $description, $imagePath]);
                $announcementId = $newId;
                error_log("Novo anúncio inserido com ID: $newId");
            } else {
                error_log("Atualizando anúncio existente: $announcementId");
                if (!empty($imagePath)) {
                    $stmt = $pdo->prepare("
                        UPDATE upcoming_announcements 
                        SET title = ?, speaker = ?, announcement_date = ?, lecture_time = ?, description = ?, image_path = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$title, $speaker, $announcementDate, $lectureTime, $description, $imagePath, $announcementId]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE upcoming_announcements 
                        SET title = ?, speaker = ?, announcement_date = ?, lecture_time = ?, description = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$title, $speaker, $announcementDate, $lectureTime, $description, $announcementId]);
                }
                error_log("Anúncio atualizado com sucesso");
            }
            
            $response = [
                'success' => true,
                'message' => 'Anúncio de palestra salvo com sucesso!',
                'id' => $announcementId
            ];
            error_log("Resposta POST: " . json_encode($response));
            echo json_encode($response);
            break;
        
        case 'GET':
            error_log("=== PROCESSANDO GET ===");
            
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                error_log("Buscando anúncio com ID: $id");
                
                $stmt = $pdo->prepare("SELECT id, title, speaker, announcement_date, lecture_time, description, image_path FROM upcoming_announcements WHERE id = ?");
                $stmt->execute([$id]);
                $announcement = $stmt->fetch(PDO::FETCH_ASSOC);
                
                error_log("Resultado da query: " . print_r($announcement, true));
                
                if ($announcement) {
                    $announcement['lecture_date'] = $announcement['announcement_date'];
                    $announcement['lecture_time'] = $announcement['lecture_time'] ?? '19:00';
                    unset($announcement['announcement_date']);
                    
                    error_log("Dados formatados: " . print_r($announcement, true));
                    echo json_encode($announcement);
                } else {
                    error_log("ERRO: Anúncio não encontrado para ID: $id");
                    http_response_code(404);
                    echo json_encode(['error' => 'Anúncio não encontrado']);
                }
            } else {
                error_log("Buscando todos os anúncios ativos");
                $stmt = $pdo->query("
                    SELECT id, title, speaker, announcement_date, lecture_time, description, image_path
                    FROM upcoming_announcements 
                    WHERE is_active = 1 
                    AND announcement_date >= CURDATE()
                    ORDER BY announcement_date ASC
                    LIMIT 3
                ");
                $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("Encontrados " . count($announcements) . " anúncios");
                echo json_encode($announcements);
            }
            break;
        
        case 'DELETE':
            error_log("=== PROCESSANDO DELETE ===");
            
            if (isset($_GET['id'])) {
                $announcementId = $_GET['id'];
                error_log("Deletando anúncio ID: $announcementId");
                
                $stmt = $pdo->prepare("SELECT image_path FROM upcoming_announcements WHERE id = ?");
                $stmt->execute([$announcementId]);
                $announcement = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($announcement && !empty($announcement['image_path'])) {
                    $documentRoot = $_SERVER['DOCUMENT_ROOT'];
                    $imagePath = $documentRoot . $announcement['image_path'];
                    
                    if (file_exists($imagePath)) {
                        @unlink($imagePath);
                        error_log("Imagem deletada: $imagePath");
                    }
                }
                
                $stmt = $pdo->prepare("DELETE FROM upcoming_announcements WHERE id = ?");
                $stmt->execute([$announcementId]);
                
                error_log("Anúncio deletado com sucesso");
                echo json_encode(['success' => true, 'message' => 'Anúncio deletado com sucesso!']);
            } else {
                error_log("ERRO: ID não fornecido para DELETE");
                http_response_code(400);
                echo json_encode(['error' => 'ID do anúncio não fornecido']);
            }
            break;
        
        default:
            error_log("ERRO: Método não permitido - $method");
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
    }
    
} catch (Exception $e) {
    error_log("EXCEPTION: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'Erro: ' . $e->getMessage()]);
}

error_log("=== FIM DA REQUISIÇÃO ===");
?>