# 🔍 Verificar Tipo de App Zoom

## ⚠️ Problema Principal

Os escopos que você mostrou têm nomenclatura **ERRADA** para Server-to-Server OAuth:

```
❌ meeting:update:meeting:admin  (formato errado - tem :meeting: no meio)
❌ user:read:user:admin          (formato errado - tem :user: no meio)
```

Isso indica que:
1. O app pode não ser do tipo **Server-to-Server OAuth**
2. OU os escopos foram adicionados incorretamente

---

## 🎯 Como Verificar o Tipo Correto

### Passo 1: Acessar Seu App

1. Vá para: https://marketplace.zoom.us/user/build
2. Você verá uma lista de apps

### Passo 2: Verificar o Tipo

Para cada app na lista, você verá:
- **Nome do App**
- **Tipo do App** ← Importante!

**Procure por:**
```
┌─────────────────────────────┐
│ Nome: [Seu App]             │
│ Tipo: Server-to-Server OAuth│ ← Deve ser este!
│ Status: Active              │
└─────────────────────────────┘
```

### Tipos de Apps Zoom

| Tipo | Para Que Serve | Usar? |
|------|----------------|-------|
| **Server-to-Server OAuth** | Apps backend sem usuário | ✅ SIM |
| OAuth | Apps que fazem login com Zoom | ❌ NÃO |
| JWT (Deprecated) | Apps antigos | ❌ NÃO |
| Webhook Only | Apenas receber eventos | ❌ NÃO |
| Chatbot | Bots do Zoom Chat | ❌ NÃO |

**Para esta integração, você PRECISA de "Server-to-Server OAuth"!**

---

## 🔍 Se o Tipo Estiver Errado

### Situação A: Você tem um app OAuth ou JWT

**Problema:** Esses tipos de app não funcionam para integração backend.

**Solução:** Criar um novo app do tipo correto.

### Situação B: Você não tem certeza qual app é

**Solução:** Crie um novo app do zero seguindo o guia abaixo.

---

## 🆕 Criar Novo App Server-to-Server OAuth

### Passo 1: Acessar Criação de App

```
https://marketplace.zoom.us/develop/create
```

### Passo 2: Escolher Tipo

Você verá várias opções de app:

```
┌─────────────────────────────────────┐
│ Choose App Type                     │
├─────────────────────────────────────┤
│ ○ OAuth                             │
│   For apps that need user login     │
│                                     │
│ ● Server-to-Server OAuth            │ ← Selecione este!
│   For backend integrations          │
│                                     │
│ ○ Webhook Only                      │
│   For receiving events              │
│                                     │
│ ○ Chatbot                          │
│   For Zoom Chat bots                │
└─────────────────────────────────────┘
```

**Clique em "Server-to-Server OAuth"**

### Passo 3: Preencher Informações

**App Name:**
```
Meu Site - Integração Zoom
```

**Short Description:**
```
Integração de reuniões do Zoom para meu website
```

**Company Name:**
```
[Seu nome ou nome da empresa]
```

**Developer Contact Information:**
```
Nome: [Seu nome]
Email: [Seu email]
```

Clique em **"Create"**

### Passo 4: Copiar Credenciais

Você verá imediatamente:

```
┌───────────────────────────────────┐
│ App Credentials                   │
├───────────────────────────────────┤
│ Account ID                        │
│ abc123xyz789                      │
│ [Copy]                            │
│                                   │
│ Client ID                         │
│ YourClientId123                   │
│ [Copy]                            │
│                                   │
│ Client Secret                     │
│ YourClientSecret123               │
│ [Copy] [View]                     │
└───────────────────────────────────┘
```

**⚠️ IMPORTANTE:**
- Copie o **Client Secret** AGORA!
- Ele só é mostrado uma vez
- Se perder, terá que regenerar

Clique em **"Continue"**

### Passo 5: Adicionar Escopos CORRETOS

Na página de Scopes:

1. Clique em **"+ Add Scopes"**
2. Na busca, digite: `meeting:write:admin`
3. Marque: ✅ `meeting:write:admin`
4. Digite: `meeting:read:admin`
5. Marque: ✅ `meeting:read:admin`
6. Digite: `meeting:update:admin`
7. Marque: ✅ `meeting:update:admin`
8. Digite: `meeting:delete:admin`
9. Marque: ✅ `meeting:delete:admin`
10. Digite: `user:read:admin`
11. Marque: ✅ `user:read:admin`
12. Clique em **"Done"**

**Verifique:** Os escopos devem aparecer SEM `:meeting:` ou `:user:` no meio!

Clique em **"Continue"**

### Passo 6: Ativar App

Na página de Activation:
1. Procure por um botão **"Activate"**
2. Clique nele
3. Confirme

O status deve mudar para **"Activated"** ou **"Active"**

---

## 🧪 Testar Novas Credenciais

### Passo 1: Abrir Testador

```
http://seusite.com/live-stream/testar_credenciais.html
```

### Passo 2: Colar Credenciais

Cole as credenciais do **NOVO app** que você acabou de criar:
- Account ID
- Client ID
- Client Secret

### Passo 3: Testar

Clique em **"🧪 Testar Credenciais"**

**Deve mostrar:**
```
✅ Credenciais Válidas!
Token de acesso obtido com sucesso.
```

### Passo 4: Atualizar zoom_config.php

Copie o código que aparece na tela e cole no `zoom_config.php`:

```php
define('ZOOM_ACCOUNT_ID', 'seu_novo_account_id');
define('ZOOM_CLIENT_ID', 'seu_novo_client_id');
define('ZOOM_CLIENT_SECRET', 'seu_novo_client_secret');
```

---

## 📋 Comparação de Escopos

### ✅ CORRETO (Server-to-Server OAuth)

```
meeting:write:admin
meeting:read:admin
meeting:update:admin
meeting:delete:admin
user:read:admin
```

**Formato:** `categoria:ação:admin`

### ❌ INCORRETO (Outro tipo de app)

```
meeting:write:meeting:admin
meeting:read:meeting:admin
user:read:user:admin
```

**Formato:** `categoria:ação:categoria:admin` ← Repetido!

---

## 🎯 Checklist Final

Após criar o novo app:

- [ ] Tipo do app: **Server-to-Server OAuth**
- [ ] Escopos adicionados no formato: `categoria:ação:admin`
- [ ] **NÃO** tem `:meeting:meeting:` ou `:user:user:`
- [ ] App está com status **"Active"**
- [ ] Copiei Account ID, Client ID e Client Secret
- [ ] Testei em `testar_credenciais.html`
- [ ] Recebi ✅ sucesso
- [ ] Atualizei `zoom_config.php`
- [ ] Testei em `test_zoom_auth.php`
- [ ] Tudo funcionando!

---

## 💡 Dica de Ouro

**Se você vir escopos com formato duplo** (`:meeting:meeting:` ou `:user:user:`), o app está do tipo **errado**.

**Solução rápida:**
1. Crie um novo app do tipo **Server-to-Server OAuth**
2. Use as credenciais do novo app
3. Ignore o app antigo

Demora apenas 5 minutos para criar um novo app!

---

## 🆘 Ainda com Problemas?

Se após criar um novo app **Server-to-Server OAuth** e adicionar os escopos corretos você ainda receber "invalid_client":

1. **Verifique se não copiou espaços** antes ou depois das credenciais
2. **Certifique-se de que o app está ativado**
3. **Aguarde 1-2 minutos** após ativar (propagação pode demorar)
4. **Tente regenerar o Client Secret** e teste novamente

---

**Após seguir este guia, sua integração funcionará! 🚀**
