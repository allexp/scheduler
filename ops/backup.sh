#!/usr/bin/env bash

set -Eeuo pipefail

project_dir="/opt/scheduler"
backup_dir="/var/backups/scheduler"
compose_file="docker-compose.production.yml"
timestamp="$(date -u +%Y%m%dT%H%M%SZ)"

umask 077
mkdir -p "$backup_dir"

cd "$project_dir"
set -a
# Файл содержит только серверные секреты и имеет права доступа 600.
source .env.production
set +a

docker compose --env-file .env.production -f "$compose_file" exec -T postgres \
    pg_dump --clean --if-exists --no-owner --no-privileges \
    --username "$POSTGRES_USER" "$POSTGRES_DB" \
    | gzip -9 > "$backup_dir/postgres-$timestamp.sql.gz"

# Локально храним семь последних ежедневных копий.
find "$backup_dir" -type f -name 'postgres-*.sql.gz' -mtime +7 -delete
