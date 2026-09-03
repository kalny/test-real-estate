build:
	docker compose -f ./docker/docker-compose.yml build

start:
	docker compose -f ./docker/docker-compose.yml up -d --remove-orphans

stop:
	docker compose -f ./docker/docker-compose.yml down --remove-orphans

shell:
	docker compose -f ./docker/docker-compose.yml exec -u www-data app bash

migrate:
	docker compose -f ./docker/docker-compose.yml exec -u www-data app php artisan migrate

seed:
	docker compose -f ./docker/docker-compose.yml exec -u www-data app php artisan db:seed --class=SupplierSeeder

analyse:
	docker compose -f ./docker/docker-compose.yml exec -u www-data app vendor/bin/phpstan analyse --memory-limit=512M

format:
	docker compose -f ./docker/docker-compose.yml exec -u www-data app vendor/bin/pint

format-test:
	docker compose -f ./docker/docker-compose.yml exec -u www-data app vendor/bin/pint --test

test:
	docker compose -f ./docker/docker-compose.yml exec -u www-data app php artisan test