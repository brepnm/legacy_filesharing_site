<?php
$config = require '/etc/private-site/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['password']) &&
        password_verify($_POST['password'], $config['password_hash'])
    ) {
        setcookie(
            'auth',
            $config['auth_token'],
            time() + 365*24*60*60,
            '/',
            '',
            false,  // http
            true
        );
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid password';
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
</head>
<body>

<form method="post">
    <input type="password" name="password" autofocus>
    <button type="submit">Login</button>
</form>

<?php if ($error): ?>
<p style="color:red"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

</body>
</html>
