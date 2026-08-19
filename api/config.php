<?php
/**
 * Global CMS Database, Persistent Storage & Cloud Configuration for Spanish Portal
 */

// 1. Supabase Project Settings
define('SUPABASE_URL', 'https://hjswqohhrrgclosqsikw.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imhqc3dxb2hocnJnY2xvc3FzaWt3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODYzNjMxMTMsImV4cCI6MjEwMTkzOTExM30.cIaGtw9CSLvPib5V6WbB7nM5_AnGg0Iz_rd2ccO52UI');

// 2. Resolve Hostinger Persistent Host Space (Outside public_html so static deployments NEVER wipe data)
function get_persistent_root() {
    $parent = dirname(__DIR__, 2); // e.g. /home/u738358110/domains/spanish2.njaccessportal.com
    $candidates = [
        $parent . '/persistent_storage',
        dirname(__DIR__, 4) . '/cms_persistent_data_spanish',
        dirname(__DIR__, 3) . '/persistent_storage',
        __DIR__ . '/../data'
    ];
    foreach ($candidates as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
            @chmod($dir, 0777);
        }
        if (is_dir($dir) && is_writable($dir)) {
            return realpath($dir) ?: $dir;
        }
    }
    return realpath(__DIR__ . '/../data') ?: (__DIR__ . '/../data');
}

$persistentRoot = get_persistent_root();
define('PERSISTENT_ROOT', $persistentRoot);
define('PERSISTENT_DATA_FILE', PERSISTENT_ROOT . '/content.json');
define('PERSISTENT_MEDIA_STORE', PERSISTENT_ROOT . '/media_store.json');
define('PERSISTENT_IMAGES_DIR', PERSISTENT_ROOT . '/uploads/images');
define('PERSISTENT_VIDEOS_DIR', PERSISTENT_ROOT . '/uploads/videos');
define('PERSISTENT_BACKUPS_DIR', PERSISTENT_ROOT . '/backups');

// Ensure persistent folders exist
if (!is_dir(PERSISTENT_IMAGES_DIR)) { @mkdir(PERSISTENT_IMAGES_DIR, 0777, true); @chmod(PERSISTENT_IMAGES_DIR, 0777); }
if (!is_dir(PERSISTENT_VIDEOS_DIR)) { @mkdir(PERSISTENT_VIDEOS_DIR, 0777, true); @chmod(PERSISTENT_VIDEOS_DIR, 0777); }
if (!is_dir(PERSISTENT_BACKUPS_DIR)) { @mkdir(PERSISTENT_BACKUPS_DIR, 0777, true); @chmod(PERSISTENT_BACKUPS_DIR, 0777); }

// Local public_html mirrors
define('DATA_FILE', __DIR__ . '/../data/content.json');
define('LOCAL_MEDIA_STORE', __DIR__ . '/../data/media_store.json');
define('LOCAL_IMAGES_DIR', __DIR__ . '/../uploads/images');
define('LOCAL_VIDEOS_DIR', __DIR__ . '/../uploads/videos');
define('LOCAL_BACKUPS_DIR', __DIR__ . '/../data/backups');

if (!is_dir(LOCAL_IMAGES_DIR)) { @mkdir(LOCAL_IMAGES_DIR, 0777, true); @chmod(LOCAL_IMAGES_DIR, 0777); }
if (!is_dir(LOCAL_VIDEOS_DIR)) { @mkdir(LOCAL_VIDEOS_DIR, 0777, true); @chmod(LOCAL_VIDEOS_DIR, 0777); }
if (!is_dir(LOCAL_BACKUPS_DIR)) { @mkdir(LOCAL_BACKUPS_DIR, 0777, true); @chmod(LOCAL_BACKUPS_DIR, 0777); }
