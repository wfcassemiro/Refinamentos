# 🎥 Integração Zoom para PHP

Integração completa do Zoom para sites PHP, permitindo criar, gerenciar e exibir reuniões diretamente no seu site.

## 📋 Índice

1. [Requisitos](#requisitos)
2. [Instalação](#instalação)
3. [Configuração](#configuração)
4. [Estrutura de Arquivos](#estrutura-de-arquivos)
5. [Como Usar](#como-usar)
6. [Funcionalidades](#funcionalidades)
7. [Troubleshooting](#troubleshooting)

---

## ✅ Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Extensões PHP necessárias:
  - `curl`
  - `json`
  - `pdo_mysql`
- Conta Zoom com aplicativo Server-to-Server OAuth configurado

---

## 🚀 Instalação

### Passo 1: Copiar Arquivos

Copie todos os arquivos desta pasta para o diretório do seu site:

```
/seu-site/
├── zoom_config.php
├── zoom_auth.php
├── zoom_functions.php
├── zoom_manage.php
├── index_with_zoom.php
├── install.php
└── README.md
```

### Passo 2: Configurar Credenciais

Edite o arquivo `zoom_config.php` e atualize as seguintes informações:

#### Credenciais do Zoom (já configuradas):
```php
define('ZOOM_ACCOUNT_ID', 'KiJeWwARQbGPJ1uhAWf-dw');
define('ZOOM_CLIENT_ID', '8dSp8Ud3Q7ebqHV7L3Wrw');
define('ZOOM_CLIENT_SECRET', 'OMqOfpuOywq8mYxYaLHrUYwwRCk9CIgK');
define('ZOOM_SECRET_TOKEN', 'Kim4ExnFS7mFd61Grrr3UQ');
```

#### Configurações do Banco de Dados:
```php
define('DB_HOST', 'localhost');           // Host do MySQL
define('DB_NAME', 'seu_banco_de_dados');  // Nome do banco
define('DB_USER', 'seu_usuario');         // Usuário do MySQL
define('DB_PASS', 'sua_senha');           // Senha do MySQL
```

### Passo 3: Executar Instalação

Acesse via navegador:
```
http://seusite.com/install.php
```

Este script irá:
- ✅ Verificar as credenciais do Zoom
- ✅ Testar conexão com banco de dados
- ✅ Criar a tabela `zoom_meetings`
- ✅ Testar autenticação com a API do Zoom
- ✅ Obter informações da conta Zoom

**IMPORTANTE:** Delete o arquivo `install.php` após a instalação por segurança!

---

## 🗂️ Estrutura de Arquivos

### `zoom_config.php`
Configurações principais:
- Credenciais do Zoom
- Configurações do banco de dados
- Função de conexão com BD
- Função para criar tabela

### `zoom_auth.php`
Sistema de autenticação:
- Obtenção de token OAuth 2.0 Server-to-Server
- Cache de token (evita requisições desnecessárias)
- Função genérica para requisições à API Zoom
- Validação de webhooks (para uso futuro)

### `zoom_functions.php`
Funções principais:
- `createZoomMeeting()` - Criar nova reunião
- `getZoomMeeting()` - Obter detalhes de reunião
- `listZoomMeetings()` - Listar todas as reuniões
- `deleteZoomMeeting()` - Deletar reunião
- `updateZoomMeeting()` - Atualizar reunião
- `addExistingMeeting()` - Adicionar reunião existente
- `getCurrentMeeting()` - Obter reunião acontecendo agora
- Funções de banco de dados local

### `zoom_manage.php`
Painel administrativo:
- Interface para criar reuniões
- Adicionar reuniões existentes por ID/URL
- Listar reuniões agendadas
- Sincronizar com API Zoom
- Deletar reuniões
- **Acesso restrito a administradores**

### `index_with_zoom.php`
Página principal atualizada:
- Exibe reunião atual do Zoom (se houver)
- Mostra informações da reunião
- Iframe para participar da reunião
- Lista de próximas reuniões
- Chat ao vivo
- Fallback para embed code padrão

### `install.php`
Script de instalação:
- Verificação de configurações
- Criação de tabela
- Testes de autenticação
- **Deve ser deletado após instalação**

---

## 🎯 Como Usar

### Para Administradores

#### 1. Acessar Painel de Gerenciamento

```
http://seusite.com/zoom_manage.php
```

#### 2. Criar Nova Reunião

Preencha o formulário "Criar Nova Reunião":
- **Título:** Nome da reunião
- **Data/Hora:** Quando a reunião acontecerá
- **Duração:** Em minutos (15 a 480)
- **Fuso Horário:** Selecione o apropriado
- **Agenda:** Descrição opcional

Clique em "Criar Reunião" - o sistema irá:
1. Criar a reunião no Zoom via API
2. Salvar no banco de dados local
3. Gerar links de participante e host
4. Exibir ID da reunião criada

#### 3. Adicionar Reunião Existente

Se você já criou uma reunião no painel do Zoom:
1. Copie o **ID da reunião** (ex: 1234567890)
2. Ou copie o **link completo** (ex: https://zoom.us/j/1234567890)
3. Cole no campo "ID ou Link da Reunião"
4. Clique em "Adicionar Reunião"

O sistema irá buscar as informações via API e salvar localmente.

#### 4. Gerenciar Reuniões

Na lista de reuniões, você pode:
- **Link de Participante:** Compartilhar com os participantes
- **Link de Host:** Seu link para iniciar a reunião
- **Sincronizar:** Atualizar informações do Zoom
- **Deletar:** Remover reunião

### Para Usuários

#### Acessar Página Principal

```
http://seusite.com/index_with_zoom.php
```

**Reunião ao Vivo:**
- Se houver reunião acontecendo AGORA, será exibida automaticamente
- Card com informações (título, horário, duração, agenda)
- Botão "Entrar na Reunião" (abre em nova aba)
- Iframe incorporado para participar direto na página

**Próximas Reuniões:**
- Grid com cards das próximas reuniões
- Informações completas de cada reunião
- Botão para acessar cada reunião

**Chat ao Vivo:**
- Disponível apenas durante transmissões
- Envio de mensagens em tempo real

---

## 🎨 Funcionalidades

### ✨ Principais Recursos

#### 1. Criação de Reuniões
- Interface amigável para criar reuniões
- Configuração de data, hora, duração
- Definição de fuso horário
- Agenda/descrição personalizada

#### 2. Gerenciamento Completo
- Adicionar reuniões criadas externamente
- Sincronização com API Zoom
- Atualização automática de status
- Exclusão de reuniões

#### 3. Exibição Inteligente
- Detecta reunião atual automaticamente
- Exibe apenas reuniões futuras
- Design responsivo (desktop, tablet, mobile)
- Fallback para embed padrão se não houver Zoom

#### 4. Links de Participação
- Link direto para participantes
- Link de host para iniciar reunião
- Iframe incorporado na página
- Compatível com todos os dispositivos

#### 5. Integração com Banco de Dados
- Armazenamento local de reuniões
- Cache de informações
- Histórico de reuniões
- Status de reuniões (scheduled, started, ended)

#### 6. Autenticação OAuth 2.0
- Server-to-Server OAuth (sem interação do usuário)
- Token cacheado (evita limites de requisição)
- Renovação automática de token
- Segurança aprimorada

---

## 🔧 Configurações Avançadas

### Ajustar Timezone Padrão

No `zoom_config.php`:
```php
date_default_timezone_set('America/Sao_Paulo');
```

Opções comuns:
- `America/Sao_Paulo` - Brasília (UTC-3)
- `America/New_York` - Nova York (UTC-5)
- `Europe/London` - Londres (UTC+0)
- `America/Los_Angeles` - Los Angeles (UTC-8)

### Configurações de Reunião Padrão

No `zoom_functions.php`, função `createZoomMeeting()`:
```php
'settings' => [
    'host_video' => true,              // Vídeo do host ativo
    'participant_video' => true,       // Vídeo dos participantes
    'join_before_host' => false,       // Entrar antes do host
    'mute_upon_entry' => true,         // Silenciar ao entrar
    'waiting_room' => true,            // Sala de espera
    'allow_multiple_devices' => true,  // Múltiplos dispositivos
    'auto_recording' => 'none',        // Gravação automática
]
```

### Limite de Reuniões Exibidas

No `zoom_functions.php`:
```php
function getActiveMeetingsFromDatabase($limit = 10)
```

No `index_with_zoom.php`:
```php
$upcomingZoomMeetings = getActiveMeetingsFromDatabase(5);
```

---

## 🐛 Troubleshooting

### Erro: "Não foi possível obter token de autenticação"

**Causa:** Credenciais do Zoom inválidas ou aplicativo não ativado

**Solução:**
1. Verifique as credenciais no `zoom_config.php`
2. Acesse Zoom Marketplace: https://marketplace.zoom.us/
3. Vá em "Develop" → "Build App"
4. Verifique se o app Server-to-Server OAuth está ativado
5. Confirme Account ID, Client ID e Client Secret

### Erro: "Conexão com banco de dados falhou"

**Causa:** Configurações de BD incorretas

**Solução:**
1. Verifique DB_HOST, DB_NAME, DB_USER, DB_PASS
2. Teste conexão manualmente:
```bash
mysql -h localhost -u seu_usuario -p
```
3. Verifique se o banco de dados existe:
```sql
SHOW DATABASES;
```

### Reunião não aparece na página

**Causa:** Reunião não está ativa ou data/hora incorreta

**Solução:**
1. Acesse `zoom_manage.php`
2. Verifique se a reunião está na lista
3. Clique em "Sincronizar" para atualizar
4. Confirme que a data/hora está correta
5. Verifique se `is_active = 1` no banco:
```sql
SELECT * FROM zoom_meetings WHERE is_active = 1;
```

### Erro 401 - Unauthorized

**Causa:** Token expirado ou inválido

**Solução:**
1. Delete o cache do token:
```bash
rm /tmp/zoom_token_cache.json
```
2. Acesse `install.php` novamente para testar autenticação

### Erro 404 - Meeting not found

**Causa:** ID da reunião incorreto ou reunião foi deletada no Zoom

**Solução:**
1. Verifique o ID no painel do Zoom
2. Tente adicionar a reunião novamente
3. Sincronize a reunião no `zoom_manage.php`

### Iframe não carrega

**Causa:** Configurações de segurança do navegador ou Zoom

**Solução:**
1. Verifique configurações CSP (Content Security Policy)
2. Adicione Zoom aos domínios permitidos:
```html
<meta http-equiv="Content-Security-Policy" 
      content="frame-src https://*.zoom.us;">
```
3. Considere usar botão "Entrar na Reunião" em vez de iframe

---

## 🔐 Segurança

### Práticas Recomendadas

1. **Delete install.php após instalação**
2. **Restrinja acesso ao zoom_manage.php** apenas para administradores
3. **Use HTTPS** em produção
4. **Não exponha credenciais** do Zoom
5. **Mantenha zoom_config.php fora do diretório público** se possível
6. **Implemente rate limiting** para evitar abuso
7. **Log de ações administrativas** para auditoria

### Proteção de Credenciais

Considere usar variáveis de ambiente:
```php
define('ZOOM_CLIENT_SECRET', getenv('ZOOM_CLIENT_SECRET'));
```

---

## 📊 Estrutura do Banco de Dados

### Tabela: `zoom_meetings`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | ID primário auto-incremento |
| meeting_id | BIGINT | ID da reunião no Zoom (único) |
| topic | VARCHAR(255) | Título da reunião |
| start_time | DATETIME | Data/hora de início |
| duration | INT | Duração em minutos |
| timezone | VARCHAR(100) | Fuso horário |
| join_url | TEXT | Link para participantes |
| start_url | TEXT | Link para host |
| password | VARCHAR(50) | Senha da reunião |
| agenda | TEXT | Descrição/agenda |
| host_id | VARCHAR(100) | ID do host no Zoom |
| status | VARCHAR(50) | Status (scheduled/started/ended) |
| is_active | TINYINT(1) | Reunião ativa (1) ou não (0) |
| created_at | TIMESTAMP | Data de criação |
| updated_at | TIMESTAMP | Data de atualização |

---

## 📞 Suporte

### Recursos Úteis

- **Documentação Zoom API:** https://developers.zoom.us/docs/api/
- **Zoom Marketplace:** https://marketplace.zoom.us/
- **OAuth 2.0 Guide:** https://developers.zoom.us/docs/integrations/oauth/

### Informações da Sua Conta

```
Account ID: KiJeWwARQbGPJ1uhAWf-dw
Client ID: 8dSp8Ud3Q7ebqHV7L3Wrw
```

**IMPORTANTE:** Mantenha o Client Secret seguro e nunca o exponha publicamente!

---

## 🎉 Próximos Passos

Após a instalação bem-sucedida:

1. ✅ Crie sua primeira reunião em `zoom_manage.php`
2. ✅ Verifique se aparece em `index_with_zoom.php`
3. ✅ Teste o link de participante
4. ✅ Customize o design conforme sua identidade visual
5. ✅ Integre com seu sistema de autenticação existente
6. ✅ Configure webhooks para eventos em tempo real (opcional)

---

## 📝 Notas Finais

- A integração usa **Server-to-Server OAuth**, que não requer autorização do usuário
- Todas as reuniões criadas via API ficam vinculadas à conta configurada
- O sistema mantém sincronização automática com o Zoom
- Design totalmente responsivo e moderno
- Código completo e documentado
- Pronto para produção

**Desenvolvido com ❤️ para facilitar a integração do Zoom no seu site PHP**
