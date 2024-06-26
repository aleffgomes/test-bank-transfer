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

2. Para iniciar os containeres Docker, execute:

    ```bash
    docker compose up -d --build
    ```

    Isso iniciará os seguintes serviços:

    - MySQL na porta 3306
    - Aplicação na porta 80
    - Redis na porta 6379
  
3. Na Raiz do projeto, configure o ``.env``:

    ```bash
    cp .env.example .env
    ```

4. Acesse o container e instale as dependências:

    ```bash
    docker exec -it APP bash
    ```

    ```bash
    composer install
    ```

5. Ainda no container, habilite a permissão para gravação de logs e cache

    ```bash
    chmod -R 777 /var/www/html/writable 
    ```

6. Ainda no container, execute os seguintes comandos para criação do banco de dados e das seeds com dados fictícios:

    ```bash
    php spark migrate
    php spark db:seed DatabaseSeeder
    ```

### Redis

O Redis já estará rodando como parte do ambiente Docker. Certifique-se de que a configuração no arquivo .env aponta para o host e porta corretos

### Link de teste da API

É possível testar a aplicação em <http://localhost/ping>

## Executando os Testes

- Para executar os testes unitários, use o seguinte comando na raiz do projeto ou no container:

    ```bash
    vendor/bin/phpunit
    ```

## API

O endpoint disponível para transferência, estará disponível no link <http://localhost/transfer> e requer um corpo com os dados necessários, para verificar consulte <http://localhost/docs>.

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
│   ├── Models/
│   ├── Services/
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
Models/: Modelos de dados.
Services/: Lógica de negócios separada dos controladores.
Views/: Arquivos de visualização.
public/: Ponto de entrada da aplicação.

tests/: Testes automatizados.

writable/: Arquivos que a aplicação pode escrever.

env: Arquivo de variáveis de ambiente.
spark e composer.json: Gerenciamento de dependências.
phpunit.xml: Configuração do PHPUnit.

## Filtro de Autorização

O filtro de autorização (AuthorizationFilter) é um middleware utilizado para verificar se o usuário possui permissão para acessar determinadas rotas da aplicação. Ele é aplicado antes de a requisição atingir o controlador correspondente.

O filtro é implementado na classe AuthorizationFilter localizada em App\Filters\AuthorizationFilter.php.
Utiliza o serviço authorizationService para verificar se o usuário possui autorização.
Retorna uma resposta não autorizada se a autorização falhar.

Registrado no arquivo de configuração ``app\Config\Filters.php``.
Configurado para ser executado antes de determinadas rotas protegidas.

## Camada de Serviços (Services)

A camada de serviços encapsula a lógica de negócios da aplicação, separando-a dos controladores para promover reutilização e facilitar testes automatizados. Essa abordagem permite que a lógica de negócios seja centralizada, tornando a manutenção e a evolução do código mais eficientes.

Para a aplicação de serviço de transferência, implementamos um método em ``app/Config/Services.php`` que instancia as classes de serviços. A escolha de usar ``app/Config/Services.php`` para essa implementação se baseia em diversos benefícios:

- Centralização da Configuração: Manter a configuração dos serviços em um único local facilita a gestão e a modificação das dependências da aplicação. Qualquer alteração nos serviços pode ser feita de forma centralizada, sem necessidade de alterar múltiplos arquivos.

- Injeção de Dependência: O uso de ``app/Config/Services.php`` permite uma injeção de dependência mais limpa e estruturada. Isso promove a inversão de controle (IoC), onde os serviços são definidos fora das classes que os utilizam, facilitando a troca de implementações e a criação de mocks para testes.

- Facilidade de Testes: Com os serviços centralizados em Services.php, é mais fácil substituir implementações reais por mocks ou stubs durante os testes automatizados. Isso é crucial para garantir que os testes sejam isolados e não dependam de recursos externos como bancos de dados ou serviços de terceiros.

- Reutilização de Código: Ao definir os serviços em ``app/Config/Services.php``, garantimos que a mesma instância de um serviço pode ser reutilizada em diferentes partes da aplicação. Isso evita a criação de múltiplas instâncias desnecessárias e promove a consistência dos dados.

## Controllers

Os controladores recebem requisições HTTP, interagem com os serviços e retornam respostas para o cliente.

## Models

Os modelos representam e interagem com os dados do banco de dados.

## Interfaces

Interfaces definem contratos que as classes devem implementar, visando desacoplar o código.

## Migrations

As migrations são scripts PHP que criam e modificam a estrutura do banco de dados de forma controlada.

## Seeds

Os seeds são scripts PHP que inserem dados iniciais no banco de dados.

## Commands

Criar comandos para serem executados via CLI.

Exemplo:

- ProcessNotificationQueue.php: É um exemplo de como processar uma fila de notificações mal sucedidas armazenadas no Redis. Este comando pode ser configurado para ser executado periodicamente, por exemplo, através de um cron job.
