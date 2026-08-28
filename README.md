# Ubuntu + PHP Docker stack

Сервисы запускаются отдельно через Docker Compose:

- `app` — Ubuntu 24.04, PHP 8.3 FPM и расширения MySQL/AMQP;
- `nginx` — веб-сервер;
- `mysql` — MySQL 8.4;
- `phpmyadmin` — интерфейс управления MySQL;
- `rabbitmq` — брокер сообщений и Management UI.
- `redis` — Redis с постоянным AOF-хранилищем;

## Запуск

При необходимости измените пароли в `.env`, затем выполните:

```bash
docker compose up -d --build
```

После запуска доступны:

- приложение: http://localhost:8080
- phpMyAdmin: http://localhost:8081
- RabbitMQ Management: http://localhost:15672

## Xdebug

Xdebug подключается к IDE на Windows по адресу `host.docker.internal:9003`.
Отладка запускается по триггеру: используйте браузерное расширение Xdebug Helper
или добавьте к запросу параметр `XDEBUG_TRIGGER=1`.

Для VS Code укажите сопоставление пути:

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
docker compose exec app php -v
docker compose exec app php --ri xdebug
```

Для phpMyAdmin используйте сервер `mysql` и данные `MYSQL_USER` / `MYSQL_PASSWORD`
из `.env`. Для RabbitMQ — `RABBITMQ_USER` / `RABBITMQ_PASSWORD`.

## Команды

```bash
docker compose ps
docker compose logs -f
docker compose down
```

## Laravel и RabbitMQ

Laravel находится в `src`, а очередь обрабатывает отдельный сервис `queue`.
Для тестовой отправки Job откройте:

```text
http://localhost:8080/rabbitmq/test
```

Проверить обработку можно командами:

```bash
docker compose logs queue
docker compose exec app tail -n 20 storage/logs/laravel.log
```

Проверить подключение Laravel к Redis:

```text
http://localhost:8080/redis/test
```

Данные MySQL и RabbitMQ хранятся в именованных томах и сохраняются после
`docker compose down`. Удалить их можно явной командой `docker compose down -v`.
