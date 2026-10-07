# 🔑 Como Configurar GitHub Secrets

Este guia mostra como configurar os Secrets necessários para o deploy automático via GitHub Actions.

## 📌 O que são GitHub Secrets?

Secrets são variáveis criptografadas que armazenam informações sensíveis (senhas, API keys, tokens) de forma segura no GitHub. Eles são usados nos workflows do GitHub Actions sem expor os valores.

---

## 🚀 Passo a Passo

### 1. Acesse as Configurações do Repositório

1. Vá para: `https://github.com/lcmenochato/robux`
2. Clique na aba **Settings** (Configurações)
3. No menu lateral esquerdo, clique em **Secrets and variables**
4. Clique em **Actions**

### 2. Adicione os Secrets Necessários

Clique no botão **New repository secret** e adicione cada um dos seguintes:

---

#### Secret #1: FTP_SERVER

**Nome do Secret:**
```
FTP_SERVER
```

**Valor:**
```
ftp.seudominio.com
```

**Descrição:** Endereço do servidor FTP onde o site será hospedado.

**Como encontrar:**
- Verifique o email de boas-vindas da hospedagem
- Painel de controle (cPanel) > FTP Accounts
- Geralmente é: `ftp.seudominio.com` ou `seudominio.com`

---

#### Secret #2: FTP_USERNAME

**Nome do Secret:**
```
FTP_USERNAME
```

**Valor:**
```
usuario@seudominio.com
```

**Descrição:** Nome de usuário para login FTP.

**Como encontrar:**
- Painel de controle > FTP Accounts
- Pode ser seu email ou um usuário específico
- Exemplos: `usuario@seudominio.com`, `ftpuser`, `admin`

---

#### Secret #3: FTP_PASSWORD

**Nome do Secret:**
```
FTP_PASSWORD
```

**Valor:**
```
sua_senha_ftp_aqui
```

**Descrição:** Senha do usuário FTP.

**⚠️ IMPORTANTE:** Use a senha real, mas ela ficará criptografada no GitHub.

---

#### Secret #4: FTP_SERVER_DIR

**Nome do Secret:**
```
FTP_SERVER_DIR
```

**Valor:**
```
/public_html/
```

ou

```
/httpdocs/
```

ou

```
/www/
```

**Descrição:** Pasta no servidor onde os arquivos serão enviados.

**Opções comuns:**
- `/public_html/` (cPanel)
- `/httpdocs/` (Plesk)
- `/www/` (alguns servidores)
- `/` (se a conta FTP já aponta para a pasta correta)

**Como descobrir:**
- Conecte via FTP manualmente
- Veja onde está o `index.php` ou `index.html` atual
- Essa é a pasta que você deve usar

---

## 📋 Resumo dos Secrets

Após adicionar todos, você deve ter:

| Secret Name | Exemplo | Status |
|-------------|---------|--------|
| `FTP_SERVER` | `ftp.tiktokstore.shop` | ✅ |
| `FTP_USERNAME` | `admin@tiktokstore.shop` | ✅ |
| `FTP_PASSWORD` | `[oculto]` | ✅ |
| `FTP_SERVER_DIR` | `/public_html/` | ✅ |

---

## 🧪 Testar o Deploy

Após configurar os secrets:

1. **Faça um commit qualquer:**
   ```bash
   git add .
   git commit -m "Teste de deploy automático"
   git push origin main
   ```

2. **Acompanhe o workflow:**
   - Vá em: `https://github.com/lcmenochato/robux/actions`
   - Clique no workflow que está rodando
   - Veja os logs em tempo real

3. **Verifique o resultado:**
   - ✅ Verde = Deploy bem-sucedido
   - ❌ Vermelho = Erro (verifique os logs)

---

## 🔍 Verificando se funcionou

Após o deploy, verifique:

```bash
# Teste se o site está no ar
curl -I https://tiktokstore.shop

# Ou acesse diretamente no navegador
```

---

## ❌ Solução de Problemas

### Erro: "Failed to connect to FTP server"

**Possíveis causas:**
- FTP_SERVER incorreto
- Firewall bloqueando a conexão
- Porta errada (padrão é 21)

**Solução:**
- Verifique o endereço FTP
- Teste conectar manualmente com FileZilla
- Fale com o suporte da hospedagem

---

### Erro: "Login incorrect"

**Possíveis causas:**
- FTP_USERNAME ou FTP_PASSWORD incorretos
- Usuário não tem permissão

**Solução:**
- Verifique as credenciais
- Teste conectar manualmente
- Recrie o usuário FTP se necessário

---

### Erro: "Cannot change directory"

**Possíveis causas:**
- FTP_SERVER_DIR incorreto
- Usuário não tem permissão na pasta

**Solução:**
- Conecte via FTP e veja o caminho correto
- Ajuste o secret FTP_SERVER_DIR
- Verifique permissões da pasta

---

## 🔐 Segurança dos Secrets

### ✅ Boas Práticas:

- Secrets são criptografados pelo GitHub
- Nunca aparecem nos logs (são mascarados)
- Apenas colaboradores com acesso ao repositório podem editá-los
- Não são expostos em forks do repositório
- Podem ser atualizados sem reescrever o workflow

### ⚠️ Cuidados:

- Não faça `echo` dos secrets nos workflows
- Não os use em branches de PRs de forks (por segurança)
- Revogue e recrie se suspeitar de vazamento

---

## 🔄 Atualizando Secrets

Se precisar mudar uma senha:

1. Vá em **Settings** > **Secrets and variables** > **Actions**
2. Clique no secret que deseja atualizar
3. Clique em **Update secret**
4. Cole o novo valor
5. Clique em **Update secret** novamente

O próximo deploy usará o novo valor automaticamente.

---

## 📞 Precisa de Ajuda?

### Onde encontrar suas credenciais FTP:

1. **Email de boas-vindas** da hospedagem
2. **Painel de controle** (cPanel/Plesk/outro)
3. **Suporte da hospedagem** - eles podem resetar para você

### Serviços de hospedagem comuns:

| Hospedagem | FTP Server | Diretório Comum |
|------------|-----------|----------------|
| HostGator | `ftp.seudominio.com` | `/public_html/` |
| Hostinger | `ftp.hostinger.com` | `/public_html/` |
| UOLHost | `ftp.seudominio.com.br` | `/httpdocs/` |
| Locaweb | `ftp.seudominio.com.br` | `/public_html/` |
| GoDaddy | `ftp.seudominio.com` | `/` |

---

## 🎯 Próximos Passos

Depois de configurar os secrets:

1. ✅ Configure os 4 secrets
2. ✅ Faça um push para main
3. ✅ Acompanhe em Actions
4. ✅ Verifique o site no ar
5. ✅ Configure SSL/HTTPS (recomendado)

---

## 📚 Documentação Oficial

- [GitHub Secrets](https://docs.github.com/en/actions/security-guides/encrypted-secrets)
- [GitHub Actions](https://docs.github.com/en/actions)
- [FTP Deploy Action](https://github.com/SamKirkland/FTP-Deploy-Action)

---

**Criado em**: Outubro 2026  
**Para**: Repository lcmenochato/robux  
**Workflow**: `.github/workflows/deploy.yml`

---

> 💡 **Dica**: Depois de configurar uma vez, você nunca mais precisa fazer upload manual via FTP!  
> Cada push na branch `main` = Deploy automático 🚀
