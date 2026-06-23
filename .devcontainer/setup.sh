#!/bin/bash

# Install dependencies
sudo apt-get update
sudo apt-get install -y apache2 php-mysql wget unzip

# Start MySQL
sudo service mysql start

# Create DB
mysql -u root -e "CREATE DATABASE wordpress;"
mysql -u root -e "CREATE USER 'wpuser'@'localhost' IDENTIFIED BY 'password';"
mysql -u root -e "GRANT ALL PRIVILEGES ON wordpress.* TO 'wpuser'@'localhost';"

# Download WordPress
cd /var/www/html
sudo rm index.html
sudo wget https://wordpress.org/latest.tar.gz
sudo tar -xzf latest.tar.gz --strip-components=1
sudo rm latest.tar.gz

# Set permissions
sudo chown -R www-data:www-data /var/www/html

# Copy your plugin
cp -r /workspaces/* /var/www/html/wp-content/plugins/

# Start Apache
sudo service apache2 start