<?php
// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug_errors.log');

session_start();

// Debug: Log informações básicas
error_log("DEBUG: Script iniciado. Method: " . $_SERVER['REQUEST_METHOD']);
error_log("DEBUG: Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("DEBUG: Session is_admin: " . ($_SESSION['is_admin'] ?? 'NOT SET'));

// Verificar se é admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    error_log("DEBUG: Acesso negado - não é admin");
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit();
}

error_log("DEBUG: Admin verificado, tentando incluir database.php");

// Tentar diferentes caminhos para database.php
$possible_paths = [
    __DIR__ . '/config/database.php',
    __DIR__ . '/../config/database.php', 
    './config/database.php',
    '../config/database.php'
];

$database_included = false;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        error_log("DEBUG: Encontrado database.php em: " . $path);
        require_once $path;
        $database_included = true;
        break;
    } else {
        error_log("DEBUG: Não encontrado em: " . $path);
    }
}

if (!$database_included) {
    error_log("ERROR: database.php não encontrado em nenhum caminho");
    http_response_code(500);
    echo json_encode(['error' => 'Erro de configuração do banco de dados']);
    exit();
}

error_log("DEBUG: Database incluído com sucesso");

// Verificar se a conexão PDO existe
if (!isset($pdo)) {
    error_log("ERROR: Variável \$pdo não está definida");
    http_response_code(500);
    echo json_encode(['error' => 'Conexão com banco de dados não estabelecida']);
    exit();
}

error_log("DEBUG: PDO connection verificada");

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
error_log("DEBUG: Method: " . $method);

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                error_log("DEBUG: Buscando anúncio com ID: " . $id);
                
                // Verificar se a tabela existe
                try {
                    $stmt = $pdo->query("DESCRIBE upcoming_announcements");
                    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    error_log("DEBUG: Tabela upcoming_announcements existe. Colunas: " . implode(', ', $columns));
                } catch (Exception $e) {
                    error_log("ERROR: Problema com tabela upcoming_announcements: " . $e->getMessage());
                    throw new Exception("Tabela não encontrada ou inacessível");
                }
                
                $stmt = $pdo->prepare("SELECT id, title, speaker, announcement_date, lecture_time, description, image_path FROM upcoming_announcements WHERE id = ?");
                $stmt->execute([$id]);
                $announcement = $stmt->fetch(PDO::FETCH_ASSOC);
                
                error_log("DEBUG: Resultado da query: " . print_r($announcement, true));
                
                if ($announcement) {
                    // Converter para formato esperado pelo form (e renomear para consistência)
                    $announcement['lecture_date'] = $announcement['announcement_date'];
                    // Adiciona o novo campo
                    $announcement['lecture_time'] = $announcement['lecture_time'] ?? '19:00'; 
                    unset($announcement['announcement_date']);
                    echo json_encode($announcement);
                } else {
                    error_log("DEBUG: Anúncio não encontrado para ID: " . $id);
                    http_response_code(404);
                    echo json_encode(['error' => 'Anúncio não encontrado']);
                }
            } else {
                error_log("DEBUG: Buscando todos os anúncios ativos");
                $stmt = $pdo->query("
                    SELECT id, title, speaker, announcement_date, lecture_time, description, image_path
                    FROM upcoming_announcements 
                    WHERE is_active = 1 
                    AND announcement_date >= CURDATE()
                    ORDER BY announcement_date ASC
                    LIMIT 3
                ");
                $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("DEBUG: Encontrados " . count($announcements) . " anúncios");
                echo json_encode($announcements);
            }
            break;
        
        case 'POST':
            error_log("DEBUG: Processando POST request");
            // Adicionar ou atualizar anúncio
            $announcementId = $_POST['lectureId'] ?? null; 
            $speaker = $_POST['lectureSpeaker'] ?? '';
            $title = $_POST['lectureTitle'] ?? '';
            $announcementDate = $_POST['lectureDate'] ?? '';
            $description = $_POST['lectureSummary'] ?? '';
            $lectureTime = $_POST['lectureTime'] ?? '';
            
            error_log("DEBUG: Dados recebidos - ID: $announcementId, Title: $title, Speaker: $speaker");
            
            // Upload da imagem (simplificado para debug)
            $imagePath = '';
            if (isset($_FILES['lectureImage']) && $_FILES['lectureImage']['error'] === UPLOAD_ERR_OK) {
                error_log("DEBUG: Processando upload de imagem");
                // Lógica de upload mantida do arquivo original...
            }
            
            if (empty($announcementId) || strpos($announcementId, 'default-') === 0) {
                // Inserir novo anúncio
                $newId = bin2hex(random_bytes(16));
                error_log("DEBUG: Inserindo novo anúncio com ID: " . $newId);
                // Query de inserção...
            } else {
                error_log("DEBUG: Atualizando anúncio existente: " . $announcementId);
                // Query de atualização...
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Debug: Operação concluída com sucesso!',
                'id' => $announcementId ?? $newId
            ]);
            break;
        
        case 'DELETE':
            error_log("DEBUG: Processando DELETE request");
            if (isset($_GET['id'])) {
                $announcementId = $_GET['id'];
                error_log("DEBUG: Deletando anúncio ID: " . $announcementId);
                
                $stmt = $pdo->prepare("DELETE FROM upcoming_announcements WHERE id = ?");
                $stmt->execute([$announcementId]);
                
                echo json_encode(['success' => true, 'message' => 'Anúncio deletado com sucesso!']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID do anúncio não fornecido']);
            }
            break;
        
        default:
            error_log("DEBUG: Método não permitido: " . $method);
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
    }
    
} catch (Exception $e) {
    error_log("ERROR: Exception capturada: " . $e->getMessage());
    error_log("ERROR: Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'Erro: ' . $e->getMessage(), 'debug' => true]);
}

error_log("DEBUG: Script finalizado");
?>