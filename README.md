# API de Gestão de Pedidos de Viagem Corporativa

Este projeto é o **back-end** de uma aplicação Full Stack para gerenciar pedidos de viagem corporativa, desenvolvido em **Laravel** e protegido por **JWT**.

---

## Funcionalidades

- Criar pedidos de viagem com informações como solicitante, destino, datas e status.
- Consultar pedidos específicos.
- Listar pedidos com filtros por status, período e destino.
- Atualizar status de pedidos (aprovado ou cancelado) — restrito a administradores.
- Cancelamento de pedidos somente se não estiverem aprovados.
- Notificações de aprovação ou cancelamento para o usuário solicitante.
- Relacionamento de pedidos com usuários — cada usuário só acessa suas próprias ordens.
- Testes unitários para principais funcionalidades.

---

## API Endpoints

### Autenticação

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST   | `/api/auth/register` | Registrar usuário |
| POST   | `/api/auth/login`    | Fazer login |
| GET    | `/api/auth/me`       | Obter dados do usuário logado |
| POST   | `/api/auth/logout`   | Fazer logout |
| POST   | `/api/auth/refresh`  | Renovar token JWT |

### Pedidos de Viagem

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET    | `/api/travel-requests` | Listar pedidos (com filtros por status, período e destino) |
| POST   | `/api/travel-requests` | Criar novo pedido |
| GET    | `/api/travel-requests/{id}` | Obter pedido específico |
| PATCH  | `/api/travel-requests/{id}/status` | Atualizar status (somente administradores) |

---

## Instalação e Execução

### 1. Clonar o repositório

```bash
git clone <URL_DO_REPOSITORIO>
cd <NOME_DO_PROJETO>
```

### 2. Buildar e iniciar os containers Docker
```bash
docker-compose up -d --build
```

Aguarde até que o container do banco de dados esteja totalmente iniciado.

### 3. Criar tabelas e rodar seeds
```bash
docker exec -it <nome_do_container_php> php artisan migrate:fresh --seed
```

Isso criará todas as tabelas necessárias e popularemos dados iniciais.

### 4. Rodar testes
```bash
docker exec -it <nome_do_container_php> php artisan test
```