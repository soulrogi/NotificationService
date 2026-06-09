# Микросервис уведомлений (Notification Service)

Микросервис отправки уведомлений. Принимает запросы через HTTP API, сохраняет их в БД, асинхронно отправляет через Kafka и обрабатывает через CLI-воркер. Поддерживает SMS и Email каналы.

## Стек

| Компонент | Технология |
|-----------|------------|
| **Framework** | Laravel 13.x (PHP 8.3+) |
| **Database** | PostgreSQL 18 (Alpine) |
| **Cache / Locking** | Redis 8 (Alpine) |
| **Message Queue** | Apache Kafka 3 |
| **Web Server** | Nginx 1.30 (Alpine) |
| **Контейнеризация** | Docker + Docker Compose |

## Архитектура

Проект использует **Domain-Driven Design (DDD)**:

- **`app/Domain/`** — сущности, value objects, интерфейсы репозиториев и сервисов
- **`app/Application/`** — use cases (оркестрация бизнес-логики)
- **`app/Infrastructure/`** — контроллеры, реализация репозиториев, Kafka-клиенты, DI-провайдеры

```
HTTP POST /api/add → создание уведомлений → Kafka (high/low)
                                           ↓
CLI: notifications:consume ← чтение из Kafka → отправка (SMS/Email)
```

## Эндпоинты API

| Метод | Путь | Описание |
|-------|------|----------|
| `POST` | `/api/add` | Создать уведомление (batch до 1000 получателей) |
| `GET` | `/api/status/{uuid}` | Статус уведомления |
| `GET` | `/api/history/recipient/{id}` | История уведомлений получателя |

## Примеры запросов

[http-example.http](http-example.http)

## Запуск

### Требования

- Docker + Docker Compose

### Быстрый старт

```shell
make init # Инициализация проекта
```

### Управление

```shell
make                    # Список всех команд
make up                 # Запустить контейнеры
make down               # Остановить контейнеры
make restart            # Перезапустить контейнеры
make build              # Пересобрать контейнеры
make php                # Войти в PHP-контейнер
make test               # Запустить тесты
make supervisor         # Зайти в контейнер supervisor
make supervisor-restart # Перезапустить воркеры
```

### CLI-воркеры отправки уведомлений

Ручной запуск:

```shell
php artisan notifications:consume --topic=high
php artisan notifications:consume --topic=low
```

### Supervisor (автоматический запуск воркеров)

При старте `docker compose up -d` автоматически запускается сервис **supervisor**, который управляет двумя процессами-воркерами:

- `consumer-high` — `php artisan notifications:consume --topic=high`
- `consumer-low` — `php artisan notifications:consume --topic=low`

Оба процесса автоматически перезапускаются при падении (`autorestart=true`).

```shell
make supervisor            # Зайти в контейнер supervisor
make restart-supervisor    # Перезапустить воркеры
```

Логи воркеров:
- `docker/supervisor/log/consumer-high.log`
- `docker/supervisor/log/consumer-low.log`

## Документация API (Swagger)

```
http://localhost:8080/api/documentation
```

## Вебморда Kafka

```
http://localhost:8081/
```

Для регенерации документации:

```shell
php artisan l5-swagger:generate
```

## Тестирование

```shell
make test
# или
php artisan test
```
