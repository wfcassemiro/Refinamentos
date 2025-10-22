# 🔧 Como Resolver: "Invalid access token"

## 🎯 Diagnóstico Rápido

Execute este arquivo para testar sua autenticação:

```
http://seusite.com/live-stream/test_zoom_auth.php
```

Este script vai:
1. ✅ Verificar suas credenciais
2. ✅ Tentar obter um token
3. ✅ Testar o token na API
4. ✅ Mostrar exatamente onde está o problema

---

## 🔍 Causas Comuns

### 1. Credenciais Incorretas

**Sintomas:**
- Erro 401 ao obter token
- Mensagem "Invalid client"

**Solução:**

Verifique as credenciais no arquivo `zoom_config.php`:

```php
define('ZOOM_ACCOUNT_ID', 'KiJeWwARQbGPJ1uhAWf-dw');
define('ZOOM_CLIENT_ID', '8dSp8Ud3Q7ebqHV7L3Wrw');
define('ZOOM_CLIENT_SECRET', 'OMqOfpuOywq8mYxYaLHrUYwwRCk9CIgK');
```

Para confirmar:
1. Acesse https://marketplace.zoom.us/
2. Vá em **Manage** → **Build App**
3. Clique no seu app Server-to-Server OAuth
4. Compare **Account ID**, **Client ID** e **Client Secret**

---

### 2. App Não Ativado

**Sintomas:**
- Token obtido mas erro 401 ao usar
- Mensagem "App has been deactivated"

**Solução:**

1. Acesse https://marketplace.zoom.us/
2. Vá em **Manage** → **Build App**
3. Clique no seu app
4. Procure por um botão **"Activate"** ou verifique o status
5. Se estiver "Inactive", clique para ativar
6. Salve as alterações

---

### 3. Falta de Escopos (Scopes)

**Sintomas:**
- Token válido mas erro ao adicionar reunião
- Mensagem "Missing required scope"

**Solução:**

Adicione os escopos necessários:

1. Acesse https://marketplace.zoom.us/
2. Vá para seu app Server-to-Server OAuth
3. Clique na aba **"Scopes"**
4. Adicione estes escopos:

```
✓ meeting:write:admin
✓ meeting:read:admin
✓ meeting:update:admin
✓ meeting:delete:admin
✓ user:read:admin (opcional, mas recomendado)
```

5. Clique em **"Add"** para cada escopo
6. **Salve** as alterações
7. **Limpe o cache do token** (ver abaixo)

---

### 4. Token em Cache Expirado

**Sintomas:**
- Funcionava antes mas parou
- Erro "Invalid access token"

**Solução:**

Limpe o cache do token:

**Opção 1: Via PHP**

Crie um arquivo `clear_cache.php`:

```php
<?php
$cacheFile = sys_get_temp_dir() . '/zoom_token_cache.json';
if (file_exists($cacheFile)) {
    unlink($cacheFile);
    echo "✅ Cache limpo com sucesso!";
} else {
    echo "ℹ️ Nenhum cache encontrado";
}
?>
```

Acesse: `http://seusite.com/live-stream/clear_cache.php`

**Opção 2: Via Servidor**

```bash
rm /tmp/zoom_token_cache.json
```

**Opção 3: Via test_zoom_auth.php**

O arquivo `test_zoom_auth.php` já limpa o cache automaticamente.

---

### 5. Problema de SSL/Certificados

**Sintomas:**
- Erro "SSL certificate problem"
- Erro de conexão cURL

**Solução:**

**Temporariamente** (apenas para testar), você pode desabilitar verificação SSL:

Em `zoom_auth.php`, altere:

```php
CURLOPT_SSL_VERIFYPEER => false,  // Apenas para teste!
```

⚠️ **ATENÇÃO:** Nunca use isso em produção! Se funcionar assim, o problema é com os certificados SSL do seu servidor.

**Solução Permanente:**
1. Atualize os certificados CA do seu servidor
2. Ou configure corretamente o caminho para os certificados

---

## 🧪 Passos para Resolver

### Passo 1: Execute o Diagnóstico

```
http://seusite.com/live-stream/test_zoom_auth.php
```

Isso mostrará exatamente qual é o problema.

### Passo 2: Verifique as Credenciais

Se o teste mostrar erro 401, suas credenciais estão incorretas.

1. Vá para https://marketplace.zoom.us/
2. Copie as credenciais corretas
3. Atualize `zoom_config.php`

### Passo 3: Verifique o App

1. Certifique-se que o app está **ativado**
2. Verifique se os **escopos** estão adicionados

### Passo 4: Limpe o Cache

```php
<?php
unlink(sys_get_temp_dir() . '/zoom_token_cache.json');
?>
```

### Passo 5: Teste Novamente

1. Execute `test_zoom_auth.php` novamente
2. Se passar todos os testes, acesse `zoom_manage.php`
3. Tente adicionar uma reunião

---

## 📋 Checklist de Verificação

Antes de tentar novamente, confirme:

- [ ] **Account ID** está correto em `zoom_config.php`
- [ ] **Client ID** está correto em `zoom_config.php`
- [ ] **Client Secret** está correto em `zoom_config.php`
- [ ] App Server-to-Server OAuth está **ativado** no Zoom Marketplace
- [ ] Escopos `meeting:write:admin` e `meeting:read:admin` estão adicionados
- [ ] Cache do token foi limpo
- [ ] `test_zoom_auth.php` passa todos os testes

---

## 🆘 Teste Manual da API

Se ainda não funcionar, teste manualmente via terminal:

```bash
# 1. Obter token
curl -X POST "https://zoom.us/oauth/token?grant_type=account_credentials&account_id=KiJeWwARQbGPJ1uhAWf-dw" \
  -H "Authorization: Basic $(echo -n '8dSp8Ud3Q7ebqHV7L3Wrw:OMqOfpuOywq8mYxYaLHrUYwwRCk9CIgK' | base64)"

# Isso deve retornar algo como:
# {"access_token":"eyJz...", "expires_in":3600}
```

Se isso funcionar mas o PHP não, o problema é com a configuração do PHP/cURL no servidor.

---

## 💡 Dicas Finais

### Verificar Logs do PHP

Adicione isto no início de `zoom_manage.php`:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

Isso mostrará todos os erros PHP.

### Verificar Logs de Erro

Os arquivos `zoom_auth.php` e `zoom_functions.php` agora fazem log de todas as operações.

Verifique os logs do PHP:

```bash
tail -f /var/log/php_errors.log
# ou
tail -f /var/log/apache2/error.log
# ou
tail -f /var/log/nginx/error.log
```

### Testar com cURL Direto

No `zoom_manage.php`, adicione debug temporário:

```php
if ($_POST['action'] === 'add_existing') {
    // Debug
    error_log("Tentando adicionar reunião: " . $_POST['meeting_id_or_url']);
    
    $result = addExistingMeeting($_POST['meeting_id_or_url']);
    
    // Debug
    error_log("Resultado: " . json_encode($result));
}
```

---

## 🎯 Resultado Esperado

Após seguir todos os passos:

**`test_zoom_auth.php` deve mostrar:**

```
✅ Token obtido com sucesso!
✅ API funcionando! Encontrados X usuários
✅ Consegue listar reuniões!
```

**`zoom_manage.php` deve:**

- ✅ Carregar sem erros
- ✅ Permitir criar reuniões
- ✅ Permitir adicionar reuniões existentes

---

## 📞 Suporte Zoom

Se nada funcionar, pode ser um problema na conta Zoom:

- **Documentação:** https://developers.zoom.us/docs/api/
- **Suporte:** https://support.zoom.us/
- **Status da API:** https://status.zoom.us/

Verifique se não há nenhum problema reportado na API do Zoom.

---

## ✅ Solução Mais Comum

Na maioria dos casos, o problema é:

1. **Escopos faltando** - Adicione `meeting:write:admin` e `meeting:read:admin`
2. **Cache de token** - Limpe com `test_zoom_auth.php`
3. **App não ativado** - Ative no Zoom Marketplace

Execute `test_zoom_auth.php` e siga as instruções que ele mostrar!
