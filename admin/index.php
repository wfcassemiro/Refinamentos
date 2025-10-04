<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verificar se é admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: /login.php');
    exit;
}

$page_title = 'Painel Administrativo - Translators101';
$page_description = 'Administração da plataforma Translators101';

// Processar formulário de embed
$message = '';
$message_type = '';

if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_embed') {
        try {
            $embed_code = trim($_POST['embed_code']);

            // Verificar se já existe uma configuração
            $stmt = $pdo->prepare("SELECT id FROM site_settings WHERE setting_key = 'live_embed_code'");
            $stmt->execute();
            $existing = $stmt->fetch();

            if ($existing) {
                // Atualizar existente
                $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'live_embed_code'");
                $stmt->execute([$embed_code]);
            } else {
                // Inserir novo
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, created_at, updated_at) VALUES ('live_embed_code', ?, NOW(), NOW())");
                $stmt->execute([$embed_code]);
            }

            $message = 'Código de embed atualizado com sucesso!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Erro ao atualizar código de embed: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Obter código de embed atual
$current_embed = '';
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'live_embed_code'");
    $stmt->execute();
    $result = $stmt->fetch();
    if ($result) {
        $current_embed = $result['setting_value'];
    }
} catch (PDOException $e) {
    // Tabela pode não existir ainda
}

// Obter estatísticas básicas
try {
    // Total de usuários ativos (is_active = 1 e is_subscriber = 1)
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE is_active = 1 AND is_subscriber = 1");
    $total_users = $stmt->fetchColumn();

    // Total de palestras (todas as entradas na tabela lectures)
    $stmt = $pdo->query("SELECT COUNT(*) as total_lectures FROM lectures");
    $total_lectures = $stmt->fetchColumn();

    // Total de certificados gerados
    $stmt = $pdo->query("SELECT COUNT(*) as total_certificates FROM certificates");
    $total_certificates = $stmt->fetchColumn();

    // Total de glossários ativos
    $stmt = $pdo->query("SELECT COUNT(*) as total_glossaries FROM glossary_files WHERE is_active = 1");
    $total_glossaries = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Em caso de erro (ex: tabela não existe), usar valores padrão
    $total_users = 0;
    $total_lectures = 0;
    $total_certificates = 0;
    $total_glossaries = 0;
}

// Formatação para exibição
$total_users_formatted = number_format($total_users);
$total_lectures_formatted = number_format($total_lectures);
$total_certificates_formatted = number_format($total_certificates);
$total_glossaries_formatted = number_format($total_glossaries);


include __DIR__ . '/../vision/includes/head.php';
include __DIR__ . '/../vision/includes/header.php';
include __DIR__ . '/../vision/includes/sidebar.php';
?>

<div class="main-content">
    <div class="glass-hero">
        <div class="hero-content">
            <h1><i class="fas fa-cogs"></i> Painel Administrativo</h1>
            <p>Gerencie todos os aspectos da plataforma Translators101</p>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="video-card <?php echo $message_type === 'success' ? 'success-message' : 'error-message'; ?>">
        <p><i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i> <?php echo htmlspecialchars($message); ?></p>
    </div>
    <?php endif; ?>

    <div class="video-card">
        <h2><i class="fas fa-tools"></i> Gerenciamento</h2>

        <div class="quick-actions-grid action-grid-margin">
            <a href="usuarios.php" class="quick-action-card">
                <div class="quick-action-icon quick-action-icon-blue">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h3>Usuários</h3>
                <p>Gerenciar usuários e permissões</p>
            </a>

            <a href="palestras.php" class="quick-action-card">
                <div class="quick-action-icon quick-action-icon-purple">
                    <i class="fas fa-video"></i>
                </div>
                <h3>Palestras</h3>
                <p>Adicionar e editar palestras</p>
            </a>

            <a href="certificados.php" class="quick-action-card">
                <div class="quick-action-icon quick-action-icon-green">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3>Certificados</h3>
                <p>Sistema de certificação</p>
            </a>

            <a href="glossarios.php" class="quick-action-card">
                <div class="quick-action-icon stats-icon-red">
                    <i class="fas fa-book"></i>
                </div>
                <h3>Glossários</h3>
                <p>Gerenciar glossários</p>
            </a>
            
            <a href="emails.php" class="quick-action-card">
                <div class="quick-action-icon quick-action-icon-blue">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>E-mails</h3>
                <p>Sistema de comunicação</p>
            </a>

            <a href="hotmart.php" class="quick-action-card">
                <div class="quick-action-icon quick-action-icon-green">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>Hotmart</h3>
                <p>Integração de vendas</p>
            </a>

            <a href="logs.php" class="quick-action-card">
                <div class="quick-action-icon stats-icon-red">
                    <i class="fas fa-list-alt"></i>
                </div>
                <h3>Logs</h3>
                <p>Auditoria do sistema</p>
            </a>

            <a href="gerenciar_senhas.php" class="quick-action-card">
                <div class="quick-action-icon quick-action-icon-purple">
                    <i class="fas fa-key"></i>
                </div>
                <h3>Senhas</h3>
                <p>Gerenciar senhas</p>
            </a>
        </div>

        <h2><i class="fas fa-chart-bar"></i> Estatísticas</h2>
        <div class="stats-grid">
            <div class="video-card stats-card">
                <div class="stats-content">
                    <div class="stats-info">
                        <h3>Usuários Ativos</h3>
                        <span class="stats-number"><?php echo $total_users_formatted; ?></span>
                    </div>
                    <div class="stats-icon stats-icon-blue">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="video-card stats-card">
                <div class="stats-content">
                    <div class="stats-info">
                        <h3>Palestras</h3>
                        <span class="stats-number"><?php echo $total_lectures_formatted; ?></span>
                    </div>
                    <div class="stats-icon stats-icon-purple">
                        <i class="fas fa-video"></i>
                    </div>
                </div>
            </div>

            <div class="video-card stats-card">
                <div class="stats-content">
                    <div class="stats-info">
                        <h3>Certificados</h3>
                        <span class="stats-number"><?php echo $total_certificates_formatted; ?></span>
                    </div>
                    <div class="stats-icon stats-icon-green">
                        <i class="fas fa-certificate"></i>
                    </div>
                </div>
            </div>

            <div class="video-card stats-card">
                <div class="stats-content">
                    <div class="stats-info">
                        <h3>Glossários</h3>
                        <span class="stats-number"><?php echo $total_glossaries_formatted; ?></span>
                    </div>
                    <div class="stats-icon stats-icon-red">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <hr> <h2><i class="fas fa-broadcast-tower"></i> Gerenciamento de Live Stream</h2>

        <div class="embed-container-wrapper">
            <form method="POST" class="embed-form">
                <input type="hidden" name="action" value="update_embed">

                <div class="form-group">
                    <label for="embed_code">
                        <i class="fas fa-code"></i> Código de embed da live stream
                    </label>
                    <textarea
                        id="embed_code"
                        name="embed_code"
                        rows="8"
                        class="form-control"
                        placeholder="Cole aqui o código de embed do Panda Video, Hotmart Live ou outro provedor..."
                    ><?php echo htmlspecialchars($current_embed); ?></textarea>
                    <small class="form-help">
                        <i class="fas fa-info-circle"></i>
                        Cole o código iframe completo fornecido pela plataforma de streaming (Panda Video, Hotmart Live, YouTube, etc.)
                    </small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Atualizar Live Stream
                    </button>

                    <button type="button" class="btn btn-secondary" onclick="previewEmbed()">
                        <i class="fas fa-eye"></i> Visualizar
                    </button>
                </div>
            </form>

            <?php if ($current_embed): ?>
            <div class="embed-preview" id="embedPreview">
                <h3><i class="fas fa-tv"></i> Preview Atual:</h3>
                <div class="embed-container">
                    <?php echo $current_embed; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    </div>

<style>
/* Estilos existentes... */
.embed-container-wrapper {
    display: flex;
    gap: 20px;
    flex-wrap: wrap; 
    align-items: flex-start; 
}

.embed-form,
.embed-preview {
    flex: 1;
    min-width: 300px;
}

.embed-form {
    background: rgba(255, 255, 255, 0.05);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #fff;
}

.form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    font-family: 'Courier New', monospace;
    resize: vertical;
}

.form-control:focus {
    outline: none;
    border-color: #007AFF;
    box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.2);
}

.form-help {
    display: block;
    margin-top: 5px;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9em;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #007AFF, #0056CC);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056CC, #003D99);
    transform: translateY(-2px);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.embed-preview {
    background: rgba(255, 255, 255, 0.05);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.embed-preview h3 {
    margin-top: 0;
}

.embed-container {
    position: relative;
    width: 100%;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    border-radius: 8px;
}

.embed-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}

.success-message {
    background: linear-gradient(135deg, rgba(52, 199, 89, 0.2), rgba(52, 199, 89, 0.1));
    border: 1px solid rgba(52, 199, 89, 0.3);
    color: #34C759;
}

.error-message {
    background: linear-gradient(135deg, rgba(255, 59, 48, 0.2), rgba(255, 59, 48, 0.1));
    border: 1px solid rgba(255, 59, 48, 0.3);
    color: #FF3B30;
}

.success-message p, .error-message p {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Ajuste de Margem para o novo layout unificado */
.action-grid-margin {
    margin-bottom: 30px; 
}

/* 1. Layout para a grid de ações rápidas - Força 4 colunas */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr); 
    gap: 20px;
    margin-top: 20px;
}

.quick-action-card {
    text-decoration: none;
    color: inherit;
    display: block;
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: transform 0.2s ease, background 0.2s ease;
}

.quick-action-card:hover {
    /*transform: translateY(-5px);*/
    background: rgba(255, 255, 255, 0.1);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.quick-action-icon {
    font-size: 30px;
    margin-bottom: 10px;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    color: #fff;
}

.quick-action-card h3 {
    margin: 0 0 5px 0;
    font-size: 1.2em;
    font-weight: 700;
    color: #fff;
}

.quick-action-card p {
    margin: 0;
    font-size: 0.9em;
    color: rgba(255, 255, 255, 0.7);
}

/* 2. Layout para a grid de estatísticas - Força 4 colunas */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr); 
    gap: 20px;
    margin-top: 20px; 
    margin-bottom: 30px; /* Adiciona espaço abaixo das estatísticas antes do novo h2/seção */
}

/* 3. Estilo para o separador */
hr {
    border: 0;
    height: 1px;
    background-color: rgba(255, 255, 255, 0.1);
    margin: 30px 0;
}

/* Estilos para responsividade (ajustados para 4 colunas) */
@media (max-width: 1400px) {
    .quick-actions-grid,
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 1024px) {
    .quick-actions-grid,
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 600px) {
    .quick-actions-grid,
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

/* Corrigir o .video-card para não ter padding excessivo quando usado como container de grid de cards internos */
.video-card:not(.stats-card) {
    padding: 20px; 
}
</style>

<script>
function previewEmbed() {
    const embedCode = document.getElementById('embed_code').value.trim();
    let previewDiv = document.getElementById('embedPreview');

    if (!embedCode) {
        alert('Por favor, insira um código de embed primeiro.');
        return;
    }

    // Se a div de preview não existir, crie-a dentro do wrapper
    if (!previewDiv) {
        previewDiv = document.createElement('div');
        previewDiv.id = 'embedPreview';
        previewDiv.className = 'embed-preview';
        // Procura o elemento mais próximo antes de onde deveria ir e anexa
        // Neste layout unificado, vamos anexar ao final da div.video-card pai.
        document.querySelector('.video-card').appendChild(previewDiv);
    }

    previewDiv.innerHTML = `
        <h3><i class="fas fa-tv"></i> Preview:</h3>
        <div class="embed-container">
            ${embedCode}
        </div>
    `;
    
    // Rola a página até a pré-visualização (opcional, para melhor UX)
    previewDiv.scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php include __DIR__ . '/../vision/includes/footer.php'; ?>