# Test Real Estate

## Installation

При використанні Docker достатньо клонувати репозиторій та запустити контейнер. Застосунок буде доступен за адресою http://localhost:8081

Якщо необхідно, замініть стандартний порт у docker/.env

```bash
git clone https://github.com/kalny/test-real-estate.git

cd test-real-estate

cp .env.example .env

# якщо необхідно, настройте підключення до БД
nano .env

cp docker/.env.example docker/.env

# якщо необхідно, замініть порти за замовчуванням та налаштування з якими буде створено БД
nano docker/.env

# збілдити і запустити контейнери
make build
make start

# міграції БД
make migrate

# phpstan
make analyse

# pint
make format-test

# запуск тестов
make test
```