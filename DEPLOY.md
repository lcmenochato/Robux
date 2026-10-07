# 📦 Guia de Deploy - Robux

Este guia contém instruções detalhadas para fazer deploy da aplicação em diferentes ambientes.

## 📋 Pré-requisitos

Antes de começar o deploy, certifique-se de ter:

- [ ] Servidor web (Apache/Nginx) configurado
- [ ] PHP 7.4 ou superior instalado
- [ ] MySQL 5.7 ou superior instalado
- [ ] Acesso SSH ao servidor (para VPS/Dedicado)
- [ ] Credenciais de banco de dados
- [ ] Domínio configurado (opcional)

## 🚀 Opções de Deploy

### Opção 1: Deploy em Hospedagem Compartilhada (cPanel)

#### Passo 1: Preparar os arquivos

```bash
# No seu computador local
git clone https://github.com/lcmenochato/robux.git
cd robux
```

#### Passo 2: Fazer upload via FTP/File Manager

1. Acesse o cPanel da sua hospedagem
2. Vá em "Gerenciador de Arquivos"
3. Navegue até `public_html` (ou a pasta do seu domínio)
4. Faça upload de todos os arquivos do projeto

#### Passo 3: Configurar banco de dados

1. No cPanel, vá em "MySQL Databases"
2. Crie um novo banco de dados
3. Crie um usuário e senha
4. Adicione o usuário ao banco com todos os privilégios
5. Vá em "phpMyAdmin"
6. Selecione o banco criado
7. Importe o arquivo `latam.sql`

#### Passo 4: Configurar credenciais

1. No File Manager, copie `config/config.example.php` para `config/config.php`
2. Edite `config/config.php` com as credenciais do banco:

```php
$host = 'localhost';
$user = 'seu_usuario_mysql';
$password = 'sua_senha_mysql';
$dbname = 'seu_banco_mysql';
```

#### Passo 5: Verificar .htaccess

Certifique-se de que o arquivo `.htaccess` foi enviado corretamente. Se não aparecer:
1. Habilite "Mostrar arquivos ocultos" no File Manager
2. Verifique se o mod_rewrite está ativo

---

### Opção 2: Deploy em VPS/Servidor Dedicado (Ubuntu)

#### Passo 1: Preparar o servidor

```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Apache, PHP e MySQL
sudo apt install apache2 php php-mysql php-xml php-curl php-mbstring mysql-server -y

# Habilitar mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Passo 2: Clonar repositório

```bash
# Navegar para a pasta do servidor
cd /var/www/html

# Clonar o projeto
sudo git clone https://github.com/lcmenochato/robux.git
cd robux

# Ajustar permissões
sudo chown -R www-data:www-data /var/www/html/robux
sudo chmod -R 755 /var/www/html/robux
```

#### Passo 3: Configurar MySQL

```bash
# Acessar MySQL
sudo mysql -u root -p

# Dentro do MySQL:
CREATE DATABASE latam CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'latam_user'@'localhost' IDENTIFIED BY 'senha_segura_aqui';
GRANT ALL PRIVILEGES ON latam.* TO 'latam_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Importar banco de dados
sudo mysql -u root -p latam < latam.sql
```

#### Passo 4: Configurar aplicação

```bash
# Copiar arquivo de configuração
sudo cp config/config.example.php config/config.php

# Editar configuração
sudo nano config/config.php
```

Ajuste as credenciais:

```php
$host = 'localhost';
$user = 'latam_user';
$password = 'senha_segura_aqui';
$dbname = 'latam';
```

#### Passo 5: Configurar VirtualHost

```bash
# Criar configuração do site
sudo nano /etc/apache2/sites-available/robux.conf
```

Adicione:

```apache
<VirtualHost *:80>
    ServerName seu-dominio.com
    ServerAlias www.seu-dominio.com
    DocumentRoot /var/www/html/robux

    <Directory /var/www/html/robux>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/robux_error.log
    CustomLog ${APACHE_LOG_DIR}/robux_access.log combined
</VirtualHost>
```

```bash
# Habilitar site
sudo a2ensite robux.conf
sudo systemctl reload apache2
```

#### Passo 6: Configurar SSL (Recomendado)

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-apache -y

# Obter certificado SSL
sudo certbot --apache -d seu-dominio.com -d www.seu-dominio.com
```

---

### Opção 3: Deploy Automatizado via GitHub Actions

#### Criar workflow para deploy via FTP

Crie o arquivo `.github/workflows/deploy.yml`:

```yaml
name: Deploy via FTP

on:
  push:
    branches: [ main ]
  workflow_dispatch:

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Checkout code
      uses: actions/checkout@v3
      
    - name: Deploy via FTP
      uses: SamKirkland/FTP-Deploy-Action@4.3.0
      with:
        server: ftp.seuservidor.com
        username: ${{ secrets.FTP_USERNAME }}
        password: ${{ secrets.FTP_PASSWORD }}
        server-dir: /public_html/
        exclude: |
          **/.git*
          **/.git*/**
          **/config/config.php
          **/node_modules/**
```

#### Configurar Secrets no GitHub

1. Vá em Settings > Secrets and variables > Actions
2. Adicione:
   - `FTP_USERNAME`: seu usuário FTP
   - `FTP_PASSWORD`: sua senha FTP

---

## 🔒 Checklist de Segurança Pós-Deploy

- [ ] Arquivo `config/config.php` não está no controle de versão
- [ ] Senhas fortes para banco de dados
- [ ] SSL/HTTPS configurado
- [ ] Arquivos sensíveis bloqueados no `.htaccess`
- [ ] Backups automáticos configurados
- [ ] Permissões de arquivo corretas (755 para pastas, 644 para arquivos)
- [ ] PHP atualizado para última versão estável
- [ ] Logs de erro configurados
- [ ] Firewall configurado (UFW no Ubuntu)

---

## 🧪 Testar o Deploy

Após o deploy, teste:

1. **Página inicial**: Acesse o domínio
2. **URLs amigáveis**: Teste as rotas (inicio, ofertas, etc.)
3. **Banco de dados**: Verifique se os dados são salvos
4. **Formulários**: Teste o envio de dados
5. **Webhooks**: Verifique integrações
6. **Admin**: Acesse área administrativa

---

## 🐛 Troubleshooting

### Erro 500 - Internal Server Error

```bash
# Verificar logs do Apache
sudo tail -f /var/log/apache2/error.log

# Verificar permissões
sudo chmod -R 755 /var/www/html/robux
sudo chown -R www-data:www-data /var/www/html/robux
```

### .htaccess não funciona

```bash
# Habilitar mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Verificar AllowOverride no VirtualHost
# Deve estar: AllowOverride All
```

### Erro de conexão com banco

1. Verifique as credenciais em `config/config.php`
2. Confirme que o usuário tem permissões
3. Verifique se o MySQL está rodando:

```bash
sudo systemctl status mysql
```

### Página em branco

```bash
# Habilitar exibição de erros (apenas desenvolvimento!)
# Adicione no início do index.php:
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## 📊 Monitoramento

### Logs importantes

```bash
# Apache error log
tail -f /var/log/apache2/error.log

# Apache access log
tail -f /var/log/apache2/access.log

# PHP error log (se configurado)
tail -f /var/log/php/error.log
```

### Backup do banco

```bash
# Criar backup
mysqldump -u latam_user -p latam > backup_$(date +%Y%m%d).sql

# Agendar backup diário (crontab)
0 2 * * * mysqldump -u latam_user -psenha latam > /backups/latam_$(date +\%Y\%m\%d).sql
```

---

## 🔄 Atualizações

Para atualizar o projeto:

```bash
cd /var/www/html/robux
sudo git pull origin main
sudo systemctl reload apache2
```

---

## 📞 Suporte

Se encontrar problemas durante o deploy:

1. Verifique os logs
2. Consulte a documentação do servidor
3. Abra uma issue no GitHub
4. Entre em contato com o suporte

---

**Última atualização**: Outubro 2026
