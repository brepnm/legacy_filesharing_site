<?php
require 'auth.php';
require_login();

$download_dir = __DIR__ . '/ytdlp_downloads';
$status_file = __DIR__ . '/ytdlp_status.json';

// Ensure download directory exists
if (!is_dir($download_dir)) {
    mkdir($download_dir, 0755, true);
}

// ---------- УДАЛЕНИЕ ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $file = basename($_POST['delete']);
    $path = $download_dir . '/' . $file;

    if (is_file($path)) {
        unlink($path);
    }

    header('Location: ytdlp.php');
    exit;
}

// ---------- СКАЧИВАНИЕ ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = trim($_POST['url']);
    $download_type = isset($_POST['type']) ? $_POST['type'] : 'video'; // 'video' or 'audio'
    
    if (!empty($url)) {
        // Validate URL format
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            // Generate output filename with timestamp
            $output_template = time() . '_%(title)s.%(ext)s';
            $output_path = $download_dir . '/' . $output_template;
            
            // Build yt-dlp command
            $cmd = escapeshellcmd('yt-dlp') . ' ' . 
                   '--no-warnings ' .
                   '-o ' . escapeshellarg($output_path);
            
            // Add audio extraction options if audio mode
            if ($download_type === 'audio') {
                $cmd .= ' -x --audio-format mp3 --audio-quality 192';
            }
            
            $cmd .= ' ' . escapeshellarg($url) . ' 2>&1';
            
            // Execute in background and capture output
            $output = array();
            $return_var = 0;
            exec($cmd, $output, $return_var);
            
            // Store status
            $status = array(
                'success' => $return_var === 0,
                'timestamp' => time(),
                'url' => $url,
                'type' => $download_type,
                'message' => implode("\n", $output)
            );
            
            file_put_contents($status_file, json_encode($status, JSON_PRETTY_PRINT));
        } else {
            file_put_contents($status_file, json_encode(array(
                'success' => false,
                'timestamp' => time(),
                'url' => $url,
                'message' => 'Invalid URL format'
            ), JSON_PRETTY_PRINT));
        }
    }

    header('Location: ytdlp.php');
    exit;
}

// ---------- ЧТЕНИЕ СТАТУСА ----------
$status = null;
if (file_exists($status_file)) {
    $status_data = json_decode(file_get_contents($status_file), true);
    if ($status_data && (time() - $status_data['timestamp']) < 3600) { // Show status for 1 hour
        $status = $status_data;
    }
}

// ---------- ЧТЕНИЕ ФАЙЛОВ ----------
$files = array();
if (is_dir($download_dir)) {
    foreach (scandir($download_dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        if (is_file($download_dir . '/' . $f)) {
            $files[] = array(
                'name' => $f,
                'size' => filesize($download_dir . '/' . $f),
                'date' => filemtime($download_dir . '/' . $f)
            );
        }
    }
}

// Sort by date descending
usort($files, function($a, $b) {
    return $b['date'] - $a['date'];
});

function format_bytes($bytes) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <!-- <title>yt-dlp Downloader</title> -->
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { margin: 15px 0; padding: 10px; border-radius: 5px; }
        .status.success { background-color: #d4edda; color: #155724; }
        .status.error { background-color: #f8d7da; color: #721c24; }
        .status.info { background-color: #d1ecf1; color: #0c5460; }
        .file-list { margin-top: 20px; }
        .file-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #ddd; margin: 5px 0; border-radius: 3px; }
        .file-info { flex: 1; }
        .file-actions { display: flex; gap: 10px; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        button { padding: 5px 10px; background-color: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background-color: #c82333; }
        input[type="text"] { padding: 8px; width: 50%; }
        input[type="submit"] { padding: 8px 15px; background-color: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; }
        input[type="submit"]:hover { background-color: #218838; }
        hr { margin: 20px 0; }
    </style>
</head>
<body>

<!-- <h1>yt-dlp Video Downloader</h1> -->

<?php if ($status): ?>
<div class="status <?php echo $status['success'] ? 'success' : 'error'; ?>">
    <strong><?php echo $status['success'] ? 'Download Successful!' : 'Download Failed'; ?></strong><br>
    URL: <?php echo htmlspecialchars($status['url']); ?><br>
    Type: <?php echo htmlspecialchars(ucfirst($status['type'] ?? 'video')); ?><br>
    <?php if (!empty($status['message'])): ?>
        <small><?php echo htmlspecialchars($status['message']); ?></small>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- <h2>Download Video</h2> -->
<form method="post">
    <input type="text" name="url" placeholder="Enter video URL (YouTube, etc.)" autofocus required>
    <input type="submit" name="download_type" value="Download Video">
    <input type="submit" name="download_type" value="Download Audio">
    <input type="hidden" name="type" id="download_type_field" value="video">
</form>

<script>
document.querySelectorAll('input[name="download_type"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
        document.getElementById('download_type_field').value = 
            this.value === 'Download Audio' ? 'audio' : 'video';
    });
});
</script>

<hr>

<!-- <h2>Downloaded Files</h2> -->

<?php if (empty($files)): ?>
    <p>No files downloaded yet.</p>
<?php else: ?>
<div class="file-list">
    <?php foreach ($files as $f): ?>
    <div class="file-item">
        <div class="file-info">
            <strong><?php echo htmlspecialchars($f['name']); ?></strong><br>
            <small><?php echo format_bytes($f['size']); ?> • <?php echo date('Y-m-d H:i:s', $f['date']); ?></small>
        </div>
        <div class="file-actions">
            <a href="<?php echo 'ytdlp_downloads/' . rawurlencode($f['name']); ?>" download>Download</a>
            <a href="stream.php?file=<?= rawurlencode($f['name']) ?>" target="_blank"> Stream </a>
            <form method="post" style="display:inline; margin:0;">
                <input type="hidden" name="delete" value="<?php echo htmlspecialchars($f['name']); ?>">
                <button type="submit">Delete</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<hr>

<a href="index.php">Back</a>

</body>
</html>
