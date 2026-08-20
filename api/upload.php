<?php
/**
 * Healthcare Access Portal - Persistent File Upload & Media Library Handler
 * Stores all uploads in Hostinger Persistent Storage (outside public_html)
 * and mirrors into public_html for instant CDN delivery.
 */
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    send_json(['success' => true]);
}

require_auth();

$action = $_GET['action'] ?? '';

// GET: List uploaded files for Media Library (Merges Persistent Storage and Local Mirror)
if ($method === 'GET' && $action === 'list') {
    $type = $_GET['type'] ?? 'all'; // 'images', 'videos', or 'all'
    $filesMap = [];

    // Helper to scan a directory
    $scanFolder = function($dir, $fileType, $urlPrefix) use (&$filesMap) {
        if (!is_dir($dir)) return;
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..' || $f === '.htaccess') continue;
            $path = $dir . '/' . $f;
            if (is_file($path)) {
                if (!isset($filesMap[$f])) {
                    $filesMap[$f] = [
                        'name' => $f,
                        'type' => $fileType,
                        'url' => $urlPrefix . '/' . $f,
                        'size' => filesize($path),
                        'mtime' => filemtime($path)
                    ];
                }
            }
        }
    };

    if ($type === 'all' || $type === 'images') {
        $scanFolder(PERSISTENT_IMAGES_DIR, 'image', '/uploads/images');
        $scanFolder(LOCAL_IMAGES_DIR, 'image', '/uploads/images');
    }

    if ($type === 'all' || $type === 'videos') {
        $scanFolder(PERSISTENT_VIDEOS_DIR, 'video', '/uploads/videos');
        $scanFolder(LOCAL_VIDEOS_DIR, 'video', '/uploads/videos');
    }

    $files = array_values($filesMap);
    usort($files, function($a, $b) {
        return $b['mtime'] <=> $a['mtime'];
    });

    send_json(['success' => true, 'files' => $files]);
}

// DELETE: Remove an uploaded file from both Persistent and Local stores
if ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $url = trim($input['url'] ?? ($_GET['url'] ?? ''));
    if (!$url) {
        send_json(['success' => false, 'error' => 'File URL is required for deletion.'], 400);
    }

    $filename = basename($url);
    $subDir = (strpos($url, '/videos/') !== false) ? 'videos' : 'images';

    $pPath = ($subDir === 'videos' ? PERSISTENT_VIDEOS_DIR : PERSISTENT_IMAGES_DIR) . '/' . $filename;
    $lPath = ($subDir === 'videos' ? LOCAL_VIDEOS_DIR : LOCAL_IMAGES_DIR) . '/' . $filename;

    if (file_exists($pPath)) @unlink($pPath);
    if (file_exists($lPath)) @unlink($lPath);

    // Remove from persistent media store
    if (file_exists(PERSISTENT_MEDIA_STORE)) {
        $store = json_decode(@file_get_contents(PERSISTENT_MEDIA_STORE), true) ?: [];
        if (isset($store[$filename])) {
            unset($store[$filename]);
            @file_put_contents(PERSISTENT_MEDIA_STORE, json_encode($store, JSON_UNESCAPED_SLASHES));
        }
    }

    send_json(['success' => true, 'message' => 'File deleted successfully.']);
}

// POST: Upload File
if ($method === 'POST') {
    if (empty($_FILES['file'])) {
        send_json(['success' => false, 'error' => 'No file attached for upload.'], 400);
    }

    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        send_json(['success' => false, 'error' => 'File upload error (Code ' . $file['error'] . ')'], 400);
    }

    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $allowedImages = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $allowedVideos = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];

    $isImage = in_array($ext, $allowedImages);
    $isVideo = in_array($ext, $allowedVideos);

    if (!$isImage && !$isVideo) {
        send_json(['success' => false, 'error' => 'Unsupported file format. (Allowed: JPG, PNG, WEBP, GIF, SVG, MP4, WEBM, MOV)'], 400);
    }

    $targetSubdir = $isImage ? 'images' : 'videos';

    // Generate safe clean filename
    $rawBase = pathinfo($filename, PATHINFO_FILENAME);
    $cleanBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $rawBase);
    $safeName = $targetSubdir . '_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;

    $pTargetDir = $isImage ? PERSISTENT_IMAGES_DIR : PERSISTENT_VIDEOS_DIR;
    $lTargetDir = $isImage ? LOCAL_IMAGES_DIR : LOCAL_VIDEOS_DIR;

    if (!is_dir($pTargetDir)) { @mkdir($pTargetDir, 0777, true); @chmod($pTargetDir, 0777); }
    if (!is_dir($lTargetDir)) { @mkdir($lTargetDir, 0777, true); @chmod($lTargetDir, 0777); }

    $pTargetPath = $pTargetDir . '/' . $safeName;
    $lTargetPath = $lTargetDir . '/' . $safeName;

    // 1. Move to Persistent Directory (Protected outside public_html)
    if (!move_uploaded_file($file['tmp_name'], $pTargetPath)) {
        // Fallback to local
        if (!copy($file['tmp_name'], $lTargetPath)) {
            send_json(['success' => false, 'error' => 'Failed to save file on server.'], 500);
        }
    }
    @chmod($pTargetPath, 0666);

    // 2. Mirror into Local public_html for instant direct web access
    if (file_exists($pTargetPath) && !file_exists($lTargetPath)) {
        @copy($pTargetPath, $lTargetPath);
        @chmod($lTargetPath, 0666);
    }

    // 3. Persistent Media Store Backup
    $relativeUrl = '/uploads/' . $targetSubdir . '/' . $safeName;
    $dataUrl = '';
    $sourceFile = file_exists($pTargetPath) ? $pTargetPath : $lTargetPath;

    if ($isImage && file_exists($sourceFile) && filesize($sourceFile) < 3000000) {
        $imgData = @file_get_contents($sourceFile);
        if ($imgData) {
            $mime = ($ext === 'svg') ? 'image/svg+xml' : (($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg'));
            $dataUrl = 'data:' . $mime . ';base64,' . base64_encode($imgData);

            $pStoreDir = dirname(PERSISTENT_MEDIA_STORE);
            if (!is_dir($pStoreDir)) { @mkdir($pStoreDir, 0777, true); }
            $store = file_exists(PERSISTENT_MEDIA_STORE) ? json_decode(@file_get_contents(PERSISTENT_MEDIA_STORE), true) : [];
            if (!is_array($store)) $store = [];
            $store[$safeName] = [
                'dataUrl' => $dataUrl,
                'type' => 'image',
                'size' => filesize($sourceFile),
                'created' => time()
            ];
            @file_put_contents(PERSISTENT_MEDIA_STORE, json_encode($store, JSON_UNESCAPED_SLASHES));
            @chmod(PERSISTENT_MEDIA_STORE, 0666);

            $lStoreDir = dirname(LOCAL_MEDIA_STORE);
            if (!is_dir($lStoreDir)) { @mkdir($lStoreDir, 0777, true); }
            @file_put_contents(LOCAL_MEDIA_STORE, json_encode($store, JSON_UNESCAPED_SLASHES));
            @chmod(LOCAL_MEDIA_STORE, 0666);
        }
    }

    send_json([
        'success' => true,
        'message' => 'File uploaded successfully.',
        'url' => $relativeUrl,
        'dataUrl' => $dataUrl,
        'filename' => $safeName,
        'type' => $targetSubdir,
        'size' => file_exists($sourceFile) ? filesize($sourceFile) : 0
    ]);
}

send_json(['success' => false, 'error' => 'Invalid request.'], 400);
