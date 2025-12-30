<?php
require 'auth.php';
require_login();

$upload_dir = __DIR__ . '/uploads';

// ---------- УДАЛЕНИЕ ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $file = basename($_POST['delete']);
    $path = $upload_dir . '/' . $file;

    if (is_file($path)) {
        unlink($path);
    }

    header('Location: upload.php');
    exit;
}

// ---------- UPLOAD ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {

    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {

        $orig = basename($_FILES['file']['name']);
        $orig = str_replace(array("\n", "\r"), '', $orig);

        $dest = time() . '_' . $orig;
        move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . '/' . $dest);
    }

    header('Location: upload.php');
    exit;
}

// ---------- ЧТЕНИЕ ----------
$files = array();
if (is_dir($upload_dir)) {
    foreach (scandir($upload_dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        if (is_file($upload_dir . '/' . $f)) {
            $files[] = $f;
        }
    }
}
sort($files);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Files</title>
</head>
<body>

<h1>Files</h1>

<ul>
<?php foreach ($files as $f): ?>
    <li>
        <a href="<?php echo 'uploads/' . rawurlencode($f); ?>" download>
            <?php echo htmlspecialchars($f); ?>
        </a>

        <form method="post" style="display:inline">
            <input type="hidden" name="delete" value="<?php echo htmlspecialchars($f); ?>">
            <button type="submit">Delete</button>
        </form>
    </li>
<?php endforeach; ?>
</ul>

<hr>

<h2>Upload file</h2>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="file">
    <button type="submit">Upload</button>
</form>

<hr>

<br>
<br>
<br>

<a href="index.php">Back</a>

</body>
</html>

