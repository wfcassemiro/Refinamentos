# 🔧 Corrigir Escopos do Zoom

## ⚠️ Problema Identificado

Seus escopos estão com nomenclatura **INCORRETA**:

### ❌ Escopos Atuais (INCORRETOS)
```
meeting:update:meeting:admin
meeting:delete:meeting:admin
meeting:update:status:admin
meeting:write:meeting:admin
user:read:user:admin
user:write:user:admin
```

### ✅ Escopos Corretos (NECESSÁRIOS)
```
meeting:write:admin
meeting:read:admin
meeting:update:admin
meeting:delete:admin
user:read:admin
```

**Nota:** A diferença está que você tem `:meeting:` ou `:user:` no meio, quando deveria ser apenas `:admin` no final.

---

## 🎯 Solução: Remover e Adicionar Escopos Corretos

### Passo 1: Acessar App no Zoom

1. Vá para: https://marketplace.zoom.us/user/build
2. Clique no seu app **Server-to-Server OAuth**

### Passo 2: Ir para Escopos

1. Clique na aba **"Scopes"** no menu do app

### Passo 3: Remover Escopos Incorretos

Para cada escopo que tem `:meeting:` ou `:user:` no meio:
1. Procure por um ícone de **"lixeira"** ou **"x"** ao lado do escopo
2. Clique para remover
3. Confirme a remoção

Remova estes escopos:
- ❌ `meeting:update:meeting:admin`
- ❌ `meeting:delete:meeting:admin`
- ❌ `meeting:update:status:admin`
- ❌ `meeting:write:meeting:admin`
- ❌ `user:read:user:admin`
- ❌ `user:write:user:admin`

### Passo 4: Adicionar Escopos Corretos

1. Clique em **"+ Add Scopes"**
2. Na busca, digite: `meeting`
3. Selecione **apenas** estes:
   - ✅ `meeting:write:admin` - Create a meeting
   - ✅ `meeting:read:admin` - View meetings
   - ✅ `meeting:update:admin` - Update a meeting
   - ✅ `meeting:delete:admin` - Delete a meeting

4. Na busca, digite: `user`
5. Selecione:
   - ✅ `user:read:admin` - View users

6. Clique em **"Done"** ou **"Continue"**

### Passo 5: Salvar e Ativar

1. Certifique-se de clicar em **"Continue"** ou **"Save"** no final
2. Verifique se o app está **"Active"**

---

## 🔍 Como Verificar se os Escopos Estão Corretos

Na aba "Scopes", você deve ver exatamente isso:

```
Meetings
✓ meeting:write:admin - Create a meeting
✓ meeting:read:admin - View meetings
✓ meeting:update:admin - Update a meeting
✓ meeting:delete:admin - Delete a meeting

User
✓ user:read:admin - View users
```

**NENHUM escopo deve ter `:meeting:` ou `:user:` no meio do nome!**

---

## ⚡ Após Corrigir os Escopos

### Limpe o Cache do Token

Execute no navegador:
```
http://seusite.com/live-stream/test_zoom_auth.php
```

Isso limpa automaticamente o cache e testa com os novos escopos.

### Teste as Credenciais Novamente

```
http://seusite.com/live-stream/testar_credenciais.html
```

Deve mostrar: **✅ Credenciais Válidas!**

---

## 🤔 Por Que Isso Aconteceu?

Os escopos com `:meeting:` ou `:user:` no meio são de **outros tipos de apps Zoom** (como OAuth apps ou JWT apps).

Para **Server-to-Server OAuth**, os escopos têm formato diferente e mais simples.

---

## 🆘 Se Ainda Não Funcionar

### Opção 1: Verificar Tipo de App

No Zoom Marketplace, na página do seu app, verifique:

- **Tipo do App:** Deve ser **"Server-to-Server OAuth"**
- Se for "OAuth", "JWT" ou outro tipo, você precisa criar um novo app do tipo correto

### Opção 2: Criar Novo App do Zero

Se o tipo estiver errado ou continuar com problemas:

1. Vá para: https://marketplace.zoom.us/develop/create
2. Selecione **"Server-to-Server OAuth"**
3. Clique em **"Create"**
4. Preencha as informações básicas
5. Na aba "Scopes", adicione os escopos corretos (sem `:meeting:` no meio)
6. Copie as novas credenciais
7. Teste em `testar_credenciais.html`

---

## 📋 Checklist de Verificação

Após seguir os passos:

- [ ] Removi todos os escopos com `:meeting:` ou `:user:` no meio
- [ ] Adicionei `meeting:write:admin`
- [ ] Adicionei `meeting:read:admin`
- [ ] Adicionei `meeting:update:admin`
- [ ] Adicionei `meeting:delete:admin`
- [ ] Adicionei `user:read:admin`
- [ ] Salvei as alterações
- [ ] App está "Active"
- [ ] Testei em `testar_credenciais.html`
- [ ] Recebi ✅ sucesso

---

## 📸 Referência Visual

### Como Deve Aparecer

```
┌─────────────────────────────────────────┐
│ Scopes                                  │
├─────────────────────────────────────────┤
│ Meetings                                │
│ ✓ meeting:write:admin                   │
│   Create a meeting                      │
│                                         │
│ ✓ meeting:read:admin                    │
│   View meetings                         │
│                                         │
│ ✓ meeting:update:admin                  │
│   Update a meeting                      │
│                                         │
│ ✓ meeting:delete:admin                  │
│   Delete a meeting                      │
│                                         │
│ User                                    │
│ ✓ user:read:admin                       │
│   View users                            │
└─────────────────────────────────────────┘
```

### ❌ Como NÃO Deve Aparecer

```
✗ meeting:write:meeting:admin
✗ meeting:update:meeting:admin
✗ user:read:user:admin
```

Se você ver escopos com formato duplo (`:meeting:meeting:` ou `:user:user:`), eles estão incorretos!

---

## 💡 Dica Importante

**Server-to-Server OAuth** usa escopos no formato:
```
categoria:ação:admin
```

Exemplos:
- `meeting:write:admin`
- `user:read:admin`
- `recording:read:admin`

**NÃO** tem a palavra da categoria repetida no meio!

---

## ✅ Resultado Esperado

Após corrigir os escopos e testar:

```
✅ Credenciais Válidas!
Token de acesso obtido com sucesso.
Token: eyJzdiI6IjAwMDAwMiIs...
Expira em: 3600 segundos
```

Então você pode:
1. Atualizar `zoom_config.php` com as credenciais
2. Acessar `zoom_manage.php`
3. Criar reuniões! 🎉

---

**Siga este guia e o problema será resolvido!**
