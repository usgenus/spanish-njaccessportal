<?php
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    send_json(['success' => true]);
}

$db = get_db_data();
$billboards = $db['billboards'] ?? [];

// GET: List billboards
if ($method === 'GET') {
    $id = $_GET['id'] ?? '';
    if ($id) {
        foreach ($billboards as $item) {
            if ($item['id'] === $id) {
                send_json(['success' => true, 'data' => $item]);
            }
        }
        send_json(['success' => false, 'error' => 'Billboard not found.'], 404);
    }

    $activeOnly = isset($_GET['active_only']) && $_GET['active_only'] == '1';
    $result = [];
    foreach ($billboards as $item) {
        if ($activeOnly && isset($item['active']) && ($item['active'] === false || $item['active'] === 0 || $item['active'] === '0' || $item['active'] === 'false')) {
            continue;
        }
        $result[] = $item;
    }

    // Sort by order
    usort($result, function($a, $b) {
        return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
    });

    $defaultCats = ['SPECIAL CAMPAIGN', 'MEDICARE UPDATE', 'PATIENT SUPPORT', 'HEALTH WEBINAR'];
    $dbCats = $db['categories']['billboards'] ?? [];
    $allCats = array_values(array_unique(array_merge($defaultCats, $dbCats)));

    send_json([
        'success' => true,
        'data' => $result,
        'categories' => $allCats
    ]);
}

// Write actions require auth
require_auth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input && !empty($_POST)) {
    $input = $_POST;
}

// POST: Create
if ($method === 'POST') {
    $title = trim($input['title'] ?? '');
    if (!$title) {
        send_json(['success' => false, 'error' => 'Please enter a billboard title.'], 400);
    }

    $newId = 'b_' . time() . '_' . substr(md5(uniqid()), 0, 4);
    $order = count($billboards) + 1;

    $newItem = [
        'id' => $newId,
        'title' => $title,
        'subtitle' => trim($input['subtitle'] ?? ''),
        'category' => trim($input['category'] ?? 'SPECIAL CAMPAIGN'),
        'mediaType' => trim($input['mediaType'] ?? 'image'),
        'mediaUrl' => trim($input['mediaUrl'] ?? ''),
        'videoUrl' => trim($input['videoUrl'] ?? ''),
        'linkUrl' => trim($input['linkUrl'] ?? '/about#contact'),
        'linkText' => trim($input['linkText'] ?? 'Learn More →'),
        'order' => (int)($input['order'] ?? $order),
        'active' => isset($input['active']) ? (bool)$input['active'] : true,
        'createdAt' => date('Y-m-d H:i:s')
    ];

    $billboards[] = $newItem;
    $db['billboards'] = $billboards;

    // Update categories if new
    if (!empty($newItem['category'])) {
        if (!isset($db['categories']['billboards'])) {
            $db['categories']['billboards'] = [];
        }
        if (!in_array($newItem['category'], $db['categories']['billboards'])) {
            $db['categories']['billboards'][] = $newItem['category'];
        }
    }

    save_db_data($db);
    send_json(['success' => true, 'message' => 'Billboard banner created successfully.', 'data' => $newItem]);
}

// PUT: Update
if ($method === 'PUT') {
    $id = $input['id'] ?? ($_GET['id'] ?? '');
    if (!$id) {
        send_json(['success' => false, 'error' => 'Billboard ID is required.'], 400);
    }

    $found = false;
    foreach ($billboards as &$item) {
        if ($item['id'] === $id) {
            if (isset($input['title'])) $item['title'] = trim($input['title']);
            if (isset($input['subtitle'])) $item['subtitle'] = trim($input['subtitle']);
            if (isset($input['category'])) $item['category'] = trim($input['category']);
            if (isset($input['mediaType'])) $item['mediaType'] = trim($input['mediaType']);
            if (isset($input['mediaUrl'])) $item['mediaUrl'] = trim($input['mediaUrl']);
            if (isset($input['videoUrl'])) $item['videoUrl'] = trim($input['videoUrl']);
            if (isset($input['linkUrl'])) $item['linkUrl'] = trim($input['linkUrl']);
            if (isset($input['linkText'])) $item['linkText'] = trim($input['linkText']);
            if (isset($input['order'])) $item['order'] = (int)$input['order'];
            if (isset($input['active'])) $item['active'] = (bool)$input['active'];
            $item['updatedAt'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }

    if (!$found) {
        send_json(['success' => false, 'error' => 'Billboard not found.'], 404);
    }

    $db['billboards'] = $billboards;
    save_db_data($db);
    send_json(['success' => true, 'message' => 'Billboard updated successfully.']);
}

// DELETE: Delete
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? ($input['id'] ?? '');
    if (!$id) {
        send_json(['success' => false, 'error' => 'Billboard ID is required.'], 400);
    }

    $newBillboards = [];
    $found = false;
    foreach ($billboards as $item) {
        if ($item['id'] === $id) {
            $found = true;
        } else {
            $newBillboards[] = $item;
        }
    }

    if (!$found) {
        send_json(['success' => false, 'error' => 'Billboard not found.'], 404);
    }

    $db['billboards'] = $newBillboards;
    save_db_data($db);
    send_json(['success' => true, 'message' => 'Billboard deleted successfully.']);
}

send_json(['success' => false, 'error' => 'Method not allowed.'], 405);
