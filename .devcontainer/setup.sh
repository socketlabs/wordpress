
#!/bin/bash

echo "Waiting for WordPress to be ready..."

# Wait until WordPress responds
until curl -s http://localhost:8080 > /dev/null; do
  sleep 3
done

echo "Installing WP-CLI..."

# Install WP-CLI
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp

cd /var/www/html

# Only install if not already installed
if ! wp core is-installed --allow-root; then
  echo "Installing WordPress..."

  wp core install \
    --url="http://localhost:8080" \
    --title="Plugin Test Site" \
    --admin_user="admin" \
    --admin_password="admin" \
    --admin_email="admin@example.com" \
    --allow-root

  echo "Activating plugin..."

  wp plugin activate my-plugin --allow-root
fi

echo "Setup complete!"
