<?php
/**
 * Script para verificar escopos e permissões do Zoom
 * Execute este script após configurar os escopos no painel Zoom
 */

require_once 'zoom_config.php';
require_once 'zoom_auth.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Escopos Zoom</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #2d3748;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .status-box {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 5px solid;
        }
        
        .status-success {
            background: #c6f6d5;
            border-color: #38a169;
            color: #22543d;
        }
        
        .status-error {
            background: #fed7d7;
            border-color: #e53e3e;
            color: #742a2a;
        }
        
        .status-warning {
            background: #feebc8;
            border-color: #ed8936;
            color: #7c2d12;
        }
        
        .status-info {
            background: #bee3f8;
            border-color: #4299e1;
            color: #2c5282;
        }
        
        .status-box h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .status-box p {
            line-height: 1.6;
            margin-bottom: 8px;
        }
        
        .test-section {
            background: #f7fafc;
            padding: 25px;
            border-radius: 12px;
            margin-top: 20px;
        }
        
        .test-section h2 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 22px;
        }
        
        .test-item {
            padding: 15px;
            background: white;
            border-radius: 8px;
            margin-bottom: 12px;
            border-left: 4px solid #e2e8f0;
        }
        
        .test-item.success {
            border-color: #38a169;
        }
        
        .test-item.error {
            border-color: #e53e3e;
        }
        
        .test-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .test-desc {
            color: #718096;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .test-result {
            font-size: 14px;
            font-weight: 600;
        }
        
        .test-result.success {
            color: #38a169;
        }
        
        .test-result.error {
            color: #e53e3e;
        }
        
        .scope-list {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .scope-list h3 {
            color: #2d3748;
            margin-bottom: 12px;
            font-size: 18px;
        }
        
        .scope-item {
            padding: 10px 15px;
            background: #f7fafc;
            border-radius: 6px;
            margin-bottom: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #4a5568;
            border-left: 3px solid #667eea;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificação de Escopos Zoom</h1>
        <p class="subtitle">Diagnóstico automático de configuração e permissões</p>
        
        <?php
        // Teste 1: Verificar credenciais
        echo '<div class="test-section">';
        echo '<h2>📋 1. Verificação de Credenciais</h2>';
        
        $credentialsOk = true;
        
        if (empty(ZOOM_ACCOUNT_ID)) {
            echo '<div class="status-box status-error">';
            echo '<h3>❌ Account ID não configurado</h3>';
            echo '<p>Configure ZOOM_ACCOUNT_ID no arquivo zoom_config.php</p>';
            echo '</div>';
            $credentialsOk = false;
        }
        
        if (empty(ZOOM_CLIENT_ID)) {
            echo '<div class="status-box status-error">';
            echo '<h3>❌ Client ID não configurado</h3>';
            echo '<p>Configure ZOOM_CLIENT_ID no arquivo zoom_config.php</p>';
            echo '</div>';
            $credentialsOk = false;
        }
        
        if (empty(ZOOM_CLIENT_SECRET)) {
            echo '<div class="status-box status-error">';
            echo '<h3>❌ Client Secret não configurado</h3>';
            echo '<p>Configure ZOOM_CLIENT_SECRET no arquivo zoom_config.php</p>';
            echo '</div>';
            $credentialsOk = false;
        }
        
        if ($credentialsOk) {
            echo '<div class="status-box status-success">';
            echo '<h3>✅ Credenciais configuradas</h3>';
            echo '<p><strong>Account ID:</strong> ' . substr(ZOOM_ACCOUNT_ID, 0, 15) . '...</p>';
            echo '<p><strong>Client ID:</strong> ' . substr(ZOOM_CLIENT_ID, 0, 15) . '...</p>';
            echo '</div>';
        }
        
        echo '</div>';
        
        // Teste 2: Obter Token
        echo '<div class="test-section">';
        echo '<h2>🔐 2. Autenticação OAuth</h2>';
        
        $token = getZoomAccessToken(true); // Forçar novo token
        
        if ($token) {
            echo '<div class="status-box status-success">';
            echo '<h3>✅ Token obtido com sucesso</h3>';
            echo '<p>A autenticação OAuth está funcionando corretamente.</p>';
            echo '<p><strong>Token:</strong> ' . substr($token, 0, 20) . '...</p>';
            echo '</div>';
            
            $tokenOk = true;
        } else {
            echo '<div class="status-box status-error">';
            echo '<h3>❌ Falha ao obter token</h3>';
            echo '<p>Verifique suas credenciais no zoom_config.php</p>';
            echo '<p>Verifique os logs de erro do PHP para mais detalhes.</p>';
            echo '</div>';
            
            $tokenOk = false;
        }
        
        echo '</div>';
        
        // Teste 3: Testar Escopos
        if ($tokenOk) {
            echo '<div class="test-section">';
            echo '<h2>🎯 3. Teste de Escopos (Permissões)</h2>';
            
            $scopeTests = [
                [
                    'name' => 'Listar Usuários',
                    'scope' => 'user:read:list_users:admin',
                    'endpoint' => '/users?status=active&page_size=1',
                    'method' => 'GET'
                ],
                [
                    'name' => 'Obter Informações do Usuário',
                    'scope' => 'user:read:user:admin',
                    'endpoint' => '/users/me',
                    'method' => 'GET'
                ],
                [
                    'name' => 'Listar Reuniões',
                    'scope' => 'meeting:read:list_meetings:admin',
                    'endpoint' => '/users/me/meetings?type=scheduled&page_size=1',
                    'method' => 'GET'
                ]
            ];
            
            $allScopesOk = true;
            
            foreach ($scopeTests as $test) {
                echo '<div class="test-item';
                
                $result = zoomApiRequest($test['endpoint'], $test['method']);
                
                if ($result['success']) {
                    echo ' success">';
                    echo '<div class="test-title">✅ ' . htmlspecialchars($test['name']) . '</div>';
                    echo '<div class="test-desc">Escopo necessário: <code>' . htmlspecialchars($test['scope']) . '</code></div>';
                    echo '<div class="test-result success">PASSOU - Escopo configurado corretamente</div>';
                } else {
                    echo ' error">';
                    echo '<div class="test-title">❌ ' . htmlspecialchars($test['name']) . '</div>';
                    echo '<div class="test-desc">Escopo necessário: <code>' . htmlspecialchars($test['scope']) . '</code></div>';
                    echo '<div class="test-result error">FALHOU - ' . htmlspecialchars($result['error']) . '</div>';
                    $allScopesOk = false;
                }
                
                echo '</div>';
            }
            
            echo '</div>';
            
            // Teste 4: Teste de Criação (apenas verificar se endpoint responde)
            echo '<div class="test-section">';
            echo '<h2>📝 4. Escopos de Escrita</h2>';
            
            echo '<div class="status-box status-info">';
            echo '<h3>ℹ️ Teste Manual Recomendado</h3>';
            echo '<p>Para testar os escopos de escrita (criar/atualizar/deletar reuniões), recomendamos usar o painel de gerenciamento.</p>';
            echo '<p><strong>Escopos de escrita necessários:</strong></p>';
            echo '<ul style="margin-left: 20px; margin-top: 10px;">';
            echo '<li><code>meeting:write:meeting:admin</code> - Criar reuniões</li>';
            echo '<li><code>meeting:update:meeting:admin</code> - Atualizar reuniões</li>';
            echo '<li><code>meeting:delete:meeting:admin</code> - Deletar reuniões</li>';
            echo '</ul>';
            echo '<a href="zoom_manage.php" class="btn">Testar no Painel de Gerenciamento</a>';
            echo '</div>';
            
            echo '</div>';
            
            // Resumo Final
            echo '<div class="test-section">';
            echo '<h2>📊 Resumo da Verificação</h2>';
            
            if ($allScopesOk) {
                echo '<div class="status-box status-success">';
                echo '<h3>🎉 Tudo Configurado Corretamente!</h3>';
                echo '<p>Todos os escopos de leitura estão funcionando. Você pode começar a usar a integração Zoom.</p>';
                echo '<p>Teste criar uma reunião no painel de gerenciamento para confirmar os escopos de escrita.</p>';
                echo '</div>';
            } else {
                echo '<div class="status-box status-error">';
                echo '<h3>⚠️ Ação Necessária</h3>';
                echo '<p>Alguns escopos não estão configurados corretamente. Siga as instruções abaixo:</p>';
                echo '</div>';
            }
            
            // Lista de escopos necessários
            echo '<div class="scope-list">';
            echo '<h3>📋 Escopos que DEVEM estar configurados no Zoom App:</h3>';
            
            $requiredScopes = [
                'meeting:write:meeting:admin',
                'meeting:read:meeting:admin',
                'meeting:update:meeting:admin',
                'meeting:delete:meeting:admin',
                'meeting:update:status:admin',
                'user:read:user:admin',
                'user:write:user:admin'
            ];
            
            foreach ($requiredScopes as $scope) {
                echo '<div class="scope-item">✓ ' . htmlspecialchars($scope) . '</div>';
            }
            
            echo '</div>';
            
            // Instruções
            if (!$allScopesOk) {
                echo '<div class="status-box status-warning">';
                echo '<h3>🛠️ Como Adicionar os Escopos</h3>';
                echo '<ol style="margin-left: 20px; line-height: 1.8;">';
                echo '<li>Acesse <a href="https://marketplace.zoom.us/" target="_blank">Zoom App Marketplace</a></li>';
                echo '<li>Vá em <strong>Manage → Build App</strong></li>';
                echo '<li>Selecione sua aplicação Server-to-Server OAuth</li>';
                echo '<li>Clique em <strong>Scopes</strong> no menu lateral</li>';
                echo '<li>Adicione TODOS os escopos listados acima</li>';
                echo '<li>Clique em <strong>Save</strong> e aguarde 2-5 minutos</li>';
                echo '<li>Volte aqui e recarregue esta página</li>';
                echo '</ol>';
                echo '<p style="margin-top: 15px;"><strong>Documentação completa:</strong> Veja o arquivo ESCOPOS_ZOOM_CORRIGIDOS.md</p>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        
        // Comandos úteis
        echo '<div class="test-section">';
        echo '<h2>🔧 Comandos Úteis</h2>';
        
        echo '<div class="status-box status-info">';
        echo '<h3>💡 Limpar Cache de Token</h3>';
        echo '<p>Se você alterou os escopos, execute este comando para limpar o cache:</p>';
        echo '<div class="code-block">rm /tmp/zoom_token_cache.json</div>';
        echo '</div>';
        
        echo '<div style="text-align: center; margin-top: 30px;">';
        echo '<a href="zoom_manage.php" class="btn">Ir para Painel de Gerenciamento</a>';
        echo '<a href="?refresh=1" class="btn" style="margin-left: 10px;">Recarregar Verificação</a>';
        echo '</div>';
        
        echo '</div>';
        ?>
    </div>
</body>
</html>