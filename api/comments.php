<?php
/**
 * Healthcare Access Portal (Spanish Edition) - Comments API Endpoint
 * Handles reading, posting, liking, and deleting comments with persistent host storage.
 */
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    send_json(['success' => true]);
}

$commentsFile = PERSISTENT_ROOT . '/comments.json';
$localCommentsFile = __DIR__ . '/../data/comments.json';

// Helper to get comments store
function get_comments_store() {
    global $commentsFile, $localCommentsFile;
    if (file_exists($commentsFile)) {
        clearstatcache(true, $commentsFile);
        $content = @file_get_contents($commentsFile);
        if ($content) {
            $data = json_decode($content, true);
            if (is_array($data)) return $data;
        }
    }
    if (file_exists($localCommentsFile)) {
        $content = @file_get_contents($localCommentsFile);
        if ($content) {
            $data = json_decode($content, true);
            if (is_array($data)) return $data;
        }
    }
    return [
        'eye-drops' => [
            [
                'id' => 'c-sp-1',
                'nickname' => 'María_Hackensack',
                'content' => 'Muchas gracias por publicar esto en español. Mi mamá usa estas gotas de prednisolona, ya mismo llamo a la farmacia para verificar el lote.',
                'createdAt' => '2026-08-04 14:20',
                'likes' => 12,
                'dislikes' => 0,
                'replies' => [
                    [
                        'id' => 'c-sp-1-1',
                        'nickname' => 'CentroSaludNJ',
                        'content' => 'Nos alegra que sea de ayuda. Si tiene dudas adicionales sobre cómo contactar a la FDA o a su farmacéutico, no dude en llamar al 1-800-999-7200.',
                        'createdAt' => '2026-08-04 15:10',
                        'likes' => 5,
                        'dislikes' => 0
                    ]
                ]
            ],
            [
                'id' => 'c-sp-2',
                'nickname' => 'Carlos_NJ',
                'content' => 'Excelente información oportuna. Es fundamental que sigan informando en nuestro idioma.',
                'createdAt' => '2026-08-04 16:45',
                'likes' => 8,
                'dislikes' => 0,
                'replies' => []
            ]
        ],
        'ai-health-assistant' => [
            [
                'id' => 'c-sp-3',
                'nickname' => 'Sra_Elena',
                'content' => 'A mi esposo a veces se le olvida tomar sus pastillas para la presión a la hora correcta. Probaré este tipo de asistente.',
                'createdAt' => '2026-08-03 10:15',
                'likes' => 7,
                'dislikes' => 0,
                'replies' => []
            ]
        ]
    ];
}

function save_comments_store($store) {
    global $commentsFile, $localCommentsFile;
    $json = json_encode($store, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $pDir = dirname($commentsFile);
    if (!is_dir($pDir)) { @mkdir($pDir, 0777, true); @chmod($pDir, 0777); }
    @file_put_contents($commentsFile, $json, LOCK_EX);
    @chmod($commentsFile, 0666);

    $lDir = dirname($localCommentsFile);
    if (!is_dir($lDir)) { @mkdir($lDir, 0777, true); @chmod($lDir, 0777); }
    @file_put_contents($localCommentsFile, $json, LOCK_EX);
    @chmod($localCommentsFile, 0666);
}

$slug = trim($_GET['slug'] ?? ($_POST['slug'] ?? ''));
$store = get_comments_store();

// Admin list all comments
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'all') {
    require_auth();
    $allList = [];
    foreach ($store as $postSlug => $commentsList) {
        if (is_array($commentsList)) {
            foreach ($commentsList as $c) {
                $allList[] = array_merge($c, ['postSlug' => $postSlug]);
                if (!empty($c['replies']) && is_array($c['replies'])) {
                    foreach ($c['replies'] as $rep) {
                        $allList[] = array_merge($rep, ['postSlug' => $postSlug, 'isReply' => true, 'parentId' => $c['id']]);
                    }
                }
            }
        }
    }
    usort($allList, function($a, $b) {
        return strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? '');
    });
    send_json(['success' => true, 'comments' => $allList, 'total' => count($allList)]);
}

// GET: Fetch comments for a post
if ($method === 'GET') {
    if (!$slug) $slug = 'eye-drops';
    $comments = $store[$slug] ?? [];
    send_json([
        'success' => true,
        'slug' => $slug,
        'comments' => $comments
    ]);
}

// POST actions
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $_GET['action'] ?? ($input['action'] ?? 'add');

if ($method === 'POST') {
    if (!$slug) $slug = trim($input['slug'] ?? 'eye-drops');
    $comments = $store[$slug] ?? [];

    // 1. ADD COMMENT
    if ($action === 'add') {
        $nickname = trim($input['nickname'] ?? '');
        $password = trim($input['password'] ?? '');
        $content = trim($input['content'] ?? '');
        $parentId = trim($input['parentId'] ?? '');

        if (!$nickname || !$content) {
            send_json(['success' => false, 'error' => 'Por favor ingrese su apodo y comentario.'], 400);
        }

        $newId = 'c_' . time() . '_' . substr(md5(uniqid()), 0, 4);
        $dateStr = date('Y-m-d H:i');

        if ($parentId) {
            // Reply to an existing comment
            $found = false;
            foreach ($comments as &$c) {
                if ($c['id'] === $parentId) {
                    if (!isset($c['replies']) || !is_array($c['replies'])) {
                        $c['replies'] = [];
                    }
                    $c['replies'][] = [
                        'id' => $newId,
                        'nickname' => $nickname,
                        'password' => $password,
                        'content' => $content,
                        'createdAt' => $dateStr,
                        'likes' => 0,
                        'dislikes' => 0
                    ];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                send_json(['success' => false, 'error' => 'Comentario principal no encontrado.'], 404);
            }
        } else {
            // Root comment
            array_unshift($comments, [
                'id' => $newId,
                'nickname' => $nickname,
                'password' => $password,
                'content' => $content,
                'createdAt' => $dateStr,
                'likes' => 0,
                'dislikes' => 0,
                'replies' => []
            ]);
        }

        $store[$slug] = $comments;
        save_comments_store($store);
        send_json(['success' => true, 'message' => 'Comentario publicado con éxito.', 'commentId' => $newId]);
    }

    // 2. DELETE COMMENT
    if ($action === 'delete') {
        $commentId = trim($input['id'] ?? ($_GET['id'] ?? ''));
        if (!$commentId) {
            send_json(['success' => false, 'error' => 'Se requiere ID de comentario.'], 400);
        }

        // Delete from specific slug or search all slugs
        $deleted = false;
        foreach ($store as $k => &$cList) {
            $newList = [];
            foreach ($cList as $c) {
                if ($c['id'] === $commentId) {
                    $deleted = true;
                    continue;
                }
                if (!empty($c['replies']) && is_array($c['replies'])) {
                    $newReplies = [];
                    foreach ($c['replies'] as $rep) {
                        if ($rep['id'] === $commentId) {
                            $deleted = true;
                            continue;
                        }
                        $newReplies[] = $rep;
                    }
                    $c['replies'] = $newReplies;
                }
                $newList[] = $c;
            }
            $store[$k] = $newList;
        }

        if ($deleted) {
            save_comments_store($store);
            send_json(['success' => true, 'message' => 'Comentario eliminado.']);
        } else {
            send_json(['success' => false, 'error' => 'Comentario no encontrado.'], 404);
        }
    }
}

send_json(['success' => false, 'error' => 'Método no admitido.'], 405);
