# Transfer Service

## Descrição

Este projeto é um sistema de transferência de dinheiro entre usuários. Ele utiliza o CodeIgniter 4 (CI4) como framework principal, MySQL como banco de dados e Redis para gerenciamento de filas de notificações. Todo o ambiente é orquestrado utilizando Docker.

## Tecnologias Utilizadas

- CodeIgniter 4 (CI4)
- MySQL
- Redis
- Docker

## Requisitos

- Docker
- Docker Compose

## Instalação

1. Clone o repositório:

   ```bash
   git clone https://github.com/aleffgomes/test-bank-transfer.git
   cd test-bank-transfer
   ```

2. Configure o ambiente:

    Renomeie o arquivo .env.example para .env e ajuste as configurações conforme necessário.

    ```bash
    mv .env.example .env
    ```

## Uso

### Inicializando o Docker

Para iniciar os contêineres Docker, execute:

```bash
docker-compose up -d
```

Isso iniciará os seguintes serviços:

- MySQL na porta 3306
- Aplicação na porta 80 <http://localhost>
- Redis na porta 6379

É possível testar a API em <http://localhost/ping>

### Migrações e Seeds

Para executar as migrações do banco de dados e popular o banco de dados com dados iniciais, entre no contêiner da aplicação:

```bash
docker exec -it APP bash
```

Dentro do contêiner, execute:

```bash
php spark migrate
php spark db:seed DatabaseSeeder
```

### Redis

O Redis já estará rodando como parte do ambiente Docker. Certifique-se de que a configuração no arquivo .env aponta para o host e porta corretos (redis e 6379)

## Executando os Testes

Para executar os testes unitários, use o seguinte comando:

```bash
vendor/bin/phpunit
```

## Documentação

Para gerar a documentação atual usando o swagger (openapi) execute na raiz do projeto:

```bash
./vendor/bin/openapi app -o public/openapi.json
```

Acesse a documentação Swagger:

Abra o navegador e acesse <http://localhost/docs> para visualizar a documentação da API.
