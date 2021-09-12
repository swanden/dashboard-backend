restart: down up
init: clear build up composer-install oauth-keys migrate scheme-update fixtures oauth-client

up:
	docker-compose up -d
down:
	docker-compose down --remove-orphans
clear:
	docker-compose down -v --remove-orphans
build:
	docker-compose build
test:
	docker-compose run --rm php-cli php bin/phpunit
unit:
	docker-compose run --rm php-cli php bin/phpunit --testsuite=unit
functional:
	docker-compose run --rm php-cli php bin/phpunit --testsuite=functional
composer-install:
	docker-compose run --rm php-cli composer install
install:
	docker-compose run --rm php-cli composer create-project symfony/skeleton goodlift --ignore-platform-reqs
psalm:
	docker-compose run --rm php-cli vendor/bin/psalm
oauth-keys:
	docker-compose run --rm php-cli mkdir -p var/oauth
	docker-compose run --rm php-cli openssl genrsa -out var/oauth/private.key 2048
	docker-compose run --rm php-cli openssl rsa -in var/oauth/private.key -pubout -out var/oauth/public.key
	docker-compose run --rm php-cli chmod 644 var/oauth/private.key var/oauth/public.key
migrate:
	docker-compose run --rm php-cli bin/console doctrine:migrations:migrate --no-interaction
scheme-update:
	docker-compose run --rm php-cli bin/console doctrine:schema:update --force
fixtures:
	docker-compose run --rm php-cli bin/console doctrine:fixtures:load --no-interaction
oauth-client:
	docker-compose run --rm php-cli bin/console trikoder:oauth2:create-client client secret