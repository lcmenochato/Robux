# Robux - Sistema de Landing Page

Sistema de landing page PHP com integração de pagamento e gerenciamento de leads.

## 🚀 Tecnologias

- PHP 7.4+
- MySQL
- Apache (mod_rewrite)
- JavaScript
- CSS3

## 📋 Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache com mod_rewrite habilitado
- Composer (para dependências do admin)

## 🔧 Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/lcmenochato/robux.git
cd robux
```

### 2. Configure o banco de dados

Importe o arquivo `latam.sql` para criar a estrutura do banco de dados:

```bash
mysql -u seu_usuario -p nome_do_banco < latam.sql
```

### 3. Configure as credenciais

Copie o arquivo de configuração de exemplo e edite com suas credenciais:

```bash
cp config/config.example.php config/config.php
```

Edite `config/config.php` e ajuste:

```php
$host = 'localhost';        // Host do banco de dados
$user = 'seu_usuario';      // Usuário do banco
$password = 'sua_senha';    // Senha do banco
$dbname = 'nome_do_banco';  // Nome do banco
```

### 4. Configure permissões

```bash
chmod 755 -R .
chmod 644 .htaccess
```

### 5. Configure o Apache

Certifique-se de que o mod_rewrite está habilitado:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Configure o VirtualHost ou aponte o DocumentRoot para a pasta do projeto.

## 📁 Estrutura do Projeto

```
.
├── admin/              # Área administrativa
├── api/                # APIs e endpoints
├── config/             # Arquivos de configuração
├── css/                # Arquivos de estilo
├── js/                 # JavaScript
├── Webhook/            # Webhooks de integração
├── x9/                 # Sistema anti-bot
├── .htaccess           # Configurações Apache
├── index.php           # Arquivo principal
└── README.md           # Este arquivo
```

## 🌐 Deploy

### Deploy em Servidor Compartilhado

1. Faça upload de todos os arquivos via FTP/SFTP
2. Importe o banco de dados pelo phpMyAdmin
3. Configure o arquivo `config/config.php`
4. Verifique se o `.htaccess` está funcionando

### Deploy em VPS/Servidor Dedicado

1. Clone o repositório no servidor
2. Configure o banco de dados
3. Configure o Apache/Nginx
4. Ajuste as permissões
5. Configure SSL/HTTPS (recomendado)

### Variáveis de Ambiente (Produção)

Para ambientes de produção, considere usar variáveis de ambiente em vez de credenciais fixas no código:

```php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'latam';
```

## 🔒 Segurança

- **NUNCA** commite o arquivo `config/config.php` com credenciais reais
- Use HTTPS em produção
- Mantenha as dependências atualizadas
- Configure backups regulares do banco de dados
- Revise regularmente os logs de acesso

## 📝 Configurações do .htaccess

O arquivo `.htaccess` inclui:
- Proteção contra listagem de diretórios
- Bloqueio de acesso a arquivos sensíveis
- Regras de rewrite para URLs amigáveis
- Páginas de erro customizadas

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/NovaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/NovaFeature`)
5. Abra um Pull Request

## ⚠️ Avisos Importantes

- Este projeto usa credenciais de banco de dados locais por padrão
- Não exponha credenciais em ambientes públicos
- Configure adequadamente para produção antes do deploy

## 📧 Suporte

Para suporte, abra uma issue no GitHub ou entre em contato.

---

**Nota**: Este é um projeto em desenvolvimento. Use em produção por sua conta e risco.
