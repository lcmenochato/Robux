# 🚀 Setup Rápido - Robux

Guia de configuração rápida para começar a trabalhar com o projeto.

## ⚡ Início Rápido (5 minutos)

### 1️⃣ Clone e Configure

```bash
# Clone o repositório
git clone https://github.com/lcmenochato/robux.git
cd robux

# Copie o arquivo de configuração
cp config/config.example.php config/config.php
```

### 2️⃣ Configure o Banco de Dados

Edite `config/config.php` com suas credenciais:

```php
$host = 'localhost';
$user = 'seu_usuario';
$password = 'sua_senha';
$dbname = 'latam';
```

### 3️⃣ Importe o Banco

```bash
mysql -u root -p nome_do_banco < latam.sql
```

### 4️⃣ Configure o Servidor

**XAMPP/WAMP (Windows/Mac):**
- Coloque a pasta `robux` dentro de `htdocs`
- Acesse: `http://localhost/robux`

**Apache (Linux):**
```bash
sudo cp -r robux /var/www/html/
sudo chown -R www-data:www-data /var/www/html/robux
```

### 5️⃣ Teste

Acesse no navegador:
- Local: `http://localhost/robux`
- Com domínio: `http://seu-dominio.com`

---

## 🔧 Configuração do GitHub Actions

Para deploy automatizado, configure os Secrets no GitHub:

1. Vá em **Settings** > **Secrets and variables** > **Actions**
2. Clique em **New repository secret**
3. Adicione os seguintes secrets:

| Secret Name | Descrição | Exemplo |
|------------|-----------|---------|
| `FTP_SERVER` | Endereço do servidor FTP | `ftp.seudominio.com` |
| `FTP_USERNAME` | Usuário FTP | `usuario@seudominio.com` |
| `FTP_PASSWORD` | Senha FTP | `sua_senha_ftp` |
| `FTP_SERVER_DIR` | Diretório no servidor | `/public_html/` |

### Como adicionar um Secret:

```
1. Name: FTP_SERVER
   Secret: ftp.seudominio.com
   [Add secret]

2. Name: FTP_USERNAME
   Secret: seu_usuario
   [Add secret]

3. Name: FTP_PASSWORD
   Secret: sua_senha
   [Add secret]

4. Name: FTP_SERVER_DIR
   Secret: /public_html/
   [Add secret]
```

Após configurar, cada push na branch `main` fará deploy automático!

---

## 📦 Deploy Manual

### Via FTP/SFTP

1. **Conecte ao servidor:**
   - Host: `ftp.seudominio.com`
   - Usuário: seu usuário FTP
   - Senha: sua senha FTP

2. **Faça upload dos arquivos:**
   - Envie todos os arquivos para `/public_html/` ou pasta do domínio
   - **NÃO** envie: `.git`, `.github`, `node_modules`, `README.md`

3. **Configure o banco:**
   - Acesse phpMyAdmin
   - Crie um banco de dados
   - Importe `latam.sql`

4. **Configure credenciais:**
   - Edite `config/config.php` diretamente no servidor
   - Use as credenciais do banco criado

---

## 🐳 Docker (Opcional)

Se preferir usar Docker:

```bash
# Criar Dockerfile
cat > Dockerfile << 'EOF'
FROM php:8.1-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
EOF

# Criar docker-compose.yml
cat > docker-compose.yml << 'EOF'
version: '3.8'

services:
  web:
    build: .
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: latam
      MYSQL_USER: latam_user
      MYSQL_PASSWORD: latam_pass
    ports:
      - "3306:3306"
    volumes:
      - ./latam.sql:/docker-entrypoint-initdb.d/latam.sql
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
EOF

# Iniciar
docker-compose up -d
```

Acesse: `http://localhost:8080`

---

## ✅ Checklist de Configuração

Antes de colocar em produção:

- [ ] `config/config.php` configurado com credenciais corretas
- [ ] Banco de dados importado
- [ ] `.htaccess` funcionando
- [ ] SSL/HTTPS configurado
- [ ] `config/config.php` adicionado ao `.gitignore`
- [ ] Secrets do GitHub Actions configurados
- [ ] Teste todas as páginas
- [ ] Teste formulários
- [ ] Verifique logs de erro

---

## 🔍 Verificação de Funcionamento

### Teste URLs:

```bash
# Página inicial
curl -I http://seu-dominio.com/

# API
curl -I http://seu-dominio.com/api/

# Páginas internas
curl -I http://seu-dominio.com/inicio
curl -I http://seu-dominio.com/ofertas
```

### Teste .htaccess:

```bash
# Deve retornar 403/404
curl -I http://seu-dominio.com/config/config.php
curl -I http://seu-dominio.com/.env
curl -I http://seu-dominio.com/.git/
```

---

## 🆘 Problemas Comuns

### Página em branco?
```bash
# Verifique os logs
tail -f /var/log/apache2/error.log
```

### .htaccess não funciona?
```bash
# Habilite mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Erro de banco de dados?
- Verifique credenciais em `config/config.php`
- Confirme que o banco foi importado
- Teste conexão: `mysql -u usuario -p -h localhost banco`

### Deploy não funciona?
- Verifique os Secrets no GitHub
- Veja logs em **Actions** no GitHub
- Confirme que o FTP está acessível

---

## 📚 Documentação Completa

- [README.md](README.md) - Visão geral do projeto
- [DEPLOY.md](DEPLOY.md) - Guia detalhado de deploy
- [SETUP.md](SETUP.md) - Este arquivo

---

## 🤝 Precisa de Ajuda?

1. Verifique a [documentação completa](DEPLOY.md)
2. Veja os [logs](#-verificação-de-funcionamento)
3. Abra uma [issue no GitHub](https://github.com/lcmenochato/robux/issues)

---

**Última atualização**: Outubro 2026
