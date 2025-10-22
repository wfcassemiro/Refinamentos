# 🚀 Guia Rápido: Obter Credenciais Zoom

## ⚡ Solução Rápida em 5 Minutos

### Passo 1: Acesse o Zoom Marketplace

Abra no navegador:
```
https://marketplace.zoom.us/user/build
```

### Passo 2: Faça Login

Use suas credenciais do Zoom (mesma conta que você usa para reuniões).

### Passo 3: Verificar App Existente

Você verá uma lista de apps. Procure por:
- Tipo: **Server-to-Server OAuth**

**Opção A: Se encontrou o app**
- Clique nele
- Vá para o **Passo 4**

**Opção B: Se NÃO tem nenhum app Server-to-Server OAuth**
- Clique em **"Create"** ou **"+ Create"**
- Selecione **"Server-to-Server OAuth"**
- Clique em **"Create"**
- Preencha:
  - **App Name:** "Meu Site Zoom"
  - **Company Name:** Seu nome
  - **Developer Email:** Seu email
  - **Short Description:** "Integração Zoom"
- Clique em **"Create"**

### Passo 4: Copiar Credenciais

Na página do app, você verá:

```
App Credentials
├─ Account ID: [COPIAR]
├─ Client ID: [COPIAR]
└─ Client Secret: [COPIAR]
```

**Clique em cada botão "Copy" ou "View and Copy"**

### Passo 5: Adicionar Escopos (IMPORTANTE!)

1. No menu do app, clique na aba **"Scopes"**
2. Clique em **"+ Add Scopes"**
3. Marque estas opções:
   - ☑️ `meeting:write:admin`
   - ☑️ `meeting:read:admin`
   - ☑️ `meeting:update:admin`
   - ☑️ `meeting:delete:admin`
4. Clique em **"Done"** ou **"Continue"**

### Passo 6: Ativar App

1. Procure por um botão **"Activate"** ou verifique se o status está **"Active"**
2. Se estiver "Inactive", clique para ativar

### Passo 7: Testar Credenciais

Acesse no navegador:
```
http://seusite.com/live-stream/testar_credenciais.html
```

Cole as credenciais e clique em **"Testar"**.

**Se mostrar ✅ sucesso:**
- Copie o código PHP que aparece
- Cole no arquivo `zoom_config.php`
- Salve o arquivo

**Se mostrar ❌ erro:**
- Volte ao Passo 4 e copie novamente
- Certifique-se de que não há espaços extras

---

## 📋 Checklist

- [ ] Acessei https://marketplace.zoom.us/user/build
- [ ] Tenho um app Server-to-Server OAuth (ou criei um novo)
- [ ] Copiei Account ID
- [ ] Copiei Client ID
- [ ] Copiei Client Secret
- [ ] Adicionei escopos de `meeting`
- [ ] App está com status "Active"
- [ ] Testei em `testar_credenciais.html`
- [ ] Recebi ✅ sucesso no teste
- [ ] Atualizei `zoom_config.php`

---

## 🆘 Problemas Comuns

### "Não consigo ver App Credentials"

Você está em um app antigo ou do tipo errado. Crie um novo:
1. Vá para https://marketplace.zoom.us/develop/create
2. Escolha **"Server-to-Server OAuth"**
3. Preencha e crie

### "Client Secret não aparece"

Se você está vendo um app existente e o Client Secret está oculto:
1. Procure por **"Regenerate Secret"** ou **"View Secret"**
2. Clique para gerar/ver novo secret
3. Copie imediatamente (só aparece uma vez!)

### "Teste dá erro mesmo copiando certo"

Possíveis causas:
1. **Espaços extras** - Certifique-se de não ter espaços antes/depois
2. **App não ativado** - Verifique se está "Active"
3. **Sem escopos** - Adicione os escopos de meeting

---

## ✅ Formato Correto das Credenciais

**Account ID:**
```
Exemplo: abc123XYZ789-_
Características: letras, números, hífens, underscores
Tamanho: ~20-30 caracteres
```

**Client ID:**
```
Exemplo: 8dSp8Ud3Q7ebqHV7L3Wrw
Características: alfanumérico
Tamanho: ~20-25 caracteres
```

**Client Secret:**
```
Exemplo: OMqOfpuOywq8mYxYaLHrUYwwRCk9CIgK
Características: alfanumérico
Tamanho: ~30-40 caracteres
```

---

## 🎯 Depois de Atualizar zoom_config.php

Execute o teste final:
```
http://seusite.com/live-stream/test_zoom_auth.php
```

Deve mostrar:
```
✅ Token obtido com sucesso!
✅ Informações do usuário Zoom obtidas
✅ API funcionando!
```

Se sim, acesse:
```
http://seusite.com/live-stream/zoom_manage.php
```

E crie sua primeira reunião! 🎉

---

## 💡 Dica de Ouro

**Sempre que copiar uma credencial:**
1. Clique no botão "Copy" ou "View and Copy"
2. Cole em um editor de texto (Notepad, VS Code)
3. Verifique se não tem espaços antes/depois
4. Copie dali para o zoom_config.php

Isso evita copiar espaços invisíveis que causam erros.

---

## 📞 Links Úteis

- **Criar App:** https://marketplace.zoom.us/develop/create
- **Meus Apps:** https://marketplace.zoom.us/user/build
- **Documentação:** https://developers.zoom.us/docs/internal-apps/s2s-oauth/

---

**⏱️ Tempo estimado: 5 minutos**

Após seguir este guia, suas credenciais devem funcionar! 🚀
