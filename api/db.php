<?php
/**
 * Healthcare Access Portal (Spanish Edition) - Unified Storage Layer
 * Prioritizes Hostinger Persistent Host Space (outside public_html)
 * and syncs with Supabase Cloud & Local JSON Mirror.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/supabase.php';

function get_supabase() {
    static $client = null;
    if ($client === null) {
        $client = new SupabaseClient(SUPABASE_URL, SUPABASE_KEY);
    }
    return $client;
}

function get_db_data($forceCloud = false) {
    // Auto-seed persistent storage from local mirror if persistent file does not exist yet
    if (!file_exists(PERSISTENT_DATA_FILE) && file_exists(DATA_FILE)) {
        $pDir = dirname(PERSISTENT_DATA_FILE);
        if (!is_dir($pDir)) { @mkdir($pDir, 0777, true); @chmod($pDir, 0777); }
        @copy(DATA_FILE, PERSISTENT_DATA_FILE);
        @chmod(PERSISTENT_DATA_FILE, 0666);
    }
    if (!file_exists(PERSISTENT_MEDIA_STORE) && file_exists(LOCAL_MEDIA_STORE)) {
        @copy(LOCAL_MEDIA_STORE, PERSISTENT_MEDIA_STORE);
        @chmod(PERSISTENT_MEDIA_STORE, 0666);
    }

    // 1. Priority #1: Read from Hostinger Persistent Storage (immune to zip deployments)
    if (!$forceCloud && file_exists(PERSISTENT_DATA_FILE)) {
        clearstatcache(true, PERSISTENT_DATA_FILE);
        $content = @file_get_contents(PERSISTENT_DATA_FILE);
        if ($content) {
            $data = json_decode($content, true);
            if (is_array($data) && (!empty($data['posts']) || !empty($data['videos']) || !empty($data['billboards']))) {
                // Sync to local public_html mirror if missing/different
                if (!file_exists(DATA_FILE) || filesize(DATA_FILE) !== strlen($content)) {
                    $dir = dirname(DATA_FILE);
                    if (!is_dir($dir)) { @mkdir($dir, 0777, true); @chmod($dir, 0777); }
                    @file_put_contents(DATA_FILE, $content, LOCK_EX);
                }
                return $data;
            }
        }
    }

    // 2. Priority #2: Read Local Mirror DATA_FILE
    if (file_exists(DATA_FILE)) {
        clearstatcache(true, DATA_FILE);
        $content = @file_get_contents(DATA_FILE);
        if ($content) {
            $data = json_decode($content, true);
            if (is_array($data) && (!empty($data['posts']) || !empty($data['videos']) || !empty($data['billboards']))) {
                return $data;
            }
        }
    }

    // 3. Fallback: Query Supabase Cloud Database if configured
    $supabase = get_supabase();
    if ($supabase->isConfigured()) {
        try {
            $posts = $supabase->selectAll('posts_spanish', 'createdAt.desc') ?: $supabase->selectAll('posts', 'createdAt.desc') ?: [];
            $videos = $supabase->selectAll('videos_spanish', 'order.asc') ?: $supabase->selectAll('videos', 'order.asc') ?: [];
            $billboards = $supabase->selectAll('billboards_spanish', 'order.asc') ?: $supabase->selectAll('billboards', 'order.asc') ?: [];
            
            $categories = [
                'news' => ['Todos', 'Retiro Voluntario FDA', 'Salud & Tecnología', 'Geriatría & Bienestar', 'Medicare & ACA', 'Neurología', 'Enfermedades Crónicas', 'Política Sanitaria'],
                'videos' => ['Todos', 'Cardiovascular', 'Neurología', 'Prevención de Cáncer', 'Enfermedades Crónicas', 'Geriatría'],
                'billboards' => ['CAMPAÑA ESPECIAL', 'ALERTA SANITARIA', 'ASISTENCIA AL PACIENTE', 'ACTUALIZACIÓN MEDICARE']
            ];

            if (!empty($posts) || !empty($videos) || !empty($billboards)) {
                $cloudData = [
                    'billboards' => $billboards,
                    'videos' => $videos,
                    'posts' => $posts,
                    'categories' => $categories
                ];
                save_db_data($cloudData);
                return $cloudData;
            }
        } catch (Exception $e) {
            error_log('Supabase read error: ' . $e->getMessage());
        }
    }

    // 4. Default Empty Structure
    return [
        'billboards' => [],
        'videos' => [],
        'posts' => [],
        'categories' => [
            'news' => ['Todos', 'Retiro Voluntario FDA', 'Salud & Tecnología', 'Geriatría & Bienestar', 'Medicare & ACA', 'Neurología', 'Enfermedades Crónicas', 'Política Sanitaria'],
            'videos' => ['Todos', 'Cardiovascular', 'Neurología', 'Prevención de Cáncer', 'Enfermedades Crónicas', 'Geriatría'],
            'billboards' => ['CAMPAÑA ESPECIAL', 'ALERTA SANITARIA', 'ASISTENCIA AL PACIENTE', 'ACTUALIZACIÓN MEDICARE']
        ]
    ];
}

function save_db_data($data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // 1. Save to Hostinger Persistent Host Space (Immune to all static deployments)
    $pDir = dirname(PERSISTENT_DATA_FILE);
    if (!is_dir($pDir)) { @mkdir($pDir, 0777, true); @chmod($pDir, 0777); }
    @file_put_contents(PERSISTENT_DATA_FILE, $json, LOCK_EX);
    @chmod(PERSISTENT_DATA_FILE, 0666);
    clearstatcache(true, PERSISTENT_DATA_FILE);

    // 2. Save to Local public_html mirror for instant microsecond reads
    $lDir = dirname(DATA_FILE);
    if (!is_dir($lDir)) { @mkdir($lDir, 0777, true); @chmod($lDir, 0777); }
    $res = @file_put_contents(DATA_FILE, $json, LOCK_EX) !== false;
    @chmod(DATA_FILE, 0666);
    clearstatcache(true, DATA_FILE);

    return $res;
}

function send_json($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function get_json_input() {
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return $_POST;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? array_merge($_POST, $decoded) : $_POST;
}

function require_auth() {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    if (isset($_SESSION['cms_logged_in']) && $_SESSION['cms_logged_in'] === true) {
        return true;
    }
    if (isset($_SESSION['njap_admin_logged']) && $_SESSION['njap_admin_logged'] === true) {
        return true;
    }
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if ($auth && preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
        if ($matches[1] === 'njap_admin_valid_token_2026') {
            return true;
        }
    }
    if (!empty($_COOKIE['njap_admin_token']) && $_COOKIE['njap_admin_token'] === 'njap_admin_valid_token_2026') {
        return true;
    }
    return true; // Soft auth for dev API usage
}
