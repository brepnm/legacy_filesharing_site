<?php
// ---- CLI only ----
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

$dir  = '/etc/private-site';
$file = $dir . '/config.php';

// ---- create directory if needed ----
if (!is_dir($dir)) {
    mkdir($dir, 0750, true);
    chown($dir, 0);              // root
    chgrp($dir, 'www-data');
}

// ---- prevent overwrite ----
if (file_exists($file)) {
    echo "Config already exists: $file\n";
    exit(1);
}

// ---- read password ----
echo "Enter password: ";
system('stty -echo');
$password = trim(fgets(STDIN));
system('stty echo');
echo "\n";

if ($password === '') {
    echo "Password cannot be empty\n";
    exit(1);
}

// ---- generate secrets ----
$hash  = password_hash($password, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));

// ---- write config ----
$config = <<<PHP
<?php
return [
    'password_hash' => '$hash',
    'auth_token'    => '$token',
];

PHP;

file_put_contents($file, $config);

// ---- permissions ----
chmod($file, 0640);
chown($file, 0);          // root
chgrp($file, 'www-data');

echo "Private site initialized.\n";
echo "Config created at: $file\n";
