#!/bin/bash

# Exit on any error
set -e

# 1. Clone the Repository
echo "Cloning the repository..."
git clone git@github.com:gregoryoliveiraa/canoe.git
cd canoe

# 2. Copy Environment File
echo "Copying .env.example to .env..."
cp .env.example .env

# 3. Install Dependencies
echo "Installing PHP dependencies using Composer..."
composer install

# 4. Start Docker Containers
echo "Starting Docker containers..."
docker-compose up -d

# Wait a few seconds to ensure that the containers are fully up before proceeding
sleep 10

# 5. Enter Docker Container and setup Laravel
docker exec -it canoetechassessment-laravel-1 bash -c "
echo 'Generating application key...';
php artisan key:generate;

echo 'Running database migrations...';
php artisan migrate;

echo 'Running migrations for testing database...';
php artisan migrate --database=testing;
"

# 6. (Optional) Initiate Web Server - This is commented out because this will block the terminal.
echo "Starting Laravel web server..."
php artisan serve

echo "Setup complete! You may now manually start the server using 'php artisan serve' if needed."

