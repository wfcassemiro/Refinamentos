<?php
/**
 * Teste de Diagnóstico da Autenticação Zoom
 * Use este arquivo para diagnosticar problemas de autenticação
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once 'zoom_config.php';
require_once 'zoom_auth.php';

// Limpar cache do token
$cacheFile = sys_get_temp_dir() . '/zoom_token_cache.json';
if (file_exists($cacheFile)) {
    unlink($cacheFile);
    echo "<p style='color: orange;'>✓ Cache de token limpo</p>";
}

echo "<h1>🔍 Diagnóstico de Autenticação Zoom</h1>";
echo "<hr>";

// Teste 1: Verificar credenciais
echo "<h2>1️⃣ Verificar Credenciais</h2>";
echo "<pre>";
echo "Account ID: " . ZOOM_ACCOUNT_ID . "\n";
echo "Client ID: " . ZOOM_CLIENT_ID . "\n";
echo "Client Secret: " . substr(ZOOM_CLIENT_SECRET, 0, 10) . "..." . "\n";
echo "</pre>";

// Teste 2: Tentar obter token
echo "<h2>2️⃣ Obter Token de Acesso</h2>";

$credentials = base64_encode(ZOOM_CLIENT_ID . ':' . ZOOM_CLIENT_SECRET);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => ZOOM_OAUTH_TOKEN_URL . '?grant_type=account_credentials&account_id=' . ZOOM_ACCOUNT_ID,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Basic ' . $credentials,
        'Content-Type: application/x-www-form-urlencoded'
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_VERBOSE => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<p><strong>Status HTTP:</strong> " . $httpCode . "</p>";

if ($curlError) {
    echo "<p style='color: red;'>❌ Erro cURL: " . htmlspecialchars($curlError) . "</p>";
}

echo "<p><strong>Resposta:</strong></p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo htmlspecialchars($response);
echo "</pre>";

$data = json_decode($response, true);

if ($httpCode === 200 && isset($data['access_token'])) {
    echo "<p style='color: green; font-weight: bold;'>✅ Token obtido com sucesso!</p>";
    echo "<p><strong>Token:</strong> " . substr($data['access_token'], 0, 30) . "...</p>";
    echo "<p><strong>Expira em:</strong> " . ($data['expires_in'] ?? 'N/A') . " segundos</p>";
    
    $token = $data['access_token'];
    
    // Teste 3: Testar token obtendo usuários
    echo "<hr>";
    echo "<h2>3️⃣ Testar Token - Listar Usuários</h2>";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => ZOOM_API_BASE_URL . '/users?status=active&page_size=10',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $usersResponse = curl_exec($ch);
    $usersHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>Status HTTP:</strong> " . $usersHttpCode . "</p>";
    echo "<p><strong>Resposta:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars($usersResponse);
    echo "</pre>";
    
    $usersData = json_decode($usersResponse, true);
    
    if ($usersHttpCode === 200 && isset($usersData['users'])) {
        echo "<p style='color: green; font-weight: bold;'>✅ API funcionando! Encontrados " . count($usersData['users']) . " usuários</p>";
        
        if (!empty($usersData['users'])) {
            $firstUser = $usersData['users'][0];
            echo "<p><strong>Primeiro usuário:</strong></p>";
            echo "<ul>";
            echo "<li>ID: " . htmlspecialchars($firstUser['id']) . "</li>";
            echo "<li>Email: " . htmlspecialchars($firstUser['email']) . "</li>";
            echo "<li>Nome: " . htmlspecialchars($firstUser['first_name'] . ' ' . $firstUser['last_name']) . "</li>";
            echo "</ul>";
        }
        
        // Teste 4: Listar reuniões
        echo "<hr>";
        echo "<h2>4️⃣ Testar Listar Reuniões</h2>";
        
        $userId = $usersData['users'][0]['id'];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => ZOOM_API_BASE_URL . '/users/' . $userId . '/meetings?type=scheduled',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $meetingsResponse = curl_exec($ch);
        $meetingsHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "<p><strong>Status HTTP:</strong> " . $meetingsHttpCode . "</p>";
        
        if ($meetingsHttpCode === 200) {
            $meetingsData = json_decode($meetingsResponse, true);
            echo "<p style='color: green; font-weight: bold;'>✅ Consegue listar reuniões!</p>";
            echo "<p>Total de reuniões: " . (isset($meetingsData['total_records']) ? $meetingsData['total_records'] : 0) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao listar reuniões</p>";
            echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
            echo htmlspecialchars($meetingsResponse);
            echo "</pre>";
        }
        
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Erro ao acessar API de usuários</p>";
        
        if (isset($usersData['message'])) {
            echo "<p><strong>Mensagem de erro:</strong> " . htmlspecialchars($usersData['message']) . "</p>";
        }
    }
    
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Erro ao obter token</p>";
    
    if (isset($data['reason'])) {
        echo "<p><strong>Motivo:</strong> " . htmlspecialchars($data['reason']) . "</p>";
    }
    
    if (isset($data['error'])) {
        echo "<p><strong>Erro:</strong> " . htmlspecialchars($data['error']) . "</p>";
    }
    
    echo "<hr>";
    echo "<h2>🔧 Possíveis Soluções:</h2>";
    echo "<ol>";
    echo "<li>Verifique se o <strong>Account ID</strong> está correto</li>";
    echo "<li>Verifique se o <strong>Client ID</strong> está correto</li>";
    echo "<li>Verifique se o <strong>Client Secret</strong> está correto</li>";
    echo "<li>Acesse <a href='https://marketplace.zoom.us/develop/apps' target='_blank'>Zoom Marketplace</a> e verifique se o app Server-to-Server OAuth está <strong>ativado</strong></li>";
    echo "<li>Verifique se os <strong>escopos necessários</strong> estão adicionados (meeting:read:admin, meeting:write:admin, user:read:admin)</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<h2>📋 Resumo</h2>";
echo "<ul>";
echo "<li>URL da API: " . ZOOM_API_BASE_URL . "</li>";
echo "<li>URL OAuth: " . ZOOM_OAUTH_TOKEN_URL . "</li>";
echo "<li>Cache de token: " . $cacheFile . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='zoom_manage.php'>← Voltar para Gerenciamento</a></p>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1000px;
        margin: 20px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h1 {
        color: #333;
    }
    h2 {
        color: #666;
        margin-top: 30px;
    }
    pre {
        overflow-x: auto;
        max-height: 400px;
        overflow-y: auto;
    }
    a {
        color: #4299e1;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
