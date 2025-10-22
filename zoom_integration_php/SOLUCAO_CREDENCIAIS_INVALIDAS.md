# 🔴 Solução: Credenciais Inválidas (invalid_client)

## ✅ Escopos Estão CORRETOS

Seus escopos estão no formato **granular correto** do Zoom:

```
meeting:write:meeting:admin     ✅ Correto
meeting:update:meeting:admin    ✅ Correto
meeting:delete:meeting:admin    ✅ Correto
user:read:user:admin            ✅ Correto
```

**Nota:** O formato com repetição (ex: `meeting:write:meeting:admin`) é o formato granular atualizado do Zoom. Está correto!

---

## 🎯 Problema Real: Credenciais Inválidas

O erro **"invalid_client"** (HTTP 400) significa que uma ou mais credenciais estão **incorretas**:

### Credenciais Testadas:
```
Account ID: KiJeWwARQbGPJ1uhAWf-dw
Client ID: 8dSp8Ud3Q7ebqHV7L3Wrw
Client Secret: OMqOfpuOywq8mYxYaLHrUYwwRCk9CIgK
```

**Resultado:** ❌ Rejeitadas pelo Zoom

---

## 🔍 Possíveis Causas

### 1. Credenciais Copiadas de Forma Incorreta

**Problema comum:**
- Espaços extras antes ou depois
- Faltou copiar caracteres
- Copiou de lugares errados no painel

**Como verificar:**
```php
// Adicione isto no zoom_config.php temporariamente para debug:
error_log("Account ID length: " . strlen(ZOOM_ACCOUNT_ID));
error_log("Client ID length: " . strlen(ZOOM_CLIENT_ID));
error_log("Client Secret length: " . strlen(ZOOM_CLIENT_SECRET));
```

Tamanhos esperados:
- Account ID: ~22 caracteres
- Client ID: ~21 caracteres  
- Client Secret: ~32 caracteres

### 2. App Foi Desativado ou Deletado

Se alguém desativou ou deletou o app no Zoom Marketplace, as credenciais não funcionarão mais.

**Como verificar:**
1. Acesse: https://marketplace.zoom.us/user/build
2. Procure o app com Client ID: `8dSp8Ud3Q7ebqHV7L3Wrw`
3. Verifique se existe e está "Active"

### 3. Credenciais de Ambiente Errado

Se você tem múltiplas contas Zoom (pessoal e trabalho), pode ter copiado credenciais de uma conta diferente.

**Como verificar:**
- Confirme que está logado na conta correta no Zoom Marketplace
- Verifique se o app pertence a essa conta

### 4. Client Secret Expirado ou Regenerado

Se o Client Secret foi regenerado no painel mas você está usando o antigo.

**Como verificar:**
1. Acesse o app no Zoom Marketplace
2. Vá em "App Credentials"
3. Clique em "View" ou "Regenerate" no Client Secret
4. Copie o secret atual

---

## 🛠️ Soluções

### Solução 1: Verificar e Recopiar Credenciais

**Passo a passo detalhado:**

1. **Abra duas abas do navegador:**
   - Aba 1: https://marketplace.zoom.us/user/build
   - Aba 2: Seu editor de código com `zoom_config.php`

2. **No Zoom Marketplace:**
   - Clique no seu app Server-to-Server OAuth
   - Vá para "App Credentials"

3. **Copiar Account ID:**
   ```
   - Clique no botão "Copy" ao lado do Account ID
   - Cole em um editor de texto temporário (Notepad)
   - Verifique se não tem espaços antes/depois
   - Copie do Notepad para zoom_config.php
   ```

4. **Copiar Client ID:**
   ```
   - Clique no botão "Copy" ao lado do Client ID
   - Cole em um editor de texto temporário
   - Verifique se não tem espaços antes/depois
   - Copie do Notepad para zoom_config.php
   ```

5. **Copiar Client Secret:**
   ```
   - Clique em "View" ou "View and Copy"
   - Se não aparecer, clique em "Regenerate"
   - Copie IMEDIATAMENTE (só aparece uma vez)
   - Cole em um editor de texto temporário
   - Verifique se não tem espaços antes/depois
   - Copie do Notepad para zoom_config.php
   ```

6. **Formato no zoom_config.php:**
   ```php
   define('ZOOM_ACCOUNT_ID', 'sem-espacos-aqui');
   define('ZOOM_CLIENT_ID', 'sem-espacos-aqui');
   define('ZOOM_CLIENT_SECRET', 'sem-espacos-aqui');
   ```

7. **Salvar e testar:**
   ```
   http://seusite.com/live-stream/testar_credenciais.html
   ```

---

### Solução 2: Regenerar Client Secret

Se você não tem certeza se o Client Secret está correto:

1. Acesse o app no Zoom Marketplace
2. Vá em "App Credentials"
3. Procure "Client Secret"
4. Clique em **"Regenerate"**
5. Uma janela popup aparecerá com o novo secret
6. **Copie imediatamente** (só aparece uma vez!)
7. Atualize `zoom_config.php`
8. Teste novamente

---

### Solução 3: Criar Novo App (Se nada funcionar)

Se você tentou tudo e continua com erro:

1. **Criar novo app:**
   ```
   https://marketplace.zoom.us/develop/create
   ```

2. **Selecionar tipo:**
   - Escolha "Server-to-Server OAuth"
   - Clique em "Create"

3. **Preencher informações:**
   ```
   App Name: Meu Site Zoom Integration
   Company Name: [Seu nome]
   Developer Email: [Seu email]
   Short Description: Integração de reuniões
   ```

4. **Na aba "Scopes", adicionar:**
   ```
   ✓ meeting:write:meeting:admin
   ✓ meeting:read:meeting:admin
   ✓ meeting:update:meeting:admin
   ✓ meeting:delete:meeting:admin
   ✓ user:read:user:admin
   ```

5. **Ativar o app:**
   - Procure botão "Activate"
   - Clique para ativar

6. **Copiar novas credenciais:**
   - Account ID
   - Client ID
   - Client Secret (copie agora!)

7. **Testar:**
   ```
   http://seusite.com/live-stream/testar_credenciais.html
   ```

---

## 🧪 Teste Passo a Passo

### Teste 1: Verificar Formato das Credenciais

Crie arquivo `verificar_formato.php`:

```php
<?php
require_once 'zoom_config.php';

echo "<h2>Verificar Formato das Credenciais</h2>";

echo "<p><strong>Account ID:</strong></p>";
echo "<pre>" . ZOOM_ACCOUNT_ID . "</pre>";
echo "<p>Tamanho: " . strlen(ZOOM_ACCOUNT_ID) . " caracteres</p>";

echo "<p><strong>Client ID:</strong></p>";
echo "<pre>" . ZOOM_CLIENT_ID . "</pre>";
echo "<p>Tamanho: " . strlen(ZOOM_CLIENT_ID) . " caracteres</p>";

echo "<p><strong>Client Secret:</strong></p>";
echo "<pre>" . ZOOM_CLIENT_SECRET . "</pre>";
echo "<p>Tamanho: " . strlen(ZOOM_CLIENT_SECRET) . " caracteres</p>";

// Verificar espaços
$hasSpaces = false;
if (trim(ZOOM_ACCOUNT_ID) !== ZOOM_ACCOUNT_ID) {
    echo "<p style='color: red;'>❌ Account ID tem espaços extras!</p>";
    $hasSpaces = true;
}
if (trim(ZOOM_CLIENT_ID) !== ZOOM_CLIENT_ID) {
    echo "<p style='color: red;'>❌ Client ID tem espaços extras!</p>";
    $hasSpaces = true;
}
if (trim(ZOOM_CLIENT_SECRET) !== ZOOM_CLIENT_SECRET) {
    echo "<p style='color: red;'>❌ Client Secret tem espaços extras!</p>";
    $hasSpaces = true;
}

if (!$hasSpaces) {
    echo "<p style='color: green;'>✅ Nenhum espaço extra detectado</p>";
}

// Verificar caracteres especiais inválidos
if (preg_match('/[^a-zA-Z0-9_-]/', ZOOM_ACCOUNT_ID)) {
    echo "<p style='color: orange;'>⚠️ Account ID contém caracteres especiais</p>";
}
?>
```

Acesse: `http://seusite.com/live-stream/verificar_formato.php`

### Teste 2: Teste Manual via cURL

No terminal do servidor:

```bash
# Substitua as credenciais pelas suas reais
ACCOUNT_ID="KiJeWwARQbGPJ1uhAWf-dw"
CLIENT_ID="8dSp8Ud3Q7ebqHV7L3Wrw"
CLIENT_SECRET="OMqOfpuOywq8mYxYaLHrUYwwRCk9CIgK"

# Fazer requisição
curl -X POST \
  "https://zoom.us/oauth/token?grant_type=account_credentials&account_id=$ACCOUNT_ID" \
  -u "$CLIENT_ID:$CLIENT_SECRET" \
  -v
```

**Se funcionar via cURL mas não via PHP:**
- Problema na configuração do PHP
- Problema com cURL do PHP
- Verifique extensão cURL: `php -m | grep curl`

**Se não funcionar via cURL:**
- Credenciais realmente estão erradas
- App foi desativado
- Conta Zoom tem restrições

---

## 📋 Checklist de Verificação

Antes de pedir ajuda, confirme:

- [ ] Acessei o Zoom Marketplace logado na conta correta
- [ ] Encontrei o app Server-to-Server OAuth
- [ ] App está com status "Active" (não "Inactive")
- [ ] Copiei Account ID usando botão "Copy"
- [ ] Copiei Client ID usando botão "Copy"
- [ ] Copiei Client Secret (ou regenerei e copiei)
- [ ] Verifiquei que não há espaços extras
- [ ] Salvei o arquivo zoom_config.php
- [ ] Testei em `testar_credenciais.html`
- [ ] Li a resposta de erro completa

---

## 🆘 Informações para Debug

Se continuar com erro, forneça estas informações:

1. **Formato das credenciais:**
   - Execute `verificar_formato.php` e envie resultado

2. **Status do app no Zoom:**
   - Screenshot da página do app mostrando status

3. **Tipo do app:**
   - Confirme que é "Server-to-Server OAuth"

4. **Teste via cURL:**
   - Execute o comando cURL acima e envie resultado

5. **Logs do PHP:**
   ```bash
   tail -f /var/log/php_errors.log
   # ou
   tail -f /var/log/apache2/error.log
   ```

---

## 💡 Solução Mais Provável

**90% dos casos de "invalid_client" são:**

1. **Client Secret incorreto** (mais comum)
   - Solução: Regenerar e copiar novamente

2. **Espaços extras nas credenciais**
   - Solução: Copiar via botão "Copy" do Zoom

3. **App desativado**
   - Solução: Ativar no painel do Zoom

**Recomendação:** Regenere o Client Secret e teste novamente!

---

## ✅ Resultado Esperado

Após corrigir:

```
✅ Credenciais Válidas!
Token de acesso obtido com sucesso.
Token: eyJzdiI6IjAwMDAwMiIs...
Expira em: 3600 segundos
```

Então você poderá:
1. Atualizar `zoom_config.php`
2. Acessar `zoom_manage.php`
3. Criar reuniões! 🎉

---

**Foque em regenerar o Client Secret - essa é a causa mais comum!**
