<?php
/**
 * Script para limpar cache do token Zoom
 * Execute este script após adicionar novos escopos
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpar Cache Zoom</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            max-width: 600px;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 32px;
        }
        
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .status {
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .status.success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 5px solid #38a169;
        }
        
        .status.info {
            background: #bee3f8;
            color: #2c5282;
            border-left: 5px solid #4299e1;
        }
        
        .status.warning {
            background: #feebc8;
            color: #7c2d12;
            border-left: 5px solid #ed8936;
        }
        
        .details {
            background: #f7fafc;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: left;
        }
        
        .details h3 {
            color: #2d3748;
            margin-bottom: 15px;
        }
        
        .details ul {
            list-style: none;
            padding: 0;
        }
        
        .details li {
            padding: 8px 0;
            color: #4a5568;
            line-height: 1.6;
        }
        
        .details code {
            background: #2d3748;
            color: #e2e8f0;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px 5px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn.secondary {
            background: #4299e1;
        }
        
        .btn.secondary:hover {
            background: #3182ce;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        $cacheFile = sys_get_temp_dir() . '/zoom_token_cache.json';
        $cacheDeleted = false;
        $cacheExisted = false;
        $cacheContent = null;
        
        if (file_exists($cacheFile)) {
            $cacheExisted = true;
            
            // Tentar ler conteúdo antes de deletar
            try {
                $cacheContent = json_decode(file_get_contents($cacheFile), true);
            } catch (Exception $e) {
                $cacheContent = null;
            }
            
            // Deletar cache
            if (unlink($cacheFile)) {
                $cacheDeleted = true;
            }
        }
        
        if ($cacheDeleted) {
            echo '<div class="icon">✅</div>';
            echo '<h1>Cache Limpo com Sucesso!</h1>';
            echo '<div class="status success">';
            echo '🎉 O cache do token Zoom foi deletado com sucesso!';
            echo '</div>';
            
            if ($cacheContent) {
                echo '<div class="details">';
                echo '<h3>📋 Informações do Token Antigo:</h3>';
                echo '<ul>';
                
                if (isset($cacheContent['created_at'])) {
                    echo '<li><strong>Criado em:</strong> ' . date('d/m/Y H:i:s', $cacheContent['created_at']) . '</li>';
                }
                
                if (isset($cacheContent['expires_at'])) {
                    $expired = $cacheContent['expires_at'] < time();
                    echo '<li><strong>Expirava em:</strong> ' . date('d/m/Y H:i:s', $cacheContent['expires_at']);
                    echo $expired ? ' <span style="color: #e53e3e;">(Expirado)</span>' : ' <span style="color: #38a169;">(Ainda válido)</span>';
                    echo '</li>';
                }
                
                echo '<li><strong>Status:</strong> <span style="color: #e53e3e;">Deletado</span></li>';
                echo '</ul>';
                echo '</div>';
            }
            
            echo '<div class="details">';
            echo '<h3>📌 Próximos Passos:</h3>';
            echo '<ul>';
            echo '<li>1️⃣ Aguarde 2-3 minutos para propagação dos novos escopos</li>';
            echo '<li>2️⃣ Acesse <code>verificar_escopos.php</code> para testar</li>';
            echo '<li>3️⃣ Ou tente criar uma reunião em <code>zoom_manage.php</code></li>';
            echo '<li>4️⃣ Um novo token será gerado automaticamente com os escopos atualizados</li>';
            echo '</ul>';
            echo '</div>';
            
        } elseif ($cacheExisted) {
            echo '<div class="icon">⚠️</div>';
            echo '<h1>Erro ao Limpar Cache</h1>';
            echo '<div class="status warning">';
            echo '⚠️ O cache existe mas não pôde ser deletado. Verifique as permissões.';
            echo '</div>';
            
            echo '<div class="details">';
            echo '<h3>🔧 Solução Alternativa:</h3>';
            echo '<ul>';
            echo '<li>Execute no terminal do servidor:</li>';
            echo '<li><code>rm /tmp/zoom_token_cache.json</code></li>';
            echo '<li>Ou verifique as permissões do diretório <code>/tmp/</code></li>';
            echo '</ul>';
            echo '</div>';
            
        } else {
            echo '<div class="icon">ℹ️</div>';
            echo '<h1>Cache Não Existe</h1>';
            echo '<div class="status info">';
            echo 'ℹ️ O cache não existe. Isso significa que:';
            echo '</div>';
            
            echo '<div class="details">';
            echo '<ul>';
            echo '<li>✅ Já foi limpo anteriormente</li>';
            echo '<li>✅ Ou nunca foi criado</li>';
            echo '<li>✅ Um novo token será gerado na próxima requisição</li>';
            echo '</ul>';
            echo '</div>';
            
            echo '<div class="details">';
            echo '<h3>📌 Próximos Passos:</h3>';
            echo '<ul>';
            echo '<li>1️⃣ Certifique-se de ter adicionado <code>meeting:write:meeting:admin</code></li>';
            echo '<li>2️⃣ Aguarde 2-3 minutos para propagação</li>';
            echo '<li>3️⃣ Acesse <code>verificar_escopos.php</code> para testar</li>';
            echo '</ul>';
            echo '</div>';
        }
        ?>
        
        <div style="margin-top: 30px;">
            <a href="verificar_escopos.php" class="btn">Verificar Escopos</a>
            <a href="zoom_manage.php" class="btn secondary">Painel de Gerenciamento</a>
        </div>
        
        <div style="margin-top: 20px;">
            <form method="GET">
                <button type="submit" class="btn secondary" style="font-size: 14px; padding: 8px 16px;">
                    🔄 Recarregar Página
                </button>
            </form>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e2e8f0;">
            <p style="color: #718096; font-size: 14px;">
                <strong>Localização do cache:</strong><br>
                <code style="background: #2d3748; color: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                    <?php echo $cacheFile; ?>
                </code>
            </p>
        </div>
    </div>
</body>
</html>
