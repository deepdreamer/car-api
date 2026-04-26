phpcs:
	docker-compose exec php vendor/bin/phpcs

phpcbf:
	docker-compose exec php vendor/bin/phpcbf

phpstan:
	docker-compose exec php vendor/bin/phpstan analyse --memory-limit=512M

phpunit:
	docker-compose exec php vendor/bin/phpunit

lint: phpcs phpstan
