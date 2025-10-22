# 🔧 ESCOPOS ZOOM - FORMATO GRANULAR CORRETO

## ⚠️ PROBLEMA IDENTIFICADO

O erro "Invalid access token, does not contain scopes" ocorre porque os escopos do Zoom agora usam **formato granular** com repetição do recurso antes do nível administrativo.

## ✅ FORMATO CORRETO DOS ESCOPOS

Na aplicação Zoom Server-to-Server OAuth, você DEVE adicionar os escopos com esta sintaxe:

```
meeting:write:meeting:admin
meeting:read:meeting:admin
meeting:update:meeting:admin
meeting:delete:meeting:admin
meeting:update:status:admin
user:read:user:admin
```

### ❌ FORMATO ANTIGO (NÃO FUNCIONA MAIS)
```
meeting:write:admin
meeting:read:admin
user:read:admin
```

### ✅ FORMATO NOVO (CORRETO)
```
meeting:write:meeting:admin    ← Note a repetição de "meeting"
meeting:read:meeting:admin
user:read:user:admin           ← Note a repetição de "user"
```

---

## 📋 PASSO A PASSO PARA ADICIONAR OS ESCOPOS

### 1. Acessar o Zoom App Marketplace

1. Acesse: https://marketplace.zoom.us/
2. Faça login com sua conta Zoom
3. Clique em **"Manage"** (no canto superior direito)
4. Clique em **"Build App"**
5. Localize sua aplicação **Server-to-Server OAuth** criada
6. Clique no nome da aplicação para abrir as configurações

---

### 2. Adicionar os Escopos Granulares

1. No menu lateral esquerdo, clique em **"Scopes"**
2. Você verá uma lista de categorias de escopos disponíveis
3. **Expanda cada categoria** e marque os seguintes escopos:

#### 📹 Meeting Scopes (Escopos de Reunião)

Na seção **"Meeting"**, marque:

- ✅ `meeting:read:meeting:admin`
  - **Descrição**: View and manage all user meetings
  - **Necessário para**: Buscar informações de reuniões

- ✅ `meeting:write:meeting:admin`
  - **Descrição**: Create meetings on behalf of another user
  - **Necessário para**: Criar novas reuniões

- ✅ `meeting:update:meeting:admin`
  - **Descrição**: Update meetings on behalf of another user
  - **Necessário para**: Atualizar reuniões existentes

- ✅ `meeting:delete:meeting:admin`
  - **Descrição**: Delete meetings on behalf of another user
  - **Necessário para**: Deletar reuniões

- ✅ `meeting:update:status:admin` (OPCIONAL)
  - **Descrição**: Update meeting status
  - **Necessário para**: Atualizar status de reuniões (ex: finalizar)

#### 👤 User Scopes (Escopos de Usuário)

Na seção **"User"**, marque:

- ✅ `user:read:user:admin`
  - **Descrição**: View all user information
  - **Necessário para**: Obter informações do host (necessário para criar reuniões)

---

### 3. Salvar as Alterações

1. Após marcar todos os escopos, role até o final da página
2. Clique em **"Continue"** ou **"Save"**
3. Pode aparecer uma mensagem informando que os escopos foram atualizados
4. **IMPORTANTE**: Aguarde alguns minutos (2-5 minutos) para que as alterações sejam propagadas nos servidores do Zoom

---

### 4. Verificar se os Escopos foram Aplicados

#### Opção 1: Via Painel Zoom
1. Volte para a seção **"Scopes"** da sua aplicação
2. Verifique se todos os escopos listados acima estão marcados
3. Verifique se aparece "Admin-level" ao lado de cada escopo

#### Opção 2: Via Script PHP (Arquivo incluído)
1. Use o arquivo `verificar_escopos.php` (fornecido abaixo)
2. Faça upload para seu servidor
3. Acesse via navegador: `https://seudominio.com/zoom_integration_php/verificar_escopos.php`

---

## 🔍 LISTA COMPLETA DE ESCOPOS NECESSÁRIOS

### Escopos OBRIGATÓRIOS (para funcionamento básico)
```
meeting:write:meeting:admin
meeting:read:meeting:admin
user:read:user:admin
```

### Escopos RECOMENDADOS (para funcionalidade completa)
```
meeting:write:meeting:admin
meeting:read:meeting:admin
meeting:update:meeting:admin
meeting:delete:meeting:admin
meeting:update:status:admin
user:read:user:admin
user:write:user:admin (se precisar criar/atualizar usuários)
```

---

## 🧪 TESTAR APÓS CONFIGURAR

### 1. Aguardar Propagação
- Aguarde **2-5 minutos** após salvar os escopos
- Os servidores do Zoom precisam propagar as alterações

### 2. Limpar Cache de Token
- Delete o arquivo de cache: `/tmp/zoom_token_cache.json`
- Ou execute:
```bash
rm /tmp/zoom_token_cache.json
```

### 3. Testar Criação de Reunião
- Acesse `zoom_manage.php`
- Tente criar uma nova reunião de teste
- Se funcionar, os escopos estão corretos!

---

## ⚡ SOLUÇÃO RÁPIDA

Se você está com pressa, copie esta lista e cole diretamente no campo de busca de escopos:

```
meeting:write:meeting:admin
meeting:read:meeting:admin
meeting:update:meeting:admin
meeting:delete:meeting:admin
meeting:update:status:admin
user:read:user:admin
user:write:user:admin
```

---

## 🆘 AINDA NÃO FUNCIONA?

### Checklist de Verificação:

- [ ] Você está usando uma aplicação **Server-to-Server OAuth** (não JWT)
- [ ] Todos os escopos estão marcados com o formato granular correto
- [ ] Você aguardou 2-5 minutos após salvar
- [ ] Você deletou o cache de token (`/tmp/zoom_token_cache.json`)
- [ ] Suas credenciais (Account ID, Client ID, Client Secret) estão corretas no `zoom_config.php`
- [ ] Sua conta Zoom tem permissões de administrador

### Comandos de Diagnóstico:

```bash
# Verificar se o arquivo de cache existe
ls -la /tmp/zoom_token_cache.json

# Deletar cache
rm /tmp/zoom_token_cache.json

# Ver logs de erro do PHP
tail -f /var/log/apache2/error.log
# ou
tail -f /var/log/php-fpm/error.log
```

---

## 📞 CONTATO ZOOM SUPPORT

Se após seguir todos os passos o problema persistir:

- Email: developersupport@zoom.us
- Forum: https://devforum.zoom.us/
- Docs: https://developers.zoom.com/docs/api/rest/reference/

---

**Última atualização**: Dezembro 2024
**Versão da API Zoom**: v2
**Tipo de App**: Server-to-Server OAuth