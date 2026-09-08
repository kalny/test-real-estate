# Test Real Estate

## Установка

### Запуск з Docker

При використанні Docker достатньо клонувати репозиторій та запустити контейнер. Застосунок буде доступний за адресою http://localhost:8081

Якщо необхідно, замініть стандартний порт у `docker/.env`

```bash
git clone https://github.com/kalny/test-real-estate.git

cd test-real-estate

cp .env.example .env

cp docker/.env.example docker/.env

# збілдити і запустити контейнери
make build
make install
make start
make key

# міграції БД
make migrate

# сідер
make seed

# запуск тестів
make test
```

### Запуск без Docker

Якщо ви не використовуєте Docker, то необхідно вказати у файлі `.env` параметри підключення до MySQL та Redis

```bash
git clone https://github.com/kalny/test-real-estate.git

cd test-real-estate

cp .env.example .env

# настройте підключення до БД та Redis
nano .env

composer install

php artisan key:generate

# міграції БД
php artisan migrate

# сідер
php artisan db:seed --class=SupplierSeeder

# запуск тестів
php artisan test 

# воркер
php artisan queue:work
```

## Ідемпотентність та конкурентний доступ

### Імпорт

Ідемпотентність створення імпорту гарантується на рівні бази даних унікальним ключем (`supplier_id`, `external_import_id`). На рівні застосунку використовується `firstOrCreate`.

При повторному надсиланні тієї самої комбінації `supplier_id` та `external_import_id` API повертає 202 Accepted, але новий запис імпорту не створюється, а повторне завдання до черги не додається.

Ідемпотентність обробки імпорту забезпечується песимістичним блокуванням запису імпорту (lockForUpdate) та перевіркою його статусу всередині транзакції. Це гарантує, що один імпорт не буде оброблений декількома одночасними worker-процесами.

### Бронювання

Ідемпотентність бронювання гарантується на рівні бази даних унікальним ключем (`offer_id`, `client_reference`) та на рівні застосунку конструкцією `firstOrCreate`.

При повторному запиті на бронювання того самого offer з тим самим `client_reference` нове бронювання не створюється — клієнту повертається вже існуючий запис бронювання.

### Запобігання подвійному бронюванню

Одночасне бронювання останнього доступного unit запобігається песимістичним блокуванням запису offer (lockForUpdate).

Перевірка `available_units`, його декремент та створення запису в reservations виконуються в межах однієї транзакції. Таким чином, одночасні запити до одного offer виконуються послідовно: лише перший запит може успішно зарезервувати останній доступний unit, а наступні отримають помилку через відсутність доступних units.