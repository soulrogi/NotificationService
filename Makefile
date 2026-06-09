.DEFAULT_GOAL:=help

####################################################################################################
# Выводит описание целей - все, что написано после двойного диеза (##) через пробел
####################################################################################################
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-16s\033[0m %s\n", $$1, $$2}'

init: ## Инициализация приложения
	sh ./docker/init.sh

restart: down up ## Перезапуск docker-контейнеров

stop: ## Остановка docker-контейнеровё
	docker compose stop

down: ## Остановка и удаление docker-контейнеров
	docker compose down --remove-orphans

clear: ## Остановка и удаление docker-контейнеров и удаление томов
	docker compose down --remove-orphans -v

up: ## Запуск docker-контейнеров
	docker compose up -d

build: ## Собрать docker-контейнеры
	docker compose build

ps: ## Запущенные контейнеры
	docker compose ps

composer-install: ## Установить зависимости
	docker compose exec php composer i

composer-dump-autoload: ## Обновить карту классов
	docker compose exec php composer dump-autoload --optimize

create-kafka-topics: ## Создать топики в кафке
	docker compose exec kafka sh -c "/opt/kafka/bin/kafka-topics.sh --create --topic high-queue-consumer --bootstrap-server kafka:9092"
	docker compose exec kafka sh -c "/opt/kafka/bin/kafka-topics.sh --create --topic low-queue-consumer --bootstrap-server kafka:9092"

php: ## Зайти в контейнер php
	docker compose exec php sh

php-root: ## Зайти в контейнер php под пользователем root
	docker compose exec -u root php sh

supervisor: ## Зайти в контейнер supervisor
	docker compose exec supervisor sh

supervisor-status: ## Статус воркеров supervisor
	docker compose exec supervisor supervisorctl -s unix:///var/run/supervisor/supervisor.sock status

supervisor-restart: ## Перезапустить supervisor
	docker compose exec supervisor supervisorctl -s unix:///var/run/supervisor/supervisor.sock reload
	sleep 2
	docker compose exec supervisor supervisorctl -s unix:///var/run/supervisor/supervisor.sock start all

test: ## Запустить тесты
	docker compose exec php php artisan test

migrate: ## Запустить миграции
	docker compose exec php php artisan migrate

swagger: ## Cгенерировать описание Api
	docker compose exec php php artisan l5-swagger:generate

log: ## Логи контейнеров
	docker compose logs -f

debug-on: ## Включить режим дебага php
	docker compose exec -u root php sed -i '/xdebug.mode=/c\xdebug.mode=debug,develop' /usr/local/etc/php/conf.d/xdebug.ini
	docker compose exec -u root php kill -USR2 1

debug-off: ## Отключить режим дебага php
	docker compose exec -u root php sed -i '/xdebug.mode=/c\xdebug.mode=off' /usr/local/etc/php/conf.d/xdebug.ini
	docker compose exec -u root php kill -USR2 1
