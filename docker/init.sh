#!/bin/sh

set -e

OS=$(uname -s)

cp .env.example .env

if [ "$OS" = "Linux" ]; then
    sed -i "s/^USER_UID=.*/USER_UID=$(id -u)/" .env
    sed -i "s/^USER_GID=.*/USER_GID=$(id -g)/" .env
elif [ "$OS" = "Darwin" ]; then
    sed -i '' "s/^USER_UID=.*/USER_UID=$(id -u)/" ./.env
    sed -i '' "s/^USER_GID=.*/USER_GID=$(id -u)/" ./.envelse
fi

cp docker-compose.override.example.yml docker-compose.override.yml

docker compose build
docker compose up -d --force-recreate

docker compose exec php composer i --prefer-dist --no-progress --no-interaction

docker compose exec kafka sh -c "/opt/kafka/bin/kafka-topics.sh --create --topic high-queue-consumer --bootstrap-server kafka:9092"
docker compose exec kafka sh -c "/opt/kafka/bin/kafka-topics.sh --create --topic low-queue-consumer --bootstrap-server kafka:9092"

docker compose exec php php artisan l5-swagger:generate
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate
