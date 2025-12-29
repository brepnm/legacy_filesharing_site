<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

$path = '/etc/private-site/config.php';

if (file_exists($path)) {
    echo "Config already exists\n";
    exit(1);
}

echo "Enter password: ";
system('stty -echo');
$password = trim(fgets(STDIN));
system('stty echo');
echo "\n";

$hash  = password_hash($password, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));

$config = "<?php\nreturn [\n"
        . "    'password_hash' => '$hash',\n"
        . "    'auth_token'    => '$token',\n"
        . "];\n";

file_put_contents($path, $config);

chmod($path, 0640);
chown($path, 0);           // root
chgrp($path, 'www-data');

echo "Config created: $path\n";
