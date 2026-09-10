#!/usr/bin/env bash

set -Eeuo pipefail

project_dir="${PROJECT_DIR:-/opt/scheduler}"
env_file="${ENV_FILE:-.env.production}"
compose_file="${COMPOSE_FILE:-docker-compose.production.yml}"

log() {
    printf '\n[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$1"
}

fail() {
    printf '\nОшибка: %s\n' "$1" >&2
    exit 1
}

trap 'fail "обновление остановлено на строке $LINENO"' ERR

command -v git >/dev/null 2>&1 || fail "команда git не найдена"
command -v docker >/dev/null 2>&1 || fail "команда docker не найдена"
docker compose version >/dev/null 2>&1 || fail "Docker Compose недоступен"

[[ -d "$project_dir/.git" ]] || fail "в $project_dir не найден Git-репозиторий"
cd "$project_dir"

[[ -f "$env_file" ]] || fail "не найден файл окружения $env_file"
[[ -f "$compose_file" ]] || fail "не найден Compose-файл $compose_file"

if [[ -n "$(git status --porcelain --untracked-files=no)" ]]; then
    fail "в репозитории есть локальные изменения отслеживаемых файлов"
fi

compose=(docker compose --env-file "$env_file" -f "$compose_file")

log "Получение обновлений из Git"
git pull --ff-only

log "Проверка production-конфигурации"
"${compose[@]}" config --quiet

log "Сборка Docker-образов"
"${compose[@]}" build --pull

log "Запуск инфраструктурных сервисов"
"${compose[@]}" up -d postgres rabbitmq redis

log "Применение миграций базы данных"
"${compose[@]}" run --rm app php artisan migrate --force --no-interaction

log "Запуск обновлённого приложения"
"${compose[@]}" up -d --remove-orphans

log "Состояние сервисов"
"${compose[@]}" ps

log "Обновление завершено"
