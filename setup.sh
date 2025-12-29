# install dependencies
sudo apt install nginx
sudo apt install php php-fpm

# give permissions
sudo chown -R www-data:www-data /var/www/private
sudo chmod -R 755 /var/www/private
sudo chown -R www-data:www-data /var/www/private/uploads
sudo chmod 755 /var/www/private/uploads
sudo chown root:root /etc/private-site/config.php
sudo chmod 600 /etc/private-site/config.php


# to get php version
ls /run/php/

# enable and start site
sudo ln -s /etc/nginx/sites-available/private /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx


# debug
sudo tail -n 50 /var/log/nginx/error.log


php -r "echo password_hash('password', PASSWORD_DEFAULT);" # change 'password' to your desired password
php -r "echo bin2hex(random_bytes(32));"




