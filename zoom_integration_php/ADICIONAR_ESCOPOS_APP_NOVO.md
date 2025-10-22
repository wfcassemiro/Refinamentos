# 🔧 Adicionar Escopos no Novo App Zoom

## ❌ Erro Atual

```
Invalid access token, does not contain scopes:
[meeting:write:meeting, meeting:write:meeting:admin]
```

**Significado:** Seu novo app **não tem os escopos necessários** para criar reuniões.

---

## ✅ Solução: Adicionar Escopos

### Passo 1: Acessar Seu Novo App

1. Vá para: https://marketplace.zoom.us/user/build
2. Clique no **novo app** que você criou (Server-to-Server OAuth)

### Passo 2: Ir para Scopes

Na página do app, procure e clique na aba **"Scopes"**

### Passo 3: Adicionar Escopos OBRIGATÓRIOS

Clique em **"+ Add Scopes"** e adicione TODOS estes escopos:

#### ✅ Escopos de Meeting (OBRIGATÓRIOS)

```
✓ meeting:write:meeting:admin
  ou
✓ meeting:write:meeting

✓ meeting:read:meeting:admin

✓ meeting:update:meeting:admin

✓ meeting:delete:meeting:admin
```

#### ✅ Escopos de User (RECOMENDADOS)

```
✓ user:read:user:admin
```

### Passo 4: Salvar

1. Depois de marcar todos os escopos, clique em **"Done"** ou **"Add"**
2. Clique em **"Continue"** ou **"Save"** no final da página
3. Aguarde a confirmação de que foi salvo

### Passo 5: Verificar

Na aba "Scopes", você deve ver algo assim:

```
Scopes (6)
───────────────────────────────────
Meetings
✓ meeting:write:meeting:admin
  Create a meeting for a user
  
✓ meeting:read:meeting:admin
  View a meeting
  
✓ meeting:update:meeting:admin
  Update a meeting
  
✓ meeting:delete:meeting:admin
  Delete a meeting

User
✓ user:read:user:admin
  View a user
```

---

## 🔄 Passo 6: Limpar Cache do Token

O token antigo não tem os novos escopos. Você PRECISA limpar o cache:

### Opção A: Via Navegador

Acesse:
```
http://seusite.com/live-stream/limpar_cache.php
```

### Opção B: Via Servidor

Execute no terminal:
```bash
rm /tmp/zoom_token_cache.json
```

### Opção C: Via test_zoom_auth.php

Acesse:
```
http://seusite.com/live-stream/test_zoom_auth.php
```

Este arquivo limpa o cache automaticamente.

---

## 🧪 Passo 7: Testar

Após adicionar os escopos e limpar o cache:

1. **Teste a autenticação:**
   ```
   http://seusite.com/live-stream/test_zoom_auth.php
   ```
   
   Deve mostrar: ✅ Token obtido com sucesso!

2. **Tente criar reunião:**
   ```
   http://seusite.com/live-stream/zoom_manage.php
   ```
   
   Preencha o formulário e clique em "Criar Reunião"
   
   Deve funcionar agora! ✅

---

## 📋 Checklist

Antes de testar novamente:

- [ ] Acessei o novo app no Zoom Marketplace
- [ ] Cliquei na aba "Scopes"
- [ ] Adicionei `meeting:write:meeting:admin`
- [ ] Adicionei `meeting:read:meeting:admin`
- [ ] Adicionei `meeting:update:meeting:admin`
- [ ] Adicionei `meeting:delete:meeting:admin`
- [ ] Adicionei `user:read:user:admin` (opcional)
- [ ] Salvei as alterações (botão "Continue" ou "Save")
- [ ] Limpei o cache do token
- [ ] Testei em test_zoom_auth.php
- [ ] Tentei criar reunião novamente

---

## 💡 Dica

Ao adicionar escopos, você pode usar a **busca** na página de Scopes:

1. Digite: `meeting:write`
2. Marque: `meeting:write:meeting:admin`
3. Digite: `meeting:read`
4. Marque: `meeting:read:meeting:admin`
5. E assim por diante...

---

## 🆘 Se Não Encontrar os Escopos

Se você não conseguir encontrar os escopos com a nomenclatura granular:

### Procure por Descrições:

- **"Create a meeting"** → meeting:write:meeting:admin
- **"View a meeting"** → meeting:read:meeting:admin
- **"Update a meeting"** → meeting:update:meeting:admin
- **"Delete a meeting"** → meeting:delete:meeting:admin

### Ou Procure por Categorias:

Na página de Scopes, há categorias como:
- **"Meetings"** - expanda e marque todos os escopos de meeting
- **"User"** - expanda e marque user:read

---

## ⚠️ Importante

**Sempre salve** depois de adicionar escopos:
- Procure botão "Continue" ou "Save" no final da página
- Aguarde a confirmação de que foi salvo
- **NÃO feche a página** antes de salvar

**Sempre limpe o cache** depois de adicionar escopos:
- O token antigo não tem os novos escopos
- Você precisa obter um novo token com os escopos atualizados

---

## ✅ Resultado Esperado

Após seguir todos os passos:

**test_zoom_auth.php:**
```
✅ Token obtido com sucesso!
✅ Informações do usuário Zoom obtidas
✅ API funcionando!
```

**zoom_manage.php:**
```
✅ Reunião criada com sucesso! ID: 1234567890
```

---

**Adicione os escopos, limpe o cache e teste novamente! 🚀**
