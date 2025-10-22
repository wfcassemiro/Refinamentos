# 🔧 Como Configurar Escopos no Zoom Marketplace

Se você recebeu o erro "Não foi possível obter informações do usuário", siga este guia para adicionar os escopos necessários ao seu app Zoom.

---

## 📋 O Que São Escopos?

Escopos (Scopes) são permissões que seu aplicativo precisa para acessar diferentes recursos da API do Zoom. Por padrão, um app pode não ter todos os escopos necessários ativados.

---

## 🚀 Passo a Passo para Adicionar Escopos

### 1. Acessar Zoom Marketplace

Acesse: https://marketplace.zoom.us/

### 2. Fazer Login

Use suas credenciais do Zoom (mesmo login que você usa para reuniões).

### 3. Ir para "Manage" → "Build App"

- Clique no seu nome no canto superior direito
- Selecione **"Manage"**
- Clique em **"Build App"** no menu lateral

### 4. Localizar Seu Aplicativo

Você deve ver seu app **Server-to-Server OAuth** na lista. Clique nele para abrir as configurações.

**Credenciais do seu app:**
- Account ID: `KiJeWwARQbGPJ1uhAWf-dw`
- Client ID: `8dSp8Ud3Q7ebqHV7L3Wrw`

### 5. Adicionar Escopos Necessários

Na página do app, procure pela aba/seção **"Scopes"** e adicione os seguintes escopos:

#### ✅ Escopos Obrigatórios:

```
✓ meeting:write:admin          - Criar reuniões
✓ meeting:read:admin           - Ler informações de reuniões
✓ meeting:update:admin         - Atualizar reuniões
✓ meeting:delete:admin         - Deletar reuniões
```

#### ✅ Escopos Recomendados (para funcionalidade completa):

```
✓ user:read:admin              - Ler informações de usuários
✓ user:write:admin             - Gerenciar usuários
✓ recording:read:admin         - Acessar gravações (opcional)
```

### 6. Clicar em "Add" para cada escopo

Depois de selecionar todos os escopos necessários, clique em **"Add"** ou **"Save"** no final da página.

### 7. Ativar o Aplicativo

Certifique-se de que o app está **Ativado**:

- Na página do app, procure por um botão **"Activate"** ou status
- Se estiver como "Inactive", clique para ativar
- O status deve mostrar **"Active"** ou **"Published"**

---

## 🔄 Após Adicionar os Escopos

### Limpar Cache do Token

O token antigo pode não ter os novos escopos. Delete o cache:

**No servidor via terminal:**
```bash
rm /tmp/zoom_token_cache.json
```

**Ou no PHP:**
```php
<?php
unlink(sys_get_temp_dir() . '/zoom_token_cache.json');
echo "Cache limpo!";
?>
```

### Executar install.php Novamente

Acesse: `http://seusite.com/install.php`

Agora a Etapa 4 deve passar com sucesso! ✅

---

## 📸 Guia Visual

### Como Encontrar a Seção de Escopos

```
Zoom Marketplace
└── Manage
    └── Build App
        └── [Seu App]
            └── Scopes  ← Clique aqui
                ├── Add Scopes
                └── [Lista de escopos disponíveis]
```

### Interface do Zoom Marketplace

1. **Aba "Scopes"**
   - Lista todos os escopos disponíveis
   - Use a busca para encontrar rapidamente
   - Exemplo: pesquise "meeting" para ver todos os escopos de reunião

2. **Adicionar Escopo**
   - Marque o checkbox ao lado do escopo
   - Clique em "Add" ou "Save"
   - O escopo aparecerá na lista "Added scopes"

3. **Salvar Alterações**
   - Sempre clique em "Continue" ou "Save" no final
   - Aguarde a confirmação de que foi salvo

---

## ❓ Perguntas Frequentes

### 1. Não consigo ver a opção "Scopes"

**R:** Verifique se você está editando um app **Server-to-Server OAuth**. Outros tipos de app têm interfaces diferentes.

### 2. Os escopos não aparecem mesmo depois de adicionar

**R:** Limpe o cache do token e aguarde alguns minutos. Às vezes leva um tempo para propagar.

### 3. Erro "Forbidden" ao adicionar escopos

**R:** Você precisa ter permissão de **Admin** na conta Zoom. Entre em contato com o administrador da conta.

### 4. App não tem opção "Activate"

**R:** Apps Server-to-Server OAuth são ativados automaticamente após configuração. Verifique se os campos obrigatórios estão preenchidos.

---

## ⚠️ Importante: O App Funciona Mesmo Sem Todos os Escopos!

**Boa notícia:** A integração foi configurada para funcionar mesmo sem acesso às informações do usuário!

### O que funciona SEM o escopo `user:read:admin`:

✅ Criar reuniões  
✅ Adicionar reuniões existentes  
✅ Obter detalhes de reunião  
✅ Atualizar reuniões  
✅ Deletar reuniões  
✅ Listar reuniões  

### O que NÃO funciona:

❌ Ver nome e email do host na instalação (não é crítico)  
❌ Alguns detalhes avançados de usuário (raramente usado)  

**Conclusão:** Você pode continuar usando normalmente! Os escopos de usuário são apenas para exibição de informações extras.

---

## 🧪 Testar se os Escopos Foram Aplicados

Crie um arquivo `test_zoom.php`:

```php
<?php
require_once 'zoom_auth.php';
require_once 'zoom_functions.php';

// Limpar cache
@unlink(sys_get_temp_dir() . '/zoom_token_cache.json');

// Obter novo token
$token = getZoomAccessToken();

if ($token) {
    echo "✅ Token obtido: " . substr($token, 0, 20) . "...\n\n";
    
    // Testar usuário
    $user = getZoomUser();
    if ($user) {
        echo "✅ Usuário: " . $user['email'] . "\n\n";
    } else {
        echo "⚠️ Não conseguiu obter usuário (mas não é crítico)\n\n";
    }
    
    // Testar criar reunião
    $result = createZoomMeeting(
        'Reunião de Teste',
        date('Y-m-d H:i:s', strtotime('+1 hour')),
        30,
        'Teste da API'
    );
    
    if ($result['success']) {
        echo "✅ Reunião criada com sucesso!\n";
        echo "ID: " . $result['meeting']['id'] . "\n";
        echo "Link: " . $result['meeting']['join_url'] . "\n";
        
        // Deletar reunião de teste
        deleteZoomMeeting($result['meeting']['id']);
        echo "\n✅ Reunião de teste deletada\n";
    } else {
        echo "❌ Erro ao criar reunião: " . $result['error'] . "\n";
    }
} else {
    echo "❌ Erro ao obter token\n";
}
?>
```

Execute: `http://seusite.com/test_zoom.php`

Se criar reunião funcionar, está tudo OK! ✅

---

## 📞 Suporte Zoom

Se continuar tendo problemas:

- **Documentação:** https://developers.zoom.us/docs/api/
- **Suporte:** https://support.zoom.us/
- **Forum:** https://devforum.zoom.us/

---

## ✅ Checklist Final

- [ ] Acessei Zoom Marketplace
- [ ] Localizei meu app Server-to-Server OAuth
- [ ] Adicionei escopos de `meeting:*:admin`
- [ ] (Opcional) Adicionei escopo `user:read:admin`
- [ ] Salvei as alterações
- [ ] Verifiquei que app está Ativo
- [ ] Limpei cache do token
- [ ] Executei install.php novamente
- [ ] Testei criar uma reunião

**Se todos os itens estiverem marcados, sua integração está 100% funcional! 🎉**
