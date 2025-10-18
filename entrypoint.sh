#!/bin/bash
set -e

# 1. Wait for the MySQL container to be ready
# (The 'mysql' part must match your DB_HOST service name in .env)
echo "Waiting for database connection..."

# Use PHP to check the database connection reliably.
# This logic uses environment variables to attempt a real PDO connection.
# CRITICAL FIX: Removed <?php tags as php -r expects raw code and was failing due to shell parsing.
PHP_CHECK='
    $host = getenv("DB_HOST") ?: "mysql";
    $port = getenv("DB_PORT") ?: "3306";
    $user = getenv("DB_USERNAME") ?: "root";
    $pass = getenv("DB_PASSWORD") ?: "";
    $db = getenv("DB_DATABASE") ?: "laravel";

    try {
        new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass, [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        exit(0);
    } catch (PDOException $e) {
        exit(1);
    }
    '

# Loop check using the PHP connection logic (max 30 seconds)
i=0
while ! php -r "$PHP_CHECK"; do
  i=$((i+1))
  if [ $i -gt 30 ]; then
    echo "Database connection failed after 30 seconds."
    exit 1
  fi
  sleep 1
done
echo "Database is up and running!"


# Check if initialization has already been done
if [ ! -f ./.container_initialized ]; then
    echo "Running first-time setup (migrations and seeding)..."

    # 2. Run Migrations
    echo "Running database migrations..."
    php artisan migrate --force

    # 3. Run Seeders
    echo "Running database seeders..."
    php artisan db:seed --force

    # Create a flag file so this block doesn't run again
    touch ./.container_initialized
    echo "Initialization complete."
else
    echo "Container already initialized. Skipping migrations and seeding."
fi

# 4. Start the main application command (PHP-FPM or Artisan Serve)
# CRITICAL FIX: Ensure permissions are set one last time, then execute the default command.
# We are assuming 'start-container' is the default command, or using 'php-fpm' directly.
# Let's use the default Sail entry point command structure.

# Ensure the storage and cache directories are writable by the container user (UID 1000, usually 'sail').
# This is a safety step often required on Windows/Mac.
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

# Execute the container's default command to start the web server (PHP-FPM)
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
