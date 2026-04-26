# Car API

## Installation

### Requirements

- Docker
- Docker Compose

### Steps

1. Clone the repository

```bash
git clone https://github.com/deepdreamer/car-api
cd car-api
```

2. Copy the environment file and configure your values

```bash
cp .env .env.local
```

3. Build and start the containers

```bash
docker-compose up -d --build
```

4. Install dependencies

```bash
docker-compose exec php composer install
```

5. Create the database and run migrations

```bash
docker-compose exec php php bin/console doctrine:database:create
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

6. Set up the test database

In order for test to pass you have to set up test database

```bash
docker-compose exec db mariadb -u root -proot -e "CREATE DATABASE IF NOT EXISTS car_api_test; GRANT ALL PRIVILEGES ON car_api_test.* TO 'app'@'%'; FLUSH PRIVILEGES;"
```

Then run migrations and load fixtures:

```bash
docker-compose exec php php bin/console doctrine:migrations:migrate --env=test --no-interaction
docker-compose exec php php bin/console doctrine:fixtures:load --env=test --no-interaction
```

The application will be available at [http://localhost:8080](http://localhost:8080).

## API Docs

Interactive API documentation (Swagger UI) is available at [http://localhost:8080/api/index.html](http://localhost:8080/api/index.html) once the application is running.

## Project Structure

| Path | Description |
|------|-------------|
| [`src/Controller/`](src/Controller/) | Route handlers — one controller per resource |
| [`src/Services/`](src/Services/) | Business logic called by controllers |
| [`src/Entity/`](src/Entity/) | Doctrine ORM entities |
| [`src/Repository/`](src/Repository/) | Database query methods |
| [`src/DTO/`](src/DTO/) | Request DTOs used for input validation |
| [`src/DataFixtures/`](src/DataFixtures/) | Seed data used in tests and local development |
| [`tests/Controller/`](tests/Controller/) | Integration tests for each controller |
| [`tests/ApiTestCase.php`](tests/ApiTestCase.php) | Shared test base class with assertion helpers |
| [`public/api/openapi.yaml`](public/api/openapi.yaml) | OpenAPI specification |
| [`migrations/`](migrations/) | Doctrine database migrations |

## Code Quality

All code quality tools can be run via `make`. Requires `make` to be installed on the host.

### Run all linters

```bash
make lint
```

Runs PHPStan and PHPCS together.

### PHPStan (static analysis)

```bash
make phpstan
# or
docker-compose exec php vendor/bin/phpstan analyse
```

### PHP_CodeSniffer (coding standards check)

```bash
make phpcs
# or
docker-compose exec php vendor/bin/phpcs
```

### PHP Code Beautifier and Fixer (auto-fix coding standards)

```bash
make phpcbf
# or
docker-compose exec php vendor/bin/phpcbf
```

### PHPUnit (tests)

```bash
make phpunit
# or
docker-compose exec php vendor/bin/phpunit
```

Run a specific test file:

```bash
docker-compose exec php vendor/bin/phpunit tests/Path/To/YourTest.php
```

Run a specific test method:

```bash
docker-compose exec php vendor/bin/phpunit --filter testMethodName
```
