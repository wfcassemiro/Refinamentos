# 🚀 Instruções de Instalação Corrigidas - Zoom no Live-Stream

## 📂 Estrutura Correta dos Arquivos

Todos os arquivos devem estar na pasta `/live-stream/`:

```
/live-stream/
├── zoom_config.php
├── zoom_auth.php
├── zoom_functions.php
├── zoom_manage.php  (use zoom_manage_CORRETO.php)
├── install.php
└── (seus outros arquivos do live-stream)
```

---

## ✅ Passo 1: Copiar Arquivos Corretos

### 1.1 Renomear o arquivo correto

```bash
cd /live-stream/
mv zoom_manage_CORRETO.php zoom_manage.php
```

Ou simplesmente **delete o `zoom_manage.php` antigo** e **renomeie `zoom_manage_CORRETO.php`** para `zoom_manage.php`.

---

## ✅ Passo 2: Verificar Configuração do Banco

O arquivo `zoom_config.php` já está com as credenciais corretas:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u335416710_t101_db');
define('DB_USER', 'u335416710_t101');
define('DB_PASS', 'Pa392ap!');
```

✅ **Nenhuma alteração necessária!**

---

## ✅ Passo 3: Executar Instalação

Acesse no navegador:

```
http://seusite.com/live-stream/install.php
```

Você verá:
- ✅ Credenciais do Zoom verificadas
- ✅ Conexão com banco de dados
- ✅ Tabela `zoom_meetings` criada
- ⚠️ Aviso sobre usuário (não é crítico)

**Importante:** O aviso na Etapa 4 é normal e não impede o funcionamento!

---

## ✅ Passo 4: Acessar Painel de Gerenciamento

Acesse:

```
http://seusite.com/live-stream/zoom_manage.php
```

### O que mudou?

**Autenticação Corrigida:**
- ✅ Usa a sessão existente do seu sistema (`$_SESSION['user_id']`)
- ✅ Verifica se o usuário é `admin` consultando a tabela `users`
- ✅ Se não estiver logado, redireciona para `/auth/login.php`
- ✅ Se não for admin, mostra mensagem de acesso negado

**Caminhos Corrigidos:**
- ✅ Usa `__DIR__ . '/../config/database.php'` para conexão
- ✅ Inclui head.php do sistema: `__DIR__ . '/../vision/includes/head.php'`
- ✅ CSS embutido no próprio arquivo (não depende de arquivos externos)

---

## 🔐 Como Funciona a Autenticação

### Verificação de Admin

O sistema verifica se você é admin da seguinte forma:

```php
// 1. Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    // Redireciona para login
}

// 2. Busca role no banco de dados
SELECT role FROM users WHERE id = $_SESSION['user_id']

// 3. Verifica se role = 'admin'
if ($user['role'] === 'admin') {
    // Permite acesso
}
```

### Sua Conta Admin

De acordo com o banco de dados, você tem **2 usuários admin**:

1. **Email:** demo@example.com
2. **Email:** testetcc2204@gmail.com

**Certifique-se de estar logado com uma dessas contas!**

---

## 📋 Testando o Sistema

### 1. Fazer Login como Admin

Acesse seu sistema e faça login com:
- `demo@example.com` OU
- `testetcc2204@gmail.com`

### 2. Acessar Painel Zoom

```
http://seusite.com/live-stream/zoom_manage.php
```

Deve carregar a página de gerenciamento! ✅

### 3. Criar Reunião de Teste

Preencha o formulário:
- **Título:** "Reunião de Teste"
- **Data/Hora:** Selecione agora ou daqui 10 minutos
- **Duração:** 30
- **Descrição:** "Testando integração"

Clique em **"Criar Reunião"**

### 4. Verificar Criação

Se funcionar, você verá:
- ✅ Mensagem de sucesso
- ✅ ID da reunião
- ✅ Reunião na lista abaixo

---

## 🐛 Troubleshooting

### Problema 1: "Acesso Negado"

**Causa:** Você não está logado como admin

**Solução:**
1. Verifique se está logado
2. Verifique se seu usuário tem `role = 'admin'` no banco
3. Execute no MySQL:

```sql
-- Verificar seu role
SELECT id, email, role FROM users WHERE email = 'seu_email@aqui.com';

-- Se não for admin, tornar admin:
UPDATE users SET role = 'admin' WHERE email = 'seu_email@aqui.com';
```

### Problema 2: Redirecionado para `/auth/login.php` que não existe

**Causa:** Você não está logado no sistema

**Solução:**
1. Faça login no sistema normalmente
2. Depois acesse `zoom_manage.php`

**OU** ajuste o caminho de redirecionamento:

```php
// Em zoom_manage.php, linha ~40, mudar de:
header('Location: /auth/login.php');

// Para o caminho correto do seu login:
header('Location: /vision/login.php');  // ou o caminho correto
```

### Problema 3: CSS não carrega

**Não é mais um problema!** O novo `zoom_manage_CORRETO.php` tem **CSS embutido** na própria página. Não depende de arquivos externos.

### Problema 4: "Erro ao conectar com banco de dados"

**Causa:** Caminho do `database.php` incorreto

**Solução:**
Verifique se existe o arquivo `/config/database.php` em relação à pasta `/live-stream/`.

Se estiver em outro lugar, ajuste em `zoom_manage.php`:

```php
// Linha ~12
require_once __DIR__ . '/../config/database.php';

// Ajustar para o caminho correto, por exemplo:
require_once __DIR__ . '/../../config/database.php';
// OU
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
```

---

## 🎯 Estrutura de Pastas Presumida

```
/ (raiz do site)
├── config/
│   └── database.php
├── vision/
│   ├── includes/
│   │   ├── head.php
│   │   ├── header.php
│   │   ├── sidebar.php
│   │   └── footer.php
│   └── assets/
│       └── css/
├── auth/
│   └── login.php
└── live-stream/
    ├── index.php
    ├── zoom_config.php
    ├── zoom_auth.php
    ├── zoom_functions.php
    ├── zoom_manage.php
    └── install.php
```

Se sua estrutura for diferente, ajuste os caminhos conforme necessário.

---

## 📝 Arquivos que Você Deve Ter Agora

Na pasta `/live-stream/`:

1. ✅ `zoom_config.php` - Configurações (com suas credenciais)
2. ✅ `zoom_auth.php` - Autenticação OAuth do Zoom
3. ✅ `zoom_functions.php` - Funções da API
4. ✅ `zoom_manage.php` - Painel admin (use o arquivo CORRETO)
5. ✅ `install.php` - Script de instalação

**Delete:**
- ❌ `zoom_manage.php` original (antigo)

**Renomeie:**
- ✅ `zoom_manage_CORRETO.php` → `zoom_manage.php`

---

## ✨ Próximos Passos

Após o `zoom_manage.php` funcionar:

1. **Criar sua primeira reunião** ✅
2. **Atualizar index.php** para exibir as reuniões
3. **Testar a exibição** na página principal

Quando estiver pronto, me avise e eu crio o `index.php` atualizado!

---

## 🆘 Precisa de Ajuda?

Se ainda tiver problemas, me envie:

1. A mensagem de erro exata
2. Print da tela
3. O resultado de: `SELECT email, role FROM users WHERE role = 'admin'`

---

## 🎉 Checklist Final

Antes de considerar instalado:

- [ ] `install.php` executado com sucesso
- [ ] Logado com conta admin no sistema
- [ ] `zoom_manage.php` carrega sem erros
- [ ] Consegue criar uma reunião de teste
- [ ] Reunião aparece na lista

Se todos os itens estiverem marcados: **Integração Funcional!** 🎊
