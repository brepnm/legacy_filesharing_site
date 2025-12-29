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

# remove file size upload limit
sudo nvim /etc/php/8.3/fpm/php.ini

# change these values
upload_max_filesize = 5G
post_max_size = 5G

sudo systemctl restart php8.3-fpm



# debug
sudo tail -n 50 /var/log/nginx/error.log




