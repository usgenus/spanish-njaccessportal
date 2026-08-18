<?php
/**
 * Healthcare Access Portal (Spanish Edition) - Media Library API
 */
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    send_json(['success' => true]);
}

require_auth();

$action = $_GET['action'] ?? '';

// GET: List files
if ($method === 'GET') {
    $type = $_GET['type'] ?? 'all';
    $filesMap = [];

    $scan = function($dir, $kind, $prefix) use (&$filesMap) {
        if (!is_dir($dir)) return;
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..' || $f === '.htaccess') continue;
            $full = $dir . '/' . $f;
            if (is_file($full)) {
                if (!isset($filesMap[$f])) {
                    $filesMap[$f] = [
                        'name' => $f,
                        'type' => $kind,
                        'url' => $prefix . '/' . $f,
                        'size' => filesize($full),
                        'mtime' => filemtime($full),
                        'date' => date('Y-m-d H:i', filemtime($full))
                    ];
                }
            }
        }
    };

    if ($type === 'all' || $type === 'images') {
        $scan(PERSISTENT_IMAGES_DIR, 'image', '/uploads/images');
        $scan(LOCAL_IMAGES_DIR, 'image', '/uploads/images');
    }
    if ($type === 'all' || $type === 'videos') {
        $scan(PERSISTENT_VIDEOS_DIR, 'video', '/uploads/videos');
        $scan(LOCAL_VIDEOS_DIR, 'video', '/uploads/videos');
    }

    $files = array_values($filesMap);
    usort($files, function($a, $b) {
        return $b['mtime'] <=> $a['mtime'];
    });

    send_json(['success' => true, 'files' => $files, 'total' => count($files)]);
}

// DELETE: Delete a media item
if ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $url = trim($input['url'] ?? ($_GET['url'] ?? ''));
    if (!$url) {
        send_json(['success' => false, 'error' => 'Se requiere URL del archivo.'], 400);
    }

    $filename = basename($url);
    $isVid = strpos($url, '/videos/') !== false;

    $pPath = ($isVid ? PERSISTENT_VIDEOS_DIR : PERSISTENT_IMAGES_DIR) . '/' . $filename;
    $lPath = ($isVid ? LOCAL_VIDEOS_DIR : LOCAL_IMAGES_DIR) . '/' . $filename;

    if (file_exists($pPath)) @unlink($pPath);
    if (file_exists($lPath)) @unlink($lPath);

    send_json(['success' => true, 'message' => 'Archivo eliminado.']);
}

send_json(['success' => false, 'error' => 'Método no admitido.'], 405);
