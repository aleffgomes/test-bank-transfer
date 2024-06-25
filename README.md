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

    Copie o arquivo ``.env.example`` para ``.env`` e ajuste as configurações conforme necessário.

2. Instale as dependências:

    ```bash
    composer install
    ```

## Uso

### Inicializando o Docker

Para iniciar os contêineres Docker, execute:

```bash
docker compose up -d --build
```

Isso iniciará os seguintes serviços:

- MySQL na porta 3306
- Aplicação na porta 80
- Redis na porta 6379

### Habilite a permissão para gravação

```bash
docker exec -it APP bash
```

```bash
chmod -R 777 /var/www/html/writable 
```

### Migrações e Seeds

Para executar as migrações do banco de dados e popular o banco de dados com dados iniciais, caso tenha saído do contêiner entre novamente:

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

### Link de teste da API

É possível testar a API em <http://localhost/ping>

## Executando os Testes

Para executar os testes unitários, use o seguinte comando na raiz do projeto:

```bash
vendor/bin/phpunit
```

## Documentação

Caso precise gerar uma documentação atual usando o swagger (openapi) execute o seguinte comando na raiz do projeto (não é necessário):

```bash
./vendor/bin/openapi app -o public/openapi.json
```

Acesse a documentação Swagger:

Abra o navegador e acesse <http://localhost/docs> para visualizar a documentação da API.

***

## API

O endpoint disponível para transferência, estará disponível no endpoint <http://localhost/transfer> e requer um corpo com os dados necessários, para verificar consulte <http://localhost/docs>.

***

## Documentação da Arquitetura do Projeto

### Introdução

Esta documentação descreve a arquitetura e os principais componentes do projeto utilizando CodeIgniter 4. Inclui informações sobre a estrutura de pastas, padrões de design, camadas de aplicação, e como diferentes componentes interagem entre si.

### Estrutura de Pastas

A estrutura de pastas típica em um projeto CodeIgniter 4 é organizada da seguinte maneira:

```php
project-root/
│
├── app/
│   ├── Config/
│   ├── Controllers/
│   ├── Database/
│   ├── Filters/
│   ├── Helpers/
│   ├── Libraries/
│   ├── Models/
│   ├── Services/
│   ├── ThirdParty/
│   └── Views/
│
├── public/
│   ├── .htaccess
│   └── index.php
│
├── tests/
│   ├── Services/
│   └── Unit/
│
├── writable/
│   ├── cache/
│   ├── logs/
│   └── session/
│
├── env
├── spark
├── composer.json
├── phpunit.xml
├── DockerFile
└── docker-compose.yaml
```

app/: Contém a lógica principal da aplicação.

Config/: Configurações da aplicação.
Controllers/: Controladores da aplicação.
Database/: Migrations e seeds.
Filters/: Filtros personalizados (middlewares)
Helpers/: Funções auxiliares globais.
Libraries/: Bibliotecas customizadas.
Models/: Modelos de dados.
Services/: Lógica de negócios separada dos controladores.
ThirdParty/: Pacotes de terceiros.
Views/: Arquivos de visualização.
public/: Ponto de entrada da aplicação.

tests/: Testes automatizados.

writable/: Arquivos que a aplicação pode escrever.

env: Arquivo de variáveis de ambiente.
spark e composer.json: Gerenciamento de dependências.
phpunit.xml: Configuração do PHPUnit.

## Filtro de Autorização

O filtro de autorização (AuthorizationFilter) é um middleware utilizado para verificar se o usuário possui permissão para acessar determinadas rotas da aplicação. Ele é aplicado antes de a requisição atingir o controlador correspondente.

### Implementação

O filtro é implementado na classe AuthorizationFilter localizada em App\Filters\AuthorizationFilter.php.
Utiliza o serviço authorizationService para verificar se o usuário possui autorização.
Retorna uma resposta não autorizada se a autorização falhar.

### Registro

Registrado no arquivo de configuração app\Config\Filters.php.
O alias checkauth é associado à classe CheckAuth.
Configurado para ser executado antes de determinadas rotas protegidas.

## Camada de Serviços (Services)

### Objetivo

A camada de serviços encapsula a lógica de negócios da aplicação, separando-a dos controladores para promover reutilização e facilitar testes automatizados.

### Exemplo de Estrutura

Services/

- TransferService.php: Implementa a lógica de transferência de dinheiro entre usuários.
- NotificationService.php: Envia notificações aos usuários.

## Controllers

### Objetivo

Os controladores recebem requisições HTTP, interagem com os serviços e retornam respostas para o cliente.

### Exemplo de Estrutura

Controllers/

- TransferController.php: Controla as operações de transferência de dinheiro.

## Models

### Objetivo

Os modelos representam e interagem com os dados do banco de dados.

### Exemplo de Estrutura

Models/

- UserModel.php: Modelo para gerenciamento de usuários.
- WalletModel.php: Modelo para gerenciamento de carteiras.
- TransactionModel.php: Modelo para gerenciamento de transações.
- TransactionStatusModel.php: Modelo para gerenciamento de status de transações.

## Interfaces

### Objetivo

Interfaces definem contratos que as classes devem implementar.

### Exemplo de Estrutura

Interfaces/

- AuthorizationServiceInterface.php: Interface para serviço de autorização.
- NotificationServiceInterface.php: Interface para serviço de notificação.

## Migrations

### Objetivo

As migrations são scripts PHP que criam e modificam a estrutura do banco de dados de forma controlada.

### Exemplo de Estrutura

Database/Migrations/

- 20240101000000_create_users_table.php: Criação da tabela de usuários.
- 20240102000000_create_wallets_table.php: Criação da tabela de carteiras.

## Seeds

### Objetivo

Os seeds são scripts PHP que inserem dados iniciais no banco de dados.

### Exemplo de Estrutura

Database/Seeds/

- UsersSeeder.php: Popula a tabela de usuários com dados de exemplo.
- WalletsSeeder.php: Popula a tabela de carteiras com dados de exemplo.

## Commands

### Objetivo

Criar comandos para serem executados via CLI.

### Exemplo de Estrutura

Commands/

- ProcessNotificationQueue.php: É um exemplo de como processar uma fila de notificações mal sucedidas armazenadas no Redis. Este comando pode ser configurado para ser executado periodicamente, por exemplo, através de um cron job.



