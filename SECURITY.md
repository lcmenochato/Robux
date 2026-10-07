# 🔒 Guia de Segurança - Robux

## ⚠️ IMPORTANTE: Correção de Segurança Aplicada

O arquivo `config/config.php` foi **removido do controle de versão Git** para proteger suas credenciais.

### O que foi feito:

1. ✅ `config.php` foi removido do rastreamento Git
2. ✅ Adicionado ao `.gitignore` para prevenir commits futuros
3. ✅ Criado `config.example.php` como template
4. ✅ Documentação de segurança adicionada

---

## 🚨 Ação Necessária

### Se você já fez deploy com credenciais expostas:

1. **TROQUE IMEDIATAMENTE as senhas do banco de dados**
2. **Revogue e recrie API keys comprometidas**
3. **Revise logs de acesso** para atividades suspeitas
4. **Notifique sua equipe** sobre a exposição

### Para novos deploys:

1. Copie `config.example.php` para `config.php`
2. Configure com credenciais reais
3. Nunca commite `config.php`

---

## 🛡️ Boas Práticas de Segurança

### 1. Arquivos Sensíveis

**NUNCA** commite no Git:
- ❌ `config/config.php`
- ❌ `.env` (arquivos de ambiente)
- ❌ Arquivos com senhas ou API keys
- ❌ Certificados SSL privados
- ❌ Backups de banco de dados
- ❌ Logs com informações sensíveis

### 2. Senhas Fortes

Use senhas complexas para:
- Banco de dados
- FTP/SFTP
- SSH
- Admin do sistema
- APIs externas

**Exemplo de senha forte:**
```
K9$mP2@nL5!qR8#wT4
```

**Geradores recomendados:**
- 1Password
- LastPass
- Bitwarden
- `openssl rand -base64 32`

### 3. Configuração de Produção

#### config.php (Produção):
```php
<?php
// Use credenciais seguras
$host = 'localhost';
$user = 'latam_prod_user';
$password = 'K9$mP2@nL5!qR8#wT4';  // Senha forte!
$dbname = 'latam_production';

// SEMPRE use PDO (mais seguro)
$conexao_tipo = 'pdo';

// Configurações de segurança
error_reporting(0);  // Não mostrar erros em produção
ini_set('display_errors', 0);
?>
```

#### .htaccess (Já configurado):
```apache
# Bloquear acesso a arquivos sensíveis
<FilesMatch "\.(htaccess|htpasswd|ini|log|sh|inc|bak|save|sql|env|config)$">
    Require all denied
</FilesMatch>
```

### 4. HTTPS Obrigatório

Configure SSL/TLS em produção:

```bash
# Com Certbot (Let's Encrypt - Grátis)
sudo certbot --apache -d seudominio.com -d www.seudominio.com
```

Adicione no `.htaccess` (início):
```apache
# Forçar HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 5. Permissões de Arquivo

```bash
# Pastas: 755 (rwxr-xr-x)
find /var/www/html/robux -type d -exec chmod 755 {} \;

# Arquivos: 644 (rw-r--r--)
find /var/www/html/robux -type f -exec chmod 644 {} \;

# config.php: 600 (rw-------)
chmod 600 /var/www/html/robux/config/config.php

# Proprietário
chown -R www-data:www-data /var/www/html/robux
```

### 6. Proteção contra SQL Injection

O projeto já usa PDO com Prepared Statements. Exemplo correto:

```php
// ✅ CORRETO - Usa prepared statements
$stmt = $conexao->prepare("SELECT * FROM links WHERE fullid = :fullid");
$stmt->execute([':fullid' => $fullId]);

// ❌ ERRADO - Vulnerável a SQL Injection
$query = "SELECT * FROM links WHERE fullid = '$fullId'";
```

### 7. Validação de Entrada

Sempre valide e sanitize inputs:

```php
// Validar e sanitizar
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$nome = htmlspecialchars($_POST['nome'], ENT_QUOTES, 'UTF-8');
$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

// Verificar antes de usar
if ($email === false) {
    die('Email inválido');
}
```

### 8. Proteção de Sessão

```php
// Configurações seguras de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);  // Apenas HTTPS
ini_set('session.use_only_cookies', 1);
session_start();

// Regenerar ID após login
session_regenerate_id(true);
```

### 9. Headers de Segurança

Adicione ao PHP ou `.htaccess`:

```php
// Headers de segurança
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'");
```

### 10. Logs e Monitoramento

```php
// Configurar logs personalizados
error_log("Erro crítico: " . $mensagem, 3, "/var/log/app/errors.log");

// Monitorar tentativas de acesso suspeitas
if ($tentativas_falhas > 5) {
    error_log("IP bloqueado: " . $_SERVER['REMOTE_ADDR']);
    die('Muitas tentativas. Tente novamente mais tarde.');
}
```

---

## 🔍 Checklist de Segurança

Use antes de cada deploy:

### Desenvolvimento Local:
- [ ] `config.php` não está no Git
- [ ] `.env` não está no Git
- [ ] Senhas de teste fracas (ok para local)
- [ ] Display errors habilitado (ok para debug)

### Staging/Homologação:
- [ ] Credenciais diferentes de produção
- [ ] HTTPS configurado
- [ ] Display errors desabilitado
- [ ] Logs habilitados

### Produção:
- [ ] Senhas fortes e únicas
- [ ] HTTPS obrigatório
- [ ] Display errors desabilitado
- [ ] Logs configurados e monitorados
- [ ] Backup automático habilitado
- [ ] Firewall configurado
- [ ] Rate limiting ativo
- [ ] SSL A+ no SSLLabs
- [ ] Headers de segurança configurados
- [ ] Permissões de arquivo corretas

---

## 🚨 Incidentes de Segurança

### Se detectar um problema:

1. **Isole o sistema**
   - Tire o site do ar temporariamente se necessário
   
2. **Avalie o dano**
   - Verifique logs de acesso
   - Identifique dados comprometidos
   
3. **Corrija a vulnerabilidade**
   - Aplique patch imediatamente
   - Troque todas as credenciais
   
4. **Notifique**
   - Informe usuários afetados
   - Documente o incidente
   
5. **Previna reincidência**
   - Implemente monitoramento adicional
   - Revise processos de segurança

---

## 🔐 Gerenciamento de Secrets no GitHub

Para deploy via GitHub Actions, use **Secrets** (nunca variáveis):

1. Settings > Secrets and variables > Actions
2. New repository secret
3. Nunca use secrets em logs ou echo

```yaml
# ✅ CORRETO
- name: Deploy
  env:
    PASSWORD: ${{ secrets.FTP_PASSWORD }}
  run: deploy.sh

# ❌ ERRADO
- name: Deploy
  run: echo "Senha: ${{ secrets.FTP_PASSWORD }}"
```

---

## 📚 Recursos Adicionais

### Ferramentas de Segurança:

- **OWASP ZAP** - Scanner de vulnerabilidades
- **SQLMap** - Teste SQL Injection
- **Burp Suite** - Testes de penetração
- **SSLLabs** - Teste SSL/TLS
- **SecurityHeaders.com** - Teste headers

### Links Úteis:

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://phptherightway.com/#security)
- [MySQL Security](https://dev.mysql.com/doc/refman/8.0/en/security.html)
- [Apache Security Tips](https://httpd.apache.org/docs/2.4/misc/security_tips.html)

---

## 🆘 Suporte de Segurança

Se encontrar uma vulnerabilidade:

1. **NÃO** abra issue pública
2. Envie email para: [ADICIONAR EMAIL DE SEGURANÇA]
3. Ou use: [Security Advisory](https://github.com/lcmenochato/robux/security/advisories)

---

**Última atualização**: Outubro 2026

**Versão do documento**: 1.0

---

> ⚠️ **LEMBRE-SE**: Segurança é um processo contínuo, não um destino. Revise regularmente!
