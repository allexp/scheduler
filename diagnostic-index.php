<?php

declare(strict_types=1);

$checks = [];

try {
    $pdo = new PDO(
        sprintf('mysql:host=mysql;dbname=%s;charset=utf8mb4', getenv('MYSQL_DATABASE') ?: 'app'),
        getenv('MYSQL_USER') ?: 'app',
        getenv('MYSQL_PASSWORD') ?: 'app_password',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $checks['MySQL'] = 'доступен (' . $pdo->query('SELECT VERSION()')->fetchColumn() . ')';
} catch (Throwable $exception) {
    $checks['MySQL'] = 'ошибка: ' . $exception->getMessage();
}

$checks['PHP'] = PHP_VERSION;
$checks['AMQP extension'] = extension_loaded('amqp') ? 'установлено' : 'не установлено';

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Docker stack</title>
    <style>
        body { max-width: 760px; margin: 60px auto; padding: 0 20px; font: 18px/1.5 system-ui, sans-serif; color: #20242b; }
        h1 { color: #3157c8; }
        li { margin: 10px 0; }
        code { padding: 2px 6px; background: #eef1f7; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Стек запущен</h1>
    <ul>
        <?php foreach ($checks as $name => $status): ?>
            <li><strong><?= htmlspecialchars($name) ?>:</strong> <?= htmlspecialchars($status) ?></li>
        <?php endforeach; ?>
    </ul>
    <p>RabbitMQ доступен приложению по адресу <code>rabbitmq:5672</code>.</p>
</body>
</html>
