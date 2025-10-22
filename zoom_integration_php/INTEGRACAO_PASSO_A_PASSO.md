# 🔧 Guia de Integração Passo a Passo

Este guia mostra **exatamente** como integrar o Zoom no seu arquivo `index.php` existente.

---

## 📂 Opção 1: Usar o Arquivo Completo (Recomendado)

### Passo 1: Backup do Arquivo Original

```bash
cp index.php index_backup.php
```

### Passo 2: Substituir pelo Novo Arquivo

```bash
cp index_with_zoom.php index.php
```

### Passo 3: Ajustar Caminhos (se necessário)

Abra o novo `index.php` e verifique os caminhos:

```php
// Linha 5-6 - Ajuste o caminho do database.php
require_once __DIR__ . '/../../config/database.php';
require_once 'zoom_functions.php';

// Linhas 48-50 - Ajuste os caminhos dos includes
include __DIR__ . '/../vision/includes/head.php';
include __DIR__ . '/../vision/includes/header.php';
include __DIR__ . '/../vision/includes/sidebar.php';

// Última linha - Ajuste o caminho do footer
include __DIR__ . '/../vision/includes/footer.php';
```

**Ajuste para seus caminhos reais:**
```php
require_once 'config/database.php';  // Seu caminho real
require_once 'zoom_functions.php';

include 'includes/head.php';         // Seus caminhos reais
include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/footer.php';
```

---

## 📝 Opção 2: Modificar o Arquivo Existente

Se preferir manter seu `index.php` e adicionar apenas o Zoom:

### Passo 1: Adicionar o Require

**No início do arquivo, após session_start():**

```php
<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// ADICIONE ESTA LINHA:
require_once 'zoom_functions.php';
```

### Passo 2: Substituir a Lógica de Live

**ENCONTRE este bloco (linhas ~20-30):**

```php
// Buscar código de embed da live
$live_embed_code = '';
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'live_embed_code'");
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row) {
        $live_embed_code = $row['setting_value'];
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar live_embed_code: " . $e->getMessage());
}

$is_live_active = !empty(trim($live_embed_code));
```

**SUBSTITUA por:**

```php
// Buscar código de embed da live (fallback)
$live_embed_code = '';
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'live_embed_code'");
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row) {
        $live_embed_code = $row['setting_value'];
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar live_embed_code: " . $e->getMessage());
}

// NOVO: Verificar reunião do Zoom acontecendo agora
$currentZoomMeeting = getCurrentMeeting();

// Se houver reunião do Zoom ativa, usar ela ao invés do embed padrão
if ($currentZoomMeeting) {
    $is_live_active = true;
    $meeting_type = 'zoom';
} else {
    $is_live_active = !empty(trim($live_embed_code));
    $meeting_type = 'embed';
}
```

### Passo 3: Adicionar Busca de Reuniões Futuras

**APÓS a busca de palestras (upcoming_announcements), ADICIONE:**

```php
// Buscar próximas reuniões do Zoom
$upcomingZoomMeetings = getActiveMeetingsFromDatabase(5);
```

### Passo 4: Atualizar o HTML do Player

**ENCONTRE a seção do player (procure por "live-player"):**

```php
<?php if ($is_live_active): ?>
    <div class="live-player">
        <?php echo $live_embed_code; ?>
    </div>
```

**SUBSTITUA por:**

```php
<?php if ($is_live_active): ?>
    <?php if ($meeting_type === 'zoom' && $currentZoomMeeting): ?>
        <!-- NOVO: Informações da Reunião Zoom -->
        <div class="meeting-info-card">
            <h3><?php echo htmlspecialchars($currentZoomMeeting['topic']); ?></h3>
            
            <div class="meeting-info-item">
                <span>🕐</span>
                <span><?php echo date('d/m/Y \à\s H:i', strtotime($currentZoomMeeting['start_time'])); ?></span>
            </div>
            
            <div class="meeting-info-item">
                <span>⏱️</span>
                <span><?php echo $currentZoomMeeting['duration']; ?> minutos</span>
            </div>
            
            <?php if (!empty($currentZoomMeeting['agenda'])): ?>
                <div class="meeting-info-item">
                    <span>📋</span>
                    <span><?php echo htmlspecialchars($currentZoomMeeting['agenda']); ?></span>
                </div>
            <?php endif; ?>
            
            <a href="<?php echo htmlspecialchars($currentZoomMeeting['join_url']); ?>" 
               target="_blank" 
               class="join-button">
                🚀 Entrar na Reunião
            </a>
        </div>
        
        <!-- NOVO: Zoom Embed -->
        <div class="zoom-embed-container">
            <iframe 
                src="<?php echo htmlspecialchars($currentZoomMeeting['join_url']); ?>" 
                allow="microphone; camera; fullscreen"
                style="width: 100%; height: 100%; border: none;">
            </iframe>
        </div>
        
    <?php else: ?>
        <!-- Embed Code Padrão -->
        <div class="live-player">
            <?php echo $live_embed_code; ?>
        </div>
    <?php endif; ?>
```

### Passo 5: Adicionar CSS para Zoom

**DENTRO da tag `<style>`, ADICIONE:**

```css
/* Estilos para integração Zoom */
.zoom-embed-container {
    width: 100%;
    height: 600px;
    border-radius: 15px;
    overflow: hidden;
    background: #000;
    margin-top: 20px;
}

.meeting-info-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 20px;
}

.meeting-info-card h3 {
    font-size: 24px;
    margin-bottom: 10px;
}

.meeting-info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 8px 0;
    font-size: 14px;
}

.join-button {
    display: inline-block;
    background: white;
    color: #667eea;
    padding: 15px 30px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    margin-top: 15px;
    transition: all 0.3s ease;
}

.join-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.zoom-meetings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.zoom-meeting-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 15px;
    transition: all 0.3s ease;
}

.zoom-meeting-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.zoom-meeting-card h4 {
    font-size: 20px;
    margin-bottom: 15px;
}

.zoom-meeting-info {
    font-size: 14px;
    margin: 8px 0;
    opacity: 0.9;
}

@media (max-width: 768px) {
    .zoom-embed-container {
        height: 400px;
    }
    
    .zoom-meetings-grid {
        grid-template-columns: 1fr;
    }
}
```

### Passo 6: Adicionar Seção de Próximas Reuniões

**ANTES do footer, ADICIONE:**

```php
<!-- NOVO: Próximas Reuniões do Zoom -->
<?php if (!empty($upcomingZoomMeetings)): ?>
<div class="schedule-card">
    <h2>📅 Próximas Reuniões Zoom</h2>
    
    <div class="zoom-meetings-grid">
        <?php foreach ($upcomingZoomMeetings as $meeting): ?>
            <div class="zoom-meeting-card">
                <h4><?php echo htmlspecialchars($meeting['topic']); ?></h4>
                
                <div class="zoom-meeting-info">
                    <strong>📅 Data:</strong> 
                    <?php echo date('d/m/Y', strtotime($meeting['start_time'])); ?>
                </div>
                
                <div class="zoom-meeting-info">
                    <strong>🕐 Horário:</strong> 
                    <?php echo date('H:i', strtotime($meeting['start_time'])); ?>
                </div>
                
                <div class="zoom-meeting-info">
                    <strong>⏱️ Duração:</strong> 
                    <?php echo $meeting['duration']; ?> minutos
                </div>
                
                <?php if (!empty($meeting['agenda'])): ?>
                    <div class="zoom-meeting-info" style="margin-top: 10px;">
                        <?php echo htmlspecialchars($meeting['agenda']); ?>
                    </div>
                <?php endif; ?>
                
                <a href="<?php echo htmlspecialchars($meeting['join_url']); ?>" 
                   target="_blank" 
                   class="join-button" 
                   style="margin-top: 15px; display: inline-block;">
                    Acessar Reunião
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
```

---

## ✅ Verificação Final

### Checklist de Integração

- [ ] Arquivo `zoom_config.php` configurado com credenciais
- [ ] Arquivo `zoom_auth.php` no mesmo diretório
- [ ] Arquivo `zoom_functions.php` no mesmo diretório
- [ ] `install.php` executado com sucesso
- [ ] Tabela `zoom_meetings` criada no banco de dados
- [ ] `require_once 'zoom_functions.php'` adicionado ao index.php
- [ ] Variável `$currentZoomMeeting` adicionada
- [ ] Variável `$meeting_type` adicionada
- [ ] HTML do player atualizado
- [ ] CSS do Zoom adicionado
- [ ] Seção de próximas reuniões adicionada
- [ ] Caminhos de includes verificados e ajustados

### Testar a Integração

1. **Acesse zoom_manage.php:**
   ```
   http://seusite.com/zoom_manage.php
   ```

2. **Crie uma reunião de teste:**
   - Título: "Reunião de Teste"
   - Data/Hora: Agora (ou próximos minutos)
   - Duração: 30 minutos

3. **Acesse index.php:**
   ```
   http://seusite.com/index.php
   ```

4. **Verifique:**
   - ✅ Card de informações da reunião aparece
   - ✅ Botão "Entrar na Reunião" funciona
   - ✅ Iframe do Zoom carrega (ou abre em nova aba)
   - ✅ Reunião aparece na seção de "Próximas Reuniões"

---

## 🐛 Problemas Comuns

### "Call to undefined function getCurrentMeeting()"

**Causa:** `zoom_functions.php` não foi incluído

**Solução:**
```php
require_once 'zoom_functions.php';  // Adicione esta linha
```

### "Table 'zoom_meetings' doesn't exist"

**Causa:** Tabela não foi criada

**Solução:**
```
Acesse: http://seusite.com/install.php
```

### Nada aparece na página

**Causa:** Nenhuma reunião criada ou data/hora incorreta

**Solução:**
1. Acesse `zoom_manage.php`
2. Crie uma reunião com hora ATUAL
3. Recarregue `index.php`

### CSS quebrado

**Causa:** CSS não foi adicionado ou há conflito

**Solução:**
1. Verifique se o CSS foi copiado corretamente
2. Use `!important` se necessário:
```css
.meeting-info-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}
```

---

## 📱 Exemplo de Código Mínimo

Se você quer a **integração mais simples possível**:

```php
<?php
session_start();
require_once 'config/database.php';
require_once 'zoom_functions.php';

// Verificar reunião atual do Zoom
$currentZoomMeeting = getCurrentMeeting();

// Buscar próximas reuniões
$upcomingZoomMeetings = getActiveMeetingsFromDatabase(5);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reuniões Zoom</title>
</head>
<body>
    <h1>Reunião Ao Vivo</h1>
    
    <?php if ($currentZoomMeeting): ?>
        <h2><?php echo $currentZoomMeeting['topic']; ?></h2>
        <p>Horário: <?php echo date('d/m/Y H:i', strtotime($currentZoomMeeting['start_time'])); ?></p>
        <a href="<?php echo $currentZoomMeeting['join_url']; ?>" target="_blank">
            Entrar na Reunião
        </a>
    <?php else: ?>
        <p>Nenhuma reunião ao vivo no momento</p>
    <?php endif; ?>
    
    <h2>Próximas Reuniões</h2>
    
    <?php foreach ($upcomingZoomMeetings as $meeting): ?>
        <div>
            <h3><?php echo $meeting['topic']; ?></h3>
            <p><?php echo date('d/m/Y H:i', strtotime($meeting['start_time'])); ?></p>
            <a href="<?php echo $meeting['join_url']; ?>" target="_blank">Acessar</a>
        </div>
    <?php endforeach; ?>
</body>
</html>
```

Este exemplo mostra a **lógica essencial** sem design complexo.

---

## 💡 Dicas Finais

1. **Sempre faça backup** antes de modificar arquivos
2. **Teste em ambiente de desenvolvimento** primeiro
3. **Use var_dump()** para debugar variáveis:
   ```php
   var_dump($currentZoomMeeting);
   exit;
   ```
4. **Verifique os logs de erro** do PHP
5. **Use DevTools do navegador** para ver erros JavaScript/CSS

---

## 🎯 Resultado Esperado

Após a integração completa, você terá:

✅ Sistema de gerenciamento de reuniões Zoom  
✅ Criação de reuniões via painel admin  
✅ Adição de reuniões existentes  
✅ Exibição automática de reunião atual  
✅ Lista de próximas reuniões  
✅ Links de participação funcionais  
✅ Design moderno e responsivo  
✅ Sincronização com API Zoom  

**Parabéns! Sua integração está completa! 🎉**
