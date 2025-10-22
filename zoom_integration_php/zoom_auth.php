<?php
/**
 * Sistema de Autenticação OAuth 2.0 Server-to-Server do Zoom
 * 
 * IMPORTANTE - FORMATO GRANULAR DE ESCOPOS:
 * O Zoom agora requer escopos no formato granular com repetição do recurso.
 * Configure os seguintes escopos no painel da sua aplicação Zoom:
 * 
 * ESCOPOS NECESSÁRIOS:
 * - meeting:write:meeting:admin   (Criar reuniões)
 * - meeting:read:meeting:admin    (Ler reuniões)
 * - meeting:update:meeting:admin  (Atualizar reuniões)
 * - meeting:delete:meeting:admin  (Deletar reuniões)
 * - meeting:update:status:admin   (Atualizar status)
 * - user:read:user:admin          (Ler usuários - necessário para host)
 * - user:write:user:admin         (Escrever usuários - opcional)
 * 
 * NOTA: Os escopos são configurados no Zoom App Marketplace, NÃO neste arquivo.
 * Acesse: https://marketplace.zoom.us/ → Manage → Build App → [Sua App] → Scopes
 * 
 * Para verificar se os escopos estão configurados, execute: verificar_escopos.php
 */

require_once 'zoom_config.php';

/**
 * Obtém token de acesso OAuth do Zoom
 * Usa Server-to-Server OAuth (não requer interação do usuário)
 * 
 * IMPORTANTE: Os escopos (permissions) são determinados pelas configurações
 * da sua aplicação no Zoom App Marketplace, NÃO por esta função.
 */
function getZoomAccessToken($forceRefresh = false) {
    // Verificar se já existe um token válido em cache
    $cacheFile = sys_get_temp_dir() . '/zoom_token_cache.json';
    
    if (!$forceRefresh && file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        
        // Se o token ainda é válido (com margem de 5 minutos)
        if (isset($cacheData['expires_at']) && $cacheData['expires_at'] > (time() + 300)) {
            error_log("Zoom: Usando token em cache (expira em: " . date('Y-m-d H:i:s', $cacheData['expires_at']) . ")");
            return $cacheData['access_token'];
        } else {
            error_log("Zoom: Token em cache expirado, obtendo novo token");
        }
    }
    
    // Criar credenciais base64 para autenticação básica
    $credentials = base64_encode(ZOOM_CLIENT_ID . ':' . ZOOM_CLIENT_SECRET);
    
    error_log("Zoom: Solicitando novo token de acesso");
    
    // Preparar requisição
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
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        error_log("Zoom: Erro cURL ao obter token: " . $error);
        return false;
    }
    
    if ($httpCode !== 200) {
        error_log("Zoom: Erro HTTP " . $httpCode . " ao obter token. Resposta: " . $response);
        return false;
    }
    
    $data = json_decode($response, true);
    
    if (!isset($data['access_token'])) {
        error_log("Zoom: Token de acesso não encontrado na resposta. Resposta: " . json_encode($data));
        return false;
    }
    
    // Salvar token em cache
    $cacheData = [
        'access_token' => $data['access_token'],
        'expires_at' => time() + ($data['expires_in'] ?? 3600),
        'created_at' => time()
    ];
    
    file_put_contents($cacheFile, json_encode($cacheData));
    
    error_log("Zoom: Novo token obtido com sucesso (expira em: " . ($data['expires_in'] ?? 3600) . " segundos)");
    
    return $data['access_token'];
}

/**
 * Fazer requisição autenticada à API do Zoom
 * 
 * Esta função usa o token obtido por getZoomAccessToken() para fazer chamadas à API.
 * As permissões (escopos) do token são determinadas pelas configurações da aplicação
 * no Zoom App Marketplace.
 * 
 * Se você receber erro "Invalid access token, does not contain scopes", significa
 * que os escopos não estão configurados corretamente no painel Zoom.
 * 
 * Veja: ESCOPOS_ZOOM_CORRIGIDOS.md para instruções detalhadas.
 */
function zoomApiRequest($endpoint, $method = 'GET', $data = null, $retry = true) {
    $token = getZoomAccessToken();
    
    if (!$token) {
        error_log("Zoom API: Falha ao obter token para endpoint: " . $endpoint);
        return [
            'success' => false,
            'error' => 'Não foi possível obter token de autenticação'
        ];
    }
    
    $url = ZOOM_API_BASE_URL . $endpoint;
    
    error_log("Zoom API: " . $method . " " . $endpoint);
    
    $ch = curl_init();
    
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];
    
    $curlOptions = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30
    ];
    
    if ($method === 'POST') {
        $curlOptions[CURLOPT_POST] = true;
        if ($data) {
            $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
        }
    } elseif ($method === 'PATCH') {
        $curlOptions[CURLOPT_CUSTOMREQUEST] = 'PATCH';
        if ($data) {
            $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
        }
    } elseif ($method === 'DELETE') {
        $curlOptions[CURLOPT_CUSTOMREQUEST] = 'DELETE';
    }
    
    curl_setopt_array($ch, $curlOptions);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        error_log("Zoom API: Erro cURL - " . $error);
        return [
            'success' => false,
            'error' => 'Erro de conexão: ' . $error
        ];
    }
    
    $responseData = json_decode($response, true);
    
    // Se receber 401 (token inválido), tentar renovar token uma vez
    if ($httpCode === 401 && $retry) {
        error_log("Zoom API: Token inválido (401), tentando renovar token");
        
        // Forçar renovação do token
        $newToken = getZoomAccessToken(true);
        
        if ($newToken) {
            error_log("Zoom API: Token renovado, tentando requisição novamente");
            return zoomApiRequest($endpoint, $method, $data, false); // Não retry novamente
        }
    }
    
    if ($httpCode >= 200 && $httpCode < 300) {
        error_log("Zoom API: Sucesso (" . $httpCode . ")");
        return [
            'success' => true,
            'data' => $responseData,
            'http_code' => $httpCode
        ];
    } else {
        error_log("Zoom API: Erro HTTP " . $httpCode . " - " . json_encode($responseData));
        
        // Mensagem específica para erro de escopos
        $errorMessage = $responseData['message'] ?? 'Erro desconhecido (HTTP ' . $httpCode . ')';
        
        if (strpos($errorMessage, 'does not contain scopes') !== false) {
            $errorMessage .= ' - SOLUÇÃO: Configure os escopos granulares no Zoom App Marketplace. Veja ESCOPOS_ZOOM_CORRIGIDOS.md';
        }
        
        return [
            'success' => false,
            'error' => $errorMessage,
            'http_code' => $httpCode,
            'response' => $responseData
        ];
    }
}

/**
 * Validar webhook do Zoom (para eventos futuros)
 */
function validateZoomWebhook($payload, $signature) {
    $message = 'v0:' . $_SERVER['HTTP_X_ZM_REQUEST_TIMESTAMP'] . ':' . $payload;
    $hash = hash_hmac('sha256', $message, ZOOM_SECRET_TOKEN);
    $expectedSignature = 'v0=' . $hash;
    
    return hash_equals($expectedSignature, $signature);
}

?>