# 🔑 Como Verificar e Corrigir Credenciais do Zoom

## ⚠️ Erro Encontrado: "invalid_client"

Este erro significa que uma ou mais credenciais estão **incorretas** ou o app foi **desativado/deletado**.

---

## 📋 Passo a Passo para Verificar

### 1. Acesse o Zoom Marketplace

```
https://marketplace.zoom.us/
```

**Faça login** com a conta que criou o aplicativo.

---

### 2. Vá para "Manage" → "Build App"

1. Clique no seu **perfil** (canto superior direito)
2. Selecione **"Manage"**
3. No menu lateral, clique em **"Build App"**

---

### 3. Localize seu App Server-to-Server OAuth

Você deve ver uma lista de aplicativos. Procure por:

- **Tipo:** Server-to-Server OAuth
- **Nome:** (o nome que você deu ao criar)

**⚠️ Importante:** Se não houver nenhum app Server-to-Server OAuth, você precisa criar um novo!

---

### 4. Verificar/Obter as Credenciais Corretas

Clique no seu app para abrir. Você verá várias abas:

#### 4.1 Aba "App Credentials"

Aqui você encontra as credenciais reais:

```
┌─────────────────────────────────────┐
│ Account ID                          │
│ [Copiar]                            │
├─────────────────────────────────────┤
│ Client ID                           │
│ [Copiar]                            │
├─────────────────────────────────────┤
│ Client Secret                       │
│ [Copiar]                            │
└─────────────────────────────────────┘
```

**Clique em "Copiar" para cada credencial** e compare com o arquivo `zoom_config.php`.

---

### 5. Verificar Status do App

Na mesma página, procure por:

- **Status:** Deve estar **"Active"** ou **"Activated"**
- Se estiver **"Inactive"** ou **"Deactivated"**, clique no botão para ativar

---

## 🔄 Como Criar um Novo App (se necessário)

Se você não tem um app Server-to-Server OAuth ou o antigo foi deletado:

### Passo 1: Criar App

1. Acesse https://marketplace.zoom.us/develop/create
2. Escolha **"Server-to-Server OAuth"**
3. Clique em **"Create"**

### Passo 2: Informações Básicas

Preencha:
- **App Name:** (exemplo: "Meu Site - Zoom Integration")
- **Company Name:** Seu nome/empresa
- **Developer Contact:** Seu email
- **Short Description:** "Integração Zoom para site"

Clique em **"Continue"**

### Passo 3: Copiar Credenciais

Você verá as credenciais:
- **Account ID**
- **Client ID**
- **Client Secret**

**⚠️ IMPORTANTE:** Copie o **Client Secret** agora! Ele só é mostrado uma vez.

### Passo 4: Adicionar Escopos

Vá para a aba **"Scopes"** e adicione:

```
✓ meeting:write:admin
✓ meeting:read:admin
✓ meeting:update:admin
✓ meeting:delete:admin
✓ user:read:admin
```

Clique em **"Continue"**

### Passo 5: Ativar App

Se houver um botão **"Activate"**, clique nele.

---

## ✏️ Atualizar zoom_config.php

Com as credenciais corretas em mãos, atualize o arquivo:

```php
<?php
// Credenciais do Zoom - SUBSTITUA COM AS SUAS CREDENCIAIS REAIS
define('ZOOM_ACCOUNT_ID', 'SUA_ACCOUNT_ID_AQUI');
define('ZOOM_CLIENT_ID', 'SEU_CLIENT_ID_AQUI');
define('ZOOM_CLIENT_SECRET', 'SEU_CLIENT_SECRET_AQUI');
define('ZOOM_SECRET_TOKEN', 'SEU_SECRET_TOKEN_AQUI');
```

**Exemplo:**
```php
define('ZOOM_ACCOUNT_ID', 'abc123xyz789');
define('ZOOM_CLIENT_ID', 'YourClientId123');
define('ZOOM_CLIENT_SECRET', 'YourClientSecretABC123XYZ');
define('ZOOM_SECRET_TOKEN', 'YourWebhookSecretToken123');
```

---

## 🧪 Testar Novamente

Após atualizar as credenciais:

1. **Salve** o arquivo `zoom_config.php`
2. **Acesse** novamente:
   ```
   http://seusite.com/live-stream/test_zoom_auth.php
   ```
3. Deve mostrar: **✅ Token obtido com sucesso!**

---

## 🔍 Como Saber se as Credenciais Estão Corretas?

### Account ID
- Formato: letras, números, hífens e underscores
- Exemplo: `KiJeWwARQbGPJ1uhAWf-dw`
- Onde encontrar: Zoom Marketplace → App → App Credentials

### Client ID
- Formato: alfanumérico
- Exemplo: `8dSp8Ud3Q7ebqHV7L3Wrw`
- Onde encontrar: Zoom Marketplace → App → App Credentials

### Client Secret
- Formato: alfanumérico longo
- Exemplo: `OMqOfpuOywq8mYxYaLHrUYwwRCk9CIgK`
- Onde encontrar: Zoom Marketplace → App → App Credentials
- **⚠️ ATENÇÃO:** Só é mostrado uma vez na criação. Se perdeu, precisa gerar um novo.

---

## 🆘 Problemas Comuns

### Problema 1: "Não vejo meu app na lista"

**Possíveis causas:**
- O app foi deletado
- Você está logado com conta diferente
- O app foi criado em outra conta Zoom

**Solução:**
- Verifique se está logado com a conta correta
- Crie um novo app Server-to-Server OAuth

---

### Problema 2: "Client Secret foi perdido"

**Solução:**

Se você perdeu o Client Secret, precisa gerar um novo:

1. Acesse seu app no Zoom Marketplace
2. Vá para **"App Credentials"**
3. Procure por **"Regenerate Secret"** ou **"Generate New Secret"**
4. Clique e copie o novo secret
5. Atualize em `zoom_config.php`

---

### Problema 3: "Erro mesmo com credenciais corretas"

**Verifique:**

1. **Sem espaços extras** nas credenciais
2. **Aspas corretas** no PHP (usar aspas simples `'`)
3. **App está ativado** no Zoom Marketplace
4. **Conta Zoom não está suspensa** ou com problemas

**Teste manual via terminal:**

```bash
curl -X POST "https://zoom.us/oauth/token?grant_type=account_credentials&account_id=SEU_ACCOUNT_ID" \
  -u "SEU_CLIENT_ID:SEU_CLIENT_SECRET"
```

Substitua `SEU_ACCOUNT_ID`, `SEU_CLIENT_ID` e `SEU_CLIENT_SECRET` pelos valores reais.

Se funcionar, deve retornar:
```json
{"access_token":"eyJz...","token_type":"bearer","expires_in":3600}
```

---

## 📸 Referência Visual

### Onde Encontrar as Credenciais

```
Zoom Marketplace (marketplace.zoom.us)
│
├─ Login
│
├─ Perfil (canto superior direito)
│  └─ Manage
│
├─ Build App (menu lateral)
│
├─ [Seu App Server-to-Server OAuth]
│  │
│  ├─ App Credentials (aba)
│  │  ├─ Account ID ← COPIAR
│  │  ├─ Client ID ← COPIAR
│  │  └─ Client Secret ← COPIAR
│  │
│  ├─ Scopes (aba)
│  │  └─ Adicionar escopos necessários
│  │
│  └─ Activation (aba)
│     └─ Ativar o app
```

---

## ✅ Checklist de Verificação

Antes de continuar, confirme:

- [ ] Tenho um app **Server-to-Server OAuth** criado no Zoom Marketplace
- [ ] O app está com status **"Active"** ou **"Activated"**
- [ ] Copiei o **Account ID** correto
- [ ] Copiei o **Client ID** correto
- [ ] Copiei o **Client Secret** correto (ou gerei um novo)
- [ ] Atualizei o arquivo `zoom_config.php` com as credenciais
- [ ] **NÃO há espaços extras** antes ou depois das credenciais
- [ ] Salvei o arquivo `zoom_config.php`

---

## 🎯 Resultado Esperado

Após corrigir as credenciais, ao acessar `test_zoom_auth.php`:

```
✅ Token obtido com sucesso!
Token: eyJzdiI6IjAwMDAwMiIs...
Expira em: 3600 segundos

✅ Informações do usuário Zoom obtidas
ID: abc123
Email: seu@email.com
Nome: Seu Nome

✅ API funcionando! Encontrados X usuários
```

---

## 💡 Dica Final

Se você está tendo problemas para encontrar ou criar o app:

1. **Certifique-se** de estar logado no Zoom com a conta administrativa
2. **Alguns tipos de conta Zoom** podem ter restrições para criar apps
3. **Entre em contato com suporte Zoom** se não conseguir criar apps

**Documentação oficial:**
https://developers.zoom.us/docs/internal-apps/s2s-oauth/

---

## 🆘 Ainda Não Funciona?

Se após seguir todos os passos ainda receber "invalid_client":

1. **Crie um app completamente novo** do zero
2. **Teste com as credenciais do novo app**
3. Se funcionar, use o novo app
4. Se não funcionar, pode haver um problema com sua conta Zoom

**Contato Zoom:**
- Suporte: https://support.zoom.us/
- Documentação: https://developers.zoom.us/docs/

---

**Após corrigir as credenciais, execute `test_zoom_auth.php` novamente!**
