# 🚨 INSTRUÇÕES URGENTES - ZOOM ESCOPOS

## ⚠️ PROBLEMA IDENTIFICADO

Você configurou os escopos corretamente, MAS:

1. **FALTA O ESCOPO MAIS IMPORTANTE**: `meeting:write:meeting:admin` (para CRIAR reuniões)
2. **Cache do token antigo** ainda está ativo

---

## ✅ ESCOPOS QUE VOCÊ JÁ TEM (CORRETOS)

```
✅ meeting:read:list_meetings:admin
✅ meeting:read:meeting:admin
✅ meeting:delete:meeting:admin
✅ meeting:update:meeting:admin
✅ meeting:update:status:admin
✅ user:read:list_users:admin
✅ user:read:user:admin
✅ user:delete:user:admin
✅ user:write:user:admin
```

---

## ❌ ESCOPO QUE ESTÁ FALTANDO (CRÍTICO)

### Você PRECISA adicionar:

```
❌ meeting:write:meeting:admin
```

**Este escopo é OBRIGATÓRIO para criar reuniões!**

### Como adicionar:

1. Acesse: https://marketplace.zoom.us/
2. Vá em **Manage → Build App → [Sua App] → Scopes**
3. Procure na seção **"Meeting"**
4. Marque: `meeting:write:meeting:admin`
   - Descrição: "Create meetings on behalf of another user"
5. Clique em **Save/Continue**

---

## 🔧 LIMPAR CACHE DO TOKEN (OBRIGATÓRIO)

Após adicionar o escopo faltante, você DEVE limpar o cache:

### No seu servidor PHP:

```bash
# Deletar cache de token
rm /tmp/zoom_token_cache.json

# Verificar se deletou
ls -la /tmp/zoom_token_cache.json
# Deve retornar: "No such file or directory"
```

### Ou via PHP:

Crie um arquivo `limpar_cache.php`:

```php
<?php
$cacheFile = sys_get_temp_dir() . '/zoom_token_cache.json';

if (file_exists($cacheFile)) {
    unlink($cacheFile);
    echo "✅ Cache deletado com sucesso!";
} else {
    echo "ℹ️ Cache não existe (já estava limpo)";
}
?>
```

Acesse no navegador: `https://seudominio.com/zoom_integration_php/limpar_cache.php`

---

## 📝 LISTA COMPLETA DE ESCOPOS (INCLUINDO O QUE FALTA)

Após adicionar `meeting:write:meeting:admin`, sua lista final deve ser:

### Meeting Scopes:
```
✅ meeting:write:meeting:admin         ← ADICIONAR ESTE!
✅ meeting:read:meeting:admin
✅ meeting:read:list_meetings:admin
✅ meeting:update:meeting:admin
✅ meeting:delete:meeting:admin
✅ meeting:update:status:admin
```

### User Scopes:
```
✅ user:read:user:admin
✅ user:read:list_users:admin
✅ user:write:user:admin
✅ user:delete:user:admin (opcional)
```

---

## 🎯 PASSO A PASSO COMPLETO

### 1. Adicionar escopo faltante
- Acesse Zoom App Marketplace
- Adicione `meeting:write:meeting:admin`
- Salve

### 2. Aguardar propagação
- Aguarde **2-5 minutos**
- O Zoom precisa propagar as alterações

### 3. Limpar cache
```bash
rm /tmp/zoom_token_cache.json
```

### 4. Verificar novamente
- Acesse: `verificar_escopos.php` no navegador
- Ou teste criar uma reunião em `zoom_manage.php`

---

## 🔍 ENTENDENDO OS ESCOPOS "LIST" vs "READ"

O Zoom tem escopos separados para:

- **LIST** (listar múltiplos): `meeting:read:list_meetings:admin`
- **READ** (ler individual): `meeting:read:meeting:admin`

**Ambos são necessários!** Por isso você precisa dos dois.

O mesmo vale para usuários:
- `user:read:list_users:admin` - para listar usuários
- `user:read:user:admin` - para ler um usuário específico

---

## ✨ DEPOIS DE CONCLUIR

Você poderá:

✅ Criar reuniões via `zoom_manage.php`
✅ Listar reuniões
✅ Atualizar reuniões
✅ Deletar reuniões
✅ Obter informações de usuários

---

## 💡 VERIFICAÇÃO RÁPIDA

Execute no terminal do seu servidor:

```bash
# Ver se cache existe
ls -la /tmp/zoom_token_cache.json

# Se existir, deletar
rm /tmp/zoom_token_cache.json

# Confirmar que foi deletado
ls -la /tmp/zoom_token_cache.json
```

---

## 📞 AINDA COM PROBLEMAS?

Se após seguir todos os passos ainda tiver erro:

1. Verifique se salvou as alterações no Zoom
2. Aguarde mais 5 minutos
3. Limpe o cache novamente
4. Tente em uma janela anônima do navegador
5. Verifique os logs: `tail -f /var/log/php-fpm/error.log`

---

**IMPORTANTE**: O escopo `meeting:write:meeting:admin` é ESSENCIAL. Sem ele, você não conseguirá criar reuniões!
