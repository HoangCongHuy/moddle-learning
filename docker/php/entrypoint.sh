#!/bin/sh
set -e

# Fix ownership of moodledata when the volume is mounted.
if [ -d "/var/www/moodledata" ]; then
    chown -R www-data:www-data /var/www/moodledata
    chmod 775 /var/www/moodledata
fi

exec "$@"
