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
install:
	docker-compose run --rm php-cli composer create-project symfony/skeleton admin-panel-backend --ignore-platform-reqs
psalm:
	docker-compose run --rm php-cli vendor/bin/psalm
