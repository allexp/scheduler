# Онлайн-запись

Веб-приложение для управления клиентами и записями сотрудников.

## Возможности

- календарь и список записей;
- создание, редактирование и отмена записей;
- карточки клиентов и история их посещений;
- комментарии к клиентам и записям;
- уведомления;
- фильтрация и постраничный просмотр записей;
- роли администратора и сотрудника;
- управление пользователями для администратора;
- журнал изменений.

## Технологический стек

- Laravel 13 и PHP 8.4;
- Vue 3, Vite 8 и Tailwind CSS 4;
- PostgreSQL 17;
- Redis 8;
- RabbitMQ 4;
- Nginx;
- Docker Compose.

## Требования

Для локального запуска необходимы Docker и Docker Compose. Устанавливать PHP,
Composer, Node.js и PostgreSQL на основную систему не требуется.

## Первый запуск

Создайте файлы окружения.

В PowerShell:

```powershell
Copy-Item .env.example .env
Copy-Item src/.env.example src/.env
```

В Bash:

```bash
cp .env.example .env
cp src/.env.example src/.env
```

При необходимости измените параметры PostgreSQL и RabbitMQ. Значения в
корневом `.env` и в `src/.env` должны совпадать.

Соберите и запустите контейнеры:

```bash
docker compose up -d --build
```

Установите PHP-зависимости и подготовьте Laravel:

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Контейнер `frontend` самостоятельно установит npm-зависимости и запустит Vite
в режиме разработки.

## Демонстрационные пользователи

После выполнения сидеров доступны следующие учётные записи:

| Роль | Email | Пароль |
| --- | --- | --- |
| Администратор | `admin@example.com` | `password` |
| Сотрудник | `employee@example.com` | `password` |

Эти данные предназначены только для локальной разработки.

## Адреса сервисов

- приложение: <http://localhost:8080>;
- Vite: <http://localhost:5173>;
- Adminer: <http://localhost:8082>;
- RabbitMQ Management: <http://localhost:15672>.

Для подключения через Adminer используйте:

- систему `PostgreSQL`;
- сервер `postgres`;
- базу данных, пользователя и пароль из корневого `.env`.

Для RabbitMQ Management используйте `RABBITMQ_USER` и `RABBITMQ_PASSWORD` из
корневого `.env`.

## Полезные команды

```bash
docker compose ps
docker compose logs -f
docker compose logs -f app
docker compose logs -f frontend
docker compose logs -f queue
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose down
```

## Тесты и проверка качества

Запуск серверных тестов:

```bash
docker compose exec app php artisan test
```

Проверка и сборка клиентской части:

```bash
docker compose exec frontend npm run lint
docker compose exec frontend npm run format:check
docker compose exec frontend npm run build
```

Те же проверки автоматически выполняются в GitHub Actions при отправке
изменений и создании pull request.

## Xdebug

Xdebug подключается к IDE на основной системе по адресу
`host.docker.internal:9003`. Отладка запускается по триггеру: используйте
браузерное расширение Xdebug Helper или добавьте к запросу параметр
`XDEBUG_TRIGGER=1`.

Пример конфигурации VS Code:

```json
{
    "name": "Listen for Xdebug",
    "type": "php",
    "request": "launch",
    "port": 9003,
    "pathMappings": {
        "/var/www/html": "${workspaceFolder}/src"
    }
}
```

Проверка расширения:

```bash
docker compose exec app php --ri xdebug
```

Режим Xdebug можно изменить переменной `XDEBUG_MODE` в корневом `.env`.

## Данные Docker

PostgreSQL, RabbitMQ, Redis и npm-зависимости используют именованные тома.
Обычная остановка контейнеров не удаляет данные:

```bash
docker compose down
```

Чтобы удалить контейнеры вместе со всеми локальными данными проекта:

```bash
docker compose down -v
```

> Команда `docker compose down -v` необратимо удаляет локальную базу данных,
> данные Redis, RabbitMQ и установленные npm-зависимости.

## Структура проекта

- `src/` — Laravel-приложение и Vue-интерфейс;
- `docker/php/` — образ PHP-FPM и конфигурация Xdebug;
- `docker/nginx/` — конфигурация Nginx;
- `docker-compose.yml` — сервисы локальной инфраструктуры;
- `.github/workflows/ci.yml` — тесты, линтинг и сборка в CI.
