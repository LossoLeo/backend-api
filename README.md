# Backend API

## Sobre o Projeto

API RESTful desenvolvida em Laravel para gerenciamento de usuários e produtos favoritos. O sistema permite autenticação de usuários, gerenciamento de perfis com sistema de permissões (Admin/Client), e integração com a FakeStore API para listagem de produtos.

## Tecnologias Utilizadas

- Laravel 12
- PHP 8.2+
- PostgreSQL
- Laravel Sanctum
- Spatie Laravel Permission
- Laravel Sail
- PHPUnit

## Requisitos

- **Docker Desktop** ou **Docker Engine + Docker Compose**
- **Git**
- **Postman**


## Instalação e Configuração

### Usando Docker (Laravel Sail)

#### 1. Clone o repositório

```bash
git clone <https://github.com/LossoLeo/backend-api.git>
cd backend-api
```

#### 2. Copie o arquivo .env

```bash
cp .env.example .env
```

#### 3. Instale as dependências (somente necessário rodar na primeira vez)

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

#### 4. Suba os containers

```bash
./vendor/bin/sail up -d
```

#### 5. Gere a chave da aplicação

```bash
./vendor/bin/sail artisan key:generate
```

#### 6. Execute as migrations e seeders

```bash
./vendor/bin/sail artisan migrate --seed
```


##  Documentação da API

A documentação completa da API está disponível no SwaggerHub:

**[Documentação Swagger - Backend API](https://app.swaggerhub.com/apis-docs/leonardolosso/BackEndAPI/1.0.0)**



## Executando os Testes

### No terminal na pasta raiz:

```bash
./vendor/bin/sail test
```


## Comandos Úteis

### Laravel Sail (Docker):

```bash
# Iniciar containers
./vendor/bin/sail up -d

# Parar containers
./vendor/bin/sail down

# Ver logs
./vendor/bin/sail logs -f

# Acessar container
./vendor/bin/sail shell

# Rodar Artisan commands
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail artisan route:list

# Rodar testes
./vendor/bin/sail test
```


## Troubleshooting

### Porta 8080 já está em uso

Edite o `.env` e altere:
```env
APP_PORT=8081
FORWARD_DB_PORT=5434
```

### Erro de permissão no Docker

```bash
sudo chown -R $USER:$USER .
```

### Containers não sobem

```bash
./vendor/bin/sail down
docker system prune -a
./vendor/bin/sail up -d
```

### Erro nas migrations

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

###  Utilização dos EndPoints caso o Swagger não funcione

Caso a documentação pelo Swagger esteja apresentando erro para rodar na web, existe a possibilidade de utilizar via Postman.

#### 1. No caso do Postman, para testar as rotas, copiar o cURL da rota desejada.
#### 2. Para ter acesso ao token (necessário para rotas autenticadas), use o seguinte fluxo (valido para o Swagger e Postman):
    2.1 - Grupo de rotas: Autenticação > /api/register
    2.2 - Copiar credenciais de email e senha
    2.3 - Ir em: Autenticação > /api/login
    2.4 - Copiar o token gerado na response do endpoint
    2.5 - Utilizar em Authorization no header da request

## Variáveis de Ambiente Importantes

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8080
APP_PORT=8080

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=backendApi
DB_USERNAME=sail
DB_PASSWORD=password

FORWARD_DB_PORT=5433
```

---

## Licença

Este projeto é open-source sob a licença [MIT](https://opensource.org/licenses/MIT).

---

## Desenvolvido por

**Leonardo Westephal Losso**

Projeto desenvolvido como teste técnico optando por adotar as práticas e tecnologias:
- Arquitetura em camadas (Controller → Service → Repository): Trazendo assim um escopo mais fácil de manutenção e escalabilidade, além da possibilidade de reuso de código.
- Integração com APIs externas: A conexão com a API FakeStoreAPI foi criado com um service de maneira na qual se o fornecedor seja alterado, as alterações que o código precisa adotar para a nova adequação serão menos complicadas de serem executadas.
- Sistema de autenticação e permissões Sanctum e Spatie: além de ser um padrão para APIs, ter as permissões consegue definir melhor o escopo em que cada role irá atuar.
- Testes automatizados: É extremamente importante ter testes que garantem a qualidade do código e garantem que se caso algo seja alterado, os testes irão detectar e apontar que algo não está correto naquele fluxo.
- Containerização com Docker: Opção de usar docker para melhorar a usabilidade da aplicação.
- Documentação completa com Swagger: Usar o Swagger garante que a API esteja em conformidade com a boa prática de documentar as ações do projeto, todos os endpoints da aplicação estão documentos, bem como os seus exemplos de utilização e retornos esperados pela API.
- Utilização do PHP e Laravel: Além da grande familiariade com o framework e a linguagem, a estruturação de pastas que o framework disponibiliza, uma API montada em PHP é bem sólida e de fácil uso e implementação em sistemas web, mobile ou até mesmo desktop.
- PostgreSQL: Banco de dados queridinho para utilizar, fácil de configurar, fácil de otimizar e possui uma estrutura que se encaixa muito bem com Laravel e PHP.


## Obrigado pela atenção :)
