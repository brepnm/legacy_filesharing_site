<?php

require 'auth.php';

require_login();

  

$file = __DIR__ . '/links.txt';

  

// ---------- УДАЛЕНИЕ ----------

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {

    $id = (int)$_POST['delete'];

  

    if (file_exists($file)) {

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

  

        if (isset($lines[$id])) {

            unset($lines[$id]);

            $lines = array_values($lines); // FIX бага с первой строкой

            file_put_contents($file, implode("\n", $lines) . "\n", LOCK_EX);

        }

    }

  

    header('Location: index.php');

    exit;

}

  

// ---------- ДОБАВЛЕНИЕ ----------

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['text'])) {

    $text = trim($_POST['text']);

    $text = str_replace(array("\n", "\r"), '', $text);

  

    if ($text !== '') {

        file_put_contents($file, $text . "\n", FILE_APPEND | LOCK_EX);

    }

  

    header('Location: index.php');

    exit;

}

  

// ---------- ЧТЕНИЕ ----------

$links = file_exists($file)

    ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)

    : [];

  

// ---------- ФУНКЦИЯ ОБРЕЗКИ ----------

function short_text($text, $limit = 50) {

    if (strlen($text) <= $limit) {

        return $text;

    }

    return substr($text, 0, $limit - 1) . '…';

}

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="utf-8">

    <title>Urls</title>

</head>

<body>

  

<h1>Urls</h1>

  

<ul>

<?php foreach ($links as $i => $link): ?>

    <?php

        $full = htmlspecialchars($link);

        $short = htmlspecialchars(short_text($link));

    ?>

    <li>

        <a href="<?php echo $full; ?>" target="_blank">

            <?php echo $short; ?>

        </a>

  

        <form method="post" style="display:inline">

            <input type="hidden" name="delete" value="<?php echo $i; ?>">

            <button type="submit">Delete</button>

        </form>

    </li>

<?php endforeach; ?>

</ul>

  

<hr>

  

<h2>Add url</h2>

<form method="post">

    <input type="text" name="text" size="40">

    <button type="submit">Add</button>

</form>

  

<hr>

<a href="logout.php">Exit</a>

  

</body>

</html>
