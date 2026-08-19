<?php
/**
 * Healthcare Access Portal (Spanish Edition) - Backup & Sync Controller
 */
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'OPTIONS') {
    send_json(['success' => true]);
}

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

// Export full database as a downloadable JSON file
if ($action === 'export') {
    $data = get_db_data();
    $filename = 'spanish_portal_backup_' . date('Y-m-d_His') . '.json';
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Download a specific backup snapshot
if ($action === 'download') {
    require_auth();
    $file = basename($_GET['file'] ?? '');
    if (!$file || !preg_match('/^content_backup_.*\.json$/', $file)) {
        send_json(['success' => false, 'error' => 'Archivo inválido.'], 400);
    }
    $path = PERSISTENT_BACKUPS_DIR . '/' . $file;
    if (!file_exists($path)) {
        $path = LOCAL_BACKUPS_DIR . '/' . $file;
    }
    if (!file_exists($path)) {
        send_json(['success' => false, 'error' => 'Respaldo no encontrado.'], 404);
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $file . '"');
    readfile($path);
    exit;
}

// List all backups
if ($action === 'list') {
    require_auth();
    $backups = [];
    $pattern = PERSISTENT_BACKUPS_DIR . '/content_backup_*.json';
    $files = glob($pattern) ?: [];
    
    // Also include local backups if any
    $localFiles = glob(LOCAL_BACKUPS_DIR . '/content_backup_*.json') ?: [];
    $allFiles = array_unique(array_merge($files, $localFiles));

    foreach ($allFiles as $filePath) {
        $basename = basename($filePath);
        $backups[] = [
            'filename' => $basename,
            'size' => filesize($filePath),
            'formattedSize' => round(filesize($filePath) / 1024, 2) . ' KB',
            'createdAt' => date('Y-m-d H:i:s', filemtime($filePath)),
            'timestamp' => filemtime($filePath)
        ];
    }

    usort($backups, function($a, $b) {
        return $b['timestamp'] <=> $a['timestamp'];
    });

    send_json([
        'success' => true,
        'count' => count($backups),
        'backups' => $backups,
        'persistentStorageActive' => file_exists(PERSISTENT_DATA_FILE),
        'persistentDataSize' => file_exists(PERSISTENT_DATA_FILE) ? filesize(PERSISTENT_DATA_FILE) : 0,
        'localDataSize' => file_exists(DATA_FILE) ? filesize(DATA_FILE) : 0,
        'lastSync' => date('Y-m-d H:i:s')
    ]);
}

// Create manual backup
if ($action === 'create' && ($method === 'POST' || $method === 'GET')) {
    require_auth();
    $data = get_db_data();
    create_backup_snapshot();
    send_json([
        'success' => true,
        'message' => 'Respaldo de seguridad creado exitosamente.'
    ]);
}

// Restore from a backup snapshot or uploaded JSON
if ($action === 'restore' && $method === 'POST') {
    require_auth();
    $input = get_json_input();
    $filename = basename($input['filename'] ?? '');

    $targetJson = null;
    if ($filename) {
        $path = PERSISTENT_BACKUPS_DIR . '/' . $filename;
        if (!file_exists($path)) {
            $path = LOCAL_BACKUPS_DIR . '/' . $filename;
        }
        if (file_exists($path)) {
            $targetJson = @file_get_contents($path);
        }
    } elseif (!empty($input['data']) && is_array($input['data'])) {
        $targetJson = json_encode($input['data']);
    }

    if (!$targetJson) {
        send_json(['success' => false, 'error' => 'No se encontró el archivo de respaldo o datos inválidos.'], 400);
    }

    $restoredData = json_decode($targetJson, true);
    if (!is_array($restoredData) || (empty($restoredData['posts']) && empty($restoredData['videos']) && empty($restoredData['billboards']))) {
        send_json(['success' => false, 'error' => 'El formato del respaldo es inválido.'], 400);
    }

    save_db_data($restoredData);
    send_json([
        'success' => true,
        'message' => 'Base de datos restaurada correctamente desde el respaldo.',
        'data' => $restoredData
    ]);
}

// Force 2-way sync
if ($action === 'sync') {
    require_auth();
    $data = get_db_data(true);
    save_db_data($data);
    sync_persistent_media_to_local();
    send_json([
        'success' => true,
        'message' => 'Sincronización bidireccional completada con éxito.'
    ]);
}

send_json(['success' => false, 'error' => 'Acción no válida.'], 400);
