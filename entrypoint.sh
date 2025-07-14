#!/bin/bash

# Link storage and run migrations
php artisan storage:link
php artisan migrate --force

# Start scheduler loop in background
(
while true
do
  php artisan schedule:run >> /dev/null 2>&1
  sleep 60
done
) &

# Start Laravel server
php artisan serve --host=0.0.0.0 --port=8080