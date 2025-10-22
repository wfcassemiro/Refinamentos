# 📚 Exemplos de Uso da API Zoom

Exemplos práticos de como usar as funções da integração Zoom.

---

## 🎯 Índice de Exemplos

1. [Criar Reunião](#1-criar-reunião)
2. [Adicionar Reunião Existente](#2-adicionar-reunião-existente)
3. [Listar Reuniões](#3-listar-reuniões)
4. [Obter Reunião Específica](#4-obter-reunião-específica)
5. [Atualizar Reunião](#5-atualizar-reunião)
6. [Deletar Reunião](#6-deletar-reunião)
7. [Obter Reunião Atual](#7-obter-reunião-atual)
8. [Buscar Reuniões do Banco](#8-buscar-reuniões-do-banco)
9. [Webhook Handler](#9-webhook-handler)
10. [Uso em AJAX](#10-uso-em-ajax)

---

## 1. Criar Reunião

### Exemplo Básico

```php
<?php
require_once 'zoom_functions.php';

$result = createZoomMeeting(
    'Palestra sobre Marketing Digital',    // Título
    '2025-05-20 14:00:00',                 // Data/Hora
    90,                                     // Duração (minutos)
    'Aprenda estratégias de marketing',    // Agenda
    'America/Sao_Paulo'                    // Timezone
);

if ($result['success']) {
    $meeting = $result['meeting'];
    
    echo "✅ Reunião criada com sucesso!\n";
    echo "ID: " . $meeting['id'] . "\n";
    echo "Link: " . $meeting['join_url'] . "\n";
    echo "Senha: " . $meeting['password'] . "\n";
} else {
    echo "❌ Erro: " . $result['error'];
}
?>
```

### Exemplo com Validação

```php
<?php
require_once 'zoom_functions.php';

function criarReuniao($dados) {
    // Validar dados
    if (empty($dados['titulo'])) {
        return ['success' => false, 'error' => 'Título é obrigatório'];
    }
    
    if (strtotime($dados['data_hora']) < time()) {
        return ['success' => false, 'error' => 'Data/hora deve ser futura'];
    }
    
    if ($dados['duracao'] < 15 || $dados['duracao'] > 480) {
        return ['success' => false, 'error' => 'Duração deve estar entre 15 e 480 minutos'];
    }
    
    // Criar reunião
    $result = createZoomMeeting(
        $dados['titulo'],
        $dados['data_hora'],
        $dados['duracao'],
        $dados['agenda'] ?? '',
        $dados['timezone'] ?? 'America/Sao_Paulo'
    );
    
    return $result;
}

// Uso
$dados = [
    'titulo' => 'Workshop de PHP',
    'data_hora' => '2025-05-25 10:00:00',
    'duracao' => 120,
    'agenda' => 'Aprenda PHP do zero',
    'timezone' => 'America/Sao_Paulo'
];

$resultado = criarReuniao($dados);

if ($resultado['success']) {
    echo "Reunião criada: " . $resultado['meeting']['join_url'];
} else {
    echo "Erro: " . $resultado['error'];
}
?>
```

### Exemplo com Formulário POST

```php
<?php
require_once 'zoom_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = createZoomMeeting(
        $_POST['titulo'],
        $_POST['data_hora'],
        (int)$_POST['duracao'],
        $_POST['agenda'] ?? '',
        $_POST['timezone'] ?? 'America/Sao_Paulo'
    );
    
    if ($result['success']) {
        $_SESSION['mensagem'] = 'Reunião criada com sucesso!';
        $_SESSION['meeting_url'] = $result['meeting']['join_url'];
        header('Location: sucesso.php');
        exit;
    } else {
        $_SESSION['erro'] = $result['error'];
        header('Location: criar_reuniao.php');
        exit;
    }
}
?>
```

---

## 2. Adicionar Reunião Existente

### Por ID

```php
<?php
require_once 'zoom_functions.php';

$meetingId = '1234567890';

$result = addExistingMeeting($meetingId);

if ($result['success']) {
    echo "✅ Reunião adicionada!\n";
    echo "Título: " . $result['meeting']['topic'] . "\n";
    echo "Data: " . $result['meeting']['start_time'] . "\n";
} else {
    echo "❌ Erro: " . $result['error'];
}
?>
```

### Por URL

```php
<?php
require_once 'zoom_functions.php';

$meetingUrl = 'https://zoom.us/j/1234567890?pwd=abcdefgh';

$result = addExistingMeeting($meetingUrl);

if ($result['success']) {
    echo "Reunião adicionada com sucesso!";
} else {
    echo "Erro: " . $result['error'];
}
?>
```

### Validar antes de Adicionar

```php
<?php
require_once 'zoom_functions.php';

function adicionarReuniaoPorInput($input) {
    // Extrair ID
    $meetingId = extractMeetingIdFromUrl($input);
    
    if (!$meetingId) {
        return [
            'success' => false,
            'error' => 'ID ou URL inválido. Use o formato: 1234567890 ou https://zoom.us/j/1234567890'
        ];
    }
    
    // Verificar se já existe no BD
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM zoom_meetings WHERE meeting_id = ?");
    $stmt->execute([$meetingId]);
    
    if ($stmt->fetch()) {
        return [
            'success' => false,
            'error' => 'Esta reunião já está cadastrada'
        ];
    }
    
    // Adicionar
    return addExistingMeeting($meetingId);
}

// Uso
$resultado = adicionarReuniaoPorInput($_POST['meeting_input']);
?>
```

---

## 3. Listar Reuniões

### Listar Todas

```php
<?php
require_once 'zoom_functions.php';

$result = listZoomMeetings('scheduled');

if ($result['success']) {
    $meetings = $result['data']['meetings'];
    
    foreach ($meetings as $meeting) {
        echo "ID: " . $meeting['id'] . "\n";
        echo "Título: " . $meeting['topic'] . "\n";
        echo "Data: " . $meeting['start_time'] . "\n";
        echo "---\n";
    }
} else {
    echo "Erro: " . $result['error'];
}
?>
```

### Filtrar por Data

```php
<?php
require_once 'zoom_functions.php';

function listarReunioesPorPeriodo($dataInicio, $dataFim) {
    $result = listZoomMeetings('scheduled');
    
    if (!$result['success']) {
        return [];
    }
    
    $meetings = $result['data']['meetings'];
    $filtradas = [];
    
    foreach ($meetings as $meeting) {
        $meetingDate = strtotime($meeting['start_time']);
        
        if ($meetingDate >= strtotime($dataInicio) && 
            $meetingDate <= strtotime($dataFim)) {
            $filtradas[] = $meeting;
        }
    }
    
    return $filtradas;
}

// Uso: listar reuniões de maio
$reunioes = listarReunioesPorPeriodo('2025-05-01', '2025-05-31');

foreach ($reunioes as $reuniao) {
    echo $reuniao['topic'] . " - " . $reuniao['start_time'] . "\n";
}
?>
```

### Exibir em HTML

```php
<?php
require_once 'zoom_functions.php';

$result = listZoomMeetings('scheduled');

if ($result['success']):
    $meetings = $result['data']['meetings'];
?>
    <div class="meetings-list">
        <h2>Reuniões Agendadas (<?php echo count($meetings); ?>)</h2>
        
        <?php foreach ($meetings as $meeting): ?>
            <div class="meeting-card">
                <h3><?php echo htmlspecialchars($meeting['topic']); ?></h3>
                <p>
                    <strong>Data:</strong> 
                    <?php echo date('d/m/Y H:i', strtotime($meeting['start_time'])); ?>
                </p>
                <p>
                    <strong>Duração:</strong> 
                    <?php echo $meeting['duration']; ?> minutos
                </p>
                <a href="<?php echo htmlspecialchars($meeting['join_url']); ?>" 
                   target="_blank" 
                   class="btn">
                    Entrar na Reunião
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
```

---

## 4. Obter Reunião Específica

### Por ID

```php
<?php
require_once 'zoom_functions.php';

$meetingId = '1234567890';

$result = getZoomMeeting($meetingId);

if ($result['success']) {
    $meeting = $result['meeting'];
    
    echo "Título: " . $meeting['topic'] . "\n";
    echo "Status: " . $meeting['status'] . "\n";
    echo "Participantes: " . $meeting['settings']['approval_type'] . "\n";
} else {
    echo "Erro: " . $result['error'];
}
?>
```

### Verificar se Reunião Existe

```php
<?php
require_once 'zoom_functions.php';

function reuniaoExiste($meetingId) {
    $result = getZoomMeeting($meetingId);
    return $result['success'];
}

if (reuniaoExiste('1234567890')) {
    echo "Reunião existe no Zoom";
} else {
    echo "Reunião não encontrada";
}
?>
```

### Sincronizar com Banco de Dados

```php
<?php
require_once 'zoom_functions.php';

function sincronizarReuniao($meetingId) {
    // Buscar do Zoom
    $result = getZoomMeeting($meetingId);
    
    if (!$result['success']) {
        return [
            'success' => false,
            'error' => 'Reunião não encontrada no Zoom'
        ];
    }
    
    // Salvar no BD (já feito dentro de getZoomMeeting)
    
    return [
        'success' => true,
        'message' => 'Reunião sincronizada com sucesso',
        'meeting' => $result['meeting']
    ];
}

// Uso
$resultado = sincronizarReuniao('1234567890');
?>
```

---

## 5. Atualizar Reunião

### Atualizar Título

```php
<?php
require_once 'zoom_functions.php';

$meetingId = '1234567890';

$result = updateZoomMeeting($meetingId, [
    'topic' => 'Novo Título da Reunião'
]);

if ($result['success']) {
    echo "Título atualizado!";
} else {
    echo "Erro: " . $result['error'];
}
?>
```

### Atualizar Data/Hora

```php
<?php
require_once 'zoom_functions.php';

$result = updateZoomMeeting('1234567890', [
    'start_time' => '2025-05-30T15:00:00',
    'duration' => 60
]);
?>
```

### Atualizar Configurações

```php
<?php
require_once 'zoom_functions.php';

$result = updateZoomMeeting('1234567890', [
    'settings' => [
        'host_video' => false,
        'participant_video' => false,
        'waiting_room' => true,
        'mute_upon_entry' => true
    ]
]);
?>
```

### Atualizar Múltiplos Campos

```php
<?php
require_once 'zoom_functions.php';

$dados = [
    'topic' => 'Workshop Avançado de PHP',
    'agenda' => 'Tópicos avançados de programação PHP',
    'start_time' => '2025-06-01T14:00:00',
    'duration' => 120,
    'settings' => [
        'host_video' => true,
        'participant_video' => true,
        'waiting_room' => false,
        'join_before_host' => true
    ]
];

$result = updateZoomMeeting('1234567890', $dados);

if ($result['success']) {
    echo "Reunião atualizada completamente!";
}
?>
```

---

## 6. Deletar Reunião

### Deletar Simples

```php
<?php
require_once 'zoom_functions.php';

$result = deleteZoomMeeting('1234567890');

if ($result['success']) {
    echo "Reunião deletada!";
} else {
    echo "Erro: " . $result['error'];
}
?>
```

### Deletar com Confirmação

```php
<?php
require_once 'zoom_functions.php';

function deletarReuniao($meetingId, $confirmacao = false) {
    if (!$confirmacao) {
        return [
            'success' => false,
            'error' => 'Confirmação necessária para deletar reunião'
        ];
    }
    
    // Verificar se tem participantes agendados
    $result = getZoomMeeting($meetingId);
    
    if ($result['success']) {
        $meeting = $result['meeting'];
        
        // Se começar em menos de 1 hora, pedir confirmação adicional
        $startTime = strtotime($meeting['start_time']);
        if ($startTime - time() < 3600) {
            return [
                'success' => false,
                'error' => 'Reunião começa em menos de 1 hora. Tem certeza?',
                'requires_double_confirmation' => true
            ];
        }
    }
    
    return deleteZoomMeeting($meetingId);
}

// Uso
if ($_POST['confirmar'] === 'sim') {
    $resultado = deletarReuniao($_POST['meeting_id'], true);
}
?>
```

### Deletar Múltiplas

```php
<?php
require_once 'zoom_functions.php';

function deletarMultiplasReunioes($meetingIds) {
    $resultados = [
        'sucesso' => [],
        'falha' => []
    ];
    
    foreach ($meetingIds as $id) {
        $result = deleteZoomMeeting($id);
        
        if ($result['success']) {
            $resultados['sucesso'][] = $id;
        } else {
            $resultados['falha'][] = [
                'id' => $id,
                'erro' => $result['error']
            ];
        }
    }
    
    return $resultados;
}

// Uso
$ids = ['1111111111', '2222222222', '3333333333'];
$resultados = deletarMultiplasReunioes($ids);

echo "Deletadas: " . count($resultados['sucesso']) . "\n";
echo "Falhas: " . count($resultados['falha']) . "\n";
?>
```

---

## 7. Obter Reunião Atual

### Reunião Acontecendo Agora

```php
<?php
require_once 'zoom_functions.php';

$currentMeeting = getCurrentMeeting();

if ($currentMeeting) {
    echo "🔴 AO VIVO: " . $currentMeeting['topic'] . "\n";
    echo "Link: " . $currentMeeting['join_url'] . "\n";
} else {
    echo "Nenhuma reunião ao vivo no momento";
}
?>
```

### Exibir em Página

```php
<?php
require_once 'zoom_functions.php';

$currentMeeting = getCurrentMeeting();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Live</title>
</head>
<body>
    <?php if ($currentMeeting): ?>
        <div class="live-banner">
            <h1>🔴 AO VIVO AGORA</h1>
            <h2><?php echo htmlspecialchars($currentMeeting['topic']); ?></h2>
            <p>
                Iniciou: <?php echo date('H:i', strtotime($currentMeeting['start_time'])); ?>
            </p>
            <a href="<?php echo htmlspecialchars($currentMeeting['join_url']); ?>" 
               target="_blank" 
               class="join-btn">
                ENTRAR AGORA
            </a>
        </div>
    <?php else: ?>
        <div class="offline">
            <p>Nenhuma transmissão ao vivo</p>
        </div>
    <?php endif; ?>
</body>
</html>
```

### Verificar Status com Margem

```php
<?php
require_once 'zoom_functions.php';

function getReuniaoComMargem($margemMinutos = 10) {
    $pdo = getDbConnection();
    
    // Buscar reunião que começou há até X minutos ou vai começar em até X minutos
    $stmt = $pdo->prepare("
        SELECT * FROM zoom_meetings 
        WHERE is_active = 1 
        AND start_time <= DATE_ADD(NOW(), INTERVAL :margem_futura MINUTE)
        AND DATE_ADD(start_time, INTERVAL (duration + :margem_passada) MINUTE) >= NOW()
        ORDER BY start_time DESC 
        LIMIT 1
    ");
    
    $stmt->execute([
        ':margem_futura' => $margemMinutos,
        ':margem_passada' => $margemMinutos
    ]);
    
    return $stmt->fetch();
}

// Uso: pegar reunião 10 minutos antes e depois
$meeting = getReuniaoComMargem(10);
?>
```

---

## 8. Buscar Reuniões do Banco

### Próximas 5 Reuniões

```php
<?php
require_once 'zoom_functions.php';

$meetings = getActiveMeetingsFromDatabase(5);

foreach ($meetings as $meeting) {
    echo $meeting['topic'] . " - ";
    echo date('d/m/Y H:i', strtotime($meeting['start_time'])) . "\n";
}
?>
```

### Reuniões de Hoje

```php
<?php
require_once 'zoom_config.php';

$pdo = getDbConnection();

$stmt = $pdo->prepare("
    SELECT * FROM zoom_meetings 
    WHERE is_active = 1 
    AND DATE(start_time) = CURDATE()
    ORDER BY start_time ASC
");

$stmt->execute();
$reunioesHoje = $stmt->fetchAll();

foreach ($reunioesHoje as $reuniao) {
    echo $reuniao['topic'] . " às " . date('H:i', strtotime($reuniao['start_time'])) . "\n";
}
?>
```

### Reuniões da Semana

```php
<?php
require_once 'zoom_config.php';

function getReunioesDaSemana() {
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("
        SELECT * FROM zoom_meetings 
        WHERE is_active = 1 
        AND start_time BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ORDER BY start_time ASC
    ");
    
    $stmt->execute();
    return $stmt->fetchAll();
}

$reunioes = getReunioesDaSemana();

echo "Reuniões desta semana: " . count($reunioes) . "\n";
?>
```

### Buscar por Título

```php
<?php
require_once 'zoom_config.php';

function buscarReuniaoPorTitulo($termo) {
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("
        SELECT * FROM zoom_meetings 
        WHERE is_active = 1 
        AND topic LIKE :termo
        ORDER BY start_time ASC
    ");
    
    $stmt->execute([':termo' => '%' . $termo . '%']);
    return $stmt->fetchAll();
}

// Uso
$resultados = buscarReuniaoPorTitulo('Workshop');

foreach ($resultados as $reuniao) {
    echo $reuniao['topic'] . "\n";
}
?>
```

---

## 9. Webhook Handler

### Receber Eventos do Zoom

```php
<?php
/**
 * webhook_zoom.php
 * Handler para receber eventos do Zoom
 */

require_once 'zoom_auth.php';
require_once 'zoom_functions.php';

// Obter payload
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_ZM_SIGNATURE'] ?? '';

// Validar webhook
if (!validateZoomWebhook($payload, $signature)) {
    http_response_code(401);
    exit('Unauthorized');
}

// Processar evento
$event = json_decode($payload, true);

switch ($event['event']) {
    case 'meeting.started':
        // Reunião iniciou
        $meetingId = $event['payload']['object']['id'];
        
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            UPDATE zoom_meetings 
            SET status = 'started' 
            WHERE meeting_id = ?
        ");
        $stmt->execute([$meetingId]);
        
        break;
        
    case 'meeting.ended':
        // Reunião terminou
        $meetingId = $event['payload']['object']['id'];
        
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            UPDATE zoom_meetings 
            SET status = 'ended' 
            WHERE meeting_id = ?
        ");
        $stmt->execute([$meetingId]);
        
        break;
        
    case 'meeting.participant_joined':
        // Participante entrou
        // Salvar log se necessário
        break;
}

http_response_code(200);
exit('OK');
?>
```

---

## 10. Uso em AJAX

### Criar Reunião via AJAX

```html
<!-- Formulário -->
<form id="createMeetingForm">
    <input type="text" name="titulo" placeholder="Título" required>
    <input type="datetime-local" name="data_hora" required>
    <input type="number" name="duracao" value="60" min="15" max="480" required>
    <textarea name="agenda" placeholder="Descrição"></textarea>
    <button type="submit">Criar Reunião</button>
</form>

<div id="resultado"></div>

<script>
document.getElementById('createMeetingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api_criar_reuniao.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('resultado').innerHTML = `
                <div class="success">
                    <h3>Reunião criada!</h3>
                    <p>ID: ${result.meeting.id}</p>
                    <p>Link: <a href="${result.meeting.join_url}" target="_blank">
                        ${result.meeting.join_url}
                    </a></p>
                </div>
            `;
            this.reset();
        } else {
            document.getElementById('resultado').innerHTML = `
                <div class="error">Erro: ${result.error}</div>
            `;
        }
    } catch (error) {
        console.error('Erro:', error);
    }
});
</script>
```

### API Endpoint

```php
<?php
/**
 * api_criar_reuniao.php
 */

require_once 'zoom_functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'error' => 'Método não permitido']));
}

$result = createZoomMeeting(
    $_POST['titulo'],
    $_POST['data_hora'],
    (int)$_POST['duracao'],
    $_POST['agenda'] ?? ''
);

echo json_encode($result);
?>
```

---

## 💡 Dicas Finais

### Performance

```php
// Cache de token já implementado em zoom_auth.php
// Token é cacheado por ~1 hora automaticamente
```

### Error Handling

```php
try {
    $result = createZoomMeeting(...);
    
    if (!$result['success']) {
        throw new Exception($result['error']);
    }
    
    // Sucesso
} catch (Exception $e) {
    error_log("Erro Zoom: " . $e->getMessage());
    // Exibir mensagem amigável para usuário
}
```

### Logs

```php
// Adicionar logs detalhados
error_log("Criando reunião: " . json_encode($dados));
error_log("Resultado: " . json_encode($result));
```

---

**Estes exemplos cobrem 90% dos casos de uso da integração Zoom! 🚀**
