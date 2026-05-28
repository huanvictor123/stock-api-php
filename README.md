# Stock API

> RESTful API de gestão de estoque e vendas construída com PHP 8.2, MySQL 8 e Docker.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)
![Docker](https://img.shields.io/badge/Docker-25+-2496ED?logo=docker)
![License](https://img.shields.io/badge/License-MIT-green)

## Sobre o projeto

API RESTful para controle de estoque e registro de vendas. Implementa CRUD completo de produtos e categorias, vendas com transações ACID (garantindo consistência do estoque), autenticação JWT e endpoints de relatórios.

Principais desafios técnicos abordados:
- Transações ACID com `FOR UPDATE` para evitar race conditions em vendas simultâneas
- Autenticação stateless via JWT sem dependência de sessão
- Arquitetura MVC sem frameworks — PDO puro com prepared statements

## Tecnologias

| Tecnologia | Versão | Função |
|-----------|--------|--------|
| PHP | 8.2 | Linguagem principal |
| MySQL | 8.0 | Banco de dados relacional |
| Docker | 25+ | Containerização |
| PDO | nativo | Acesso ao banco com prepared statements |
| Firebase JWT | 7.0 | Autenticação via token |

## Arquitetura

Padrão MVC sem framework, com separação em camadas:

```
src/
├── index.php           (entry point / front controller)
├── .htaccess           (URL rewriting)
├── config/Database.php (singleton PDO)
├── routes/api.php      (mapeamento de rotas)
├── controllers/        (recebem HTTP, validam input, chamam models)
├── models/             (acesso ao banco via PDO)
├── middleware/          (validação JWT)
└── helpers/            (respostas JSON padronizadas + validador)
```

### Fluxo de uma requisição

```
HTTP Request → index.php → routes/api.php → AuthMiddleware → Controller → Model → Response JSON
```

## Como rodar

**Pré-requisito:** Docker Desktop instalado.

```bash
git clone https://github.com/huanvictor123/stock-api-php.git
cd stock-api-php
cp .env.example .env
docker compose up -d
```

A API estará disponível em **http://localhost:8080**
A documentação interativa (Swagger UI) em **http://localhost:8081**

```bash
# Verificar se está rodando
curl http://localhost:8080
# → {"message":"Stock API is running","version":"1.0.0"}
```

## Endpoints

### Autenticação (pública)

| Método | Rota | Descrição |
|--------|------|-----------|
| POST | /auth/login | Login (email + password) → retorna Bearer token |

### Produtos (protegido)

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | /products | Listar todos (com nome da categoria e fornecedor) |
| GET | /products/:id | Buscar por ID |
| POST | /products | Criar produto |
| PUT | /products/:id | Atualizar produto |
| DELETE | /products/:id | Deletar produto |

### Categorias (protegido)

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | /categories | Listar todas |
| GET | /categories/:id | Buscar por ID |
| POST | /categories | Criar categoria |
| DELETE | /categories/:id | Deletar (409 se tiver produtos vinculados) |

### Vendas (protegido)

| Método | Rota | Descrição |
|--------|------|-----------|
| POST | /sales | Criar venda (com transação ACID + baixa de estoque) |
| GET | /sales/:id | Buscar venda com itens |

### Relatórios (protegido)

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | /reports/low-stock | Produtos com estoque abaixo do mínimo |
| GET | /reports/sales-summary | Resumo de vendas do período (?start= e ?end=) |

## Autenticação

```bash
# 1. Fazer login
curl -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@stockapi.com","password":"admin123"}'

# 2. Usar o token nas próximas requisições
curl http://localhost:8080/products \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

O token expira em **1 hora**. Após expirar, faça login novamente.

## Decisões técnicas

**Por que DECIMAL em vez de FLOAT para preço?**
FLOAT tem imprecisão de ponto flutuante. R$ 2,10 pode virar R$ 2,0999999... `DECIMAL(10,2)` é exato e é o tipo correto para valores monetários.

**Por que transações ACID na criação de vendas?**
Uma venda envolve inserir `sale`, `sale_items` e decrementar estoque. Se qualquer passo falhar, a transação faz rollback de tudo — evitando estados inconsistentes.

**Por que `FOR UPDATE` na verificação de estoque?**
Impede que duas vendas simultâneas leiam o mesmo estoque e ambas aprovem a compra do último item. Uma espera a outra terminar.

**Por que `price_at_sale` em `sale_items`?**
Congela o preço no momento da venda. Se o produto mudar de preço amanhã, o histórico financeiro permanece correto.

**Por que PDO em vez de um ORM?**
Demonstra domínio da camada de dados. ORMs adicionam abstração; PDO com prepared statements é direto, seguro e sem SQL injection.

## Banco de dados

### Tabelas

| Tabela | Descrição |
|--------|-----------|
| categories | Categorias de produtos |
| suppliers | Fornecedores |
| products | Produtos com FK para categorias e fornecedores |
| sales | Cabeçalho da venda (cliente inline) |
| sale_items | Itens da venda (com price_at_sale) |
| users | Usuários para autenticação JWT |

### Credenciais do banco (desenvolvimento)

- **Banco:** stock_api
- **Usuário:** stock_user
- **Senha:** stock_pass_123
- **Porta:** 3307 (host) / 3306 (container)

### Usuário padrão

- **Email:** admin@stockapi.com
- **Senha:** admin123

## Comandos úteis

```bash
# Subir containers
docker compose up -d

# Parar containers
docker compose down

# Ver logs
docker compose logs -f php
docker compose logs -f mysql

# Entrar no container PHP
docker compose exec php bash

# Entrar no MySQL
docker compose exec mysql mysql -u stock_user -pstock_pass_123 stock_api

# Reconstruir imagem PHP
docker compose up -d --build php

# Resetar banco (apaga dados)
docker compose down -v && docker compose up -d
```

## Licença

Distribuído sob licença MIT. Veja `LICENSE` para mais informações.
