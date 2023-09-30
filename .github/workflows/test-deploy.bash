#!/bin/bash
# export $(xargs < /services/diginext/diginext-new-backend/.env)
# set -e
# docker exec -i diginext-new-db mysqldump --no-tablespaces -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} > /ssd/diginext-new_backups/db-`date +"%Y-%m-%d_%H-%M"`.sql
git submodule update
docker-compose build
docker run --rm --network diginext-new-backend_default --env-file .env --entrypoint php diginext-new-fpm:latest artisan migrate --pretend
docker run --rm --network diginext-new-backend_default --env-file .env --entrypoint php diginext-new-fpm:latest artisan migrate
docker-compose stop fpm
docker-compose rm -f fpm
docker-compose up -d --build
docker-compose stop nginx && docker-compose up -d  # Update nginx upstream URLs
