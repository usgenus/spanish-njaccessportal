<?php
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    send_json(['success' => true]);
}

$db = get_db_data();
$videos = $db['videos'] ?? [];

// Helper to extract YouTube ID
function extract_youtube_id($url) {
    if (preg_match('~(?:youtu\.be/|youtube\.com/(?:embed/|v/|watch\?v=|watch\?.+&v=))([\w-]{11})~', $url, $m)) {
        return $m[1];
    }
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', trim($url))) {
        return trim($url);
    }
    return '';
}

// GET: List or single
if ($method === 'GET') {
    $id = $_GET['id'] ?? '';
    if ($id) {
        foreach ($videos as $item) {
            if ($item['id'] === $id) {
                send_json(['success' => true, 'data' => $item]);
            }
        }
        send_json(['success' => false, 'error' => 'Video not found.'], 404);
    }

    $activeOnly = isset($_GET['active_only']) && $_GET['active_only'] == '1';
    $category = $_GET['category'] ?? '';

    $result = [];
    foreach ($videos as $item) {
        if ($activeOnly && isset($item['active']) && ($item['active'] === false || $item['active'] === 0 || $item['active'] === '0' || $item['active'] === 'false')) {
            continue;
        }
        if ($category && $category !== 'Todos' && $category !== 'All' && $category !== '전체' && ($item['category'] ?? '') !== $category) {
            continue;
        }
        $result[] = $item;
    }

    // Sort by order
    usort($result, function($a, $b) {
        return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
    });

    $defaultCats = ['Cardiovascular', 'Neurology', 'Cancer Prevention & Screening', 'Orthopedics & Joints', 'Chronic Disease Care', 'Medicare & Healthcare Access', 'Health News'];
    $dbCats = $db['categories']['videos'] ?? [];
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
        send_json(['success' => false, 'error' => 'Please enter a video title.'], 400);
    }

    $newId = 'v_' . time() . '_' . substr(md5(uniqid()), 0, 4);
    $order = count($videos) + 1;

    $rawYt = trim($input['youtubeId'] ?? ($input['youtubeUrl'] ?? ''));
    $youtubeId = extract_youtube_id($rawYt);
    $youtubeUrl = trim($input['youtubeUrl'] ?? ($youtubeId ? "https://www.youtube.com/watch?v={$youtubeId}" : ''));

    $videoUrl = trim($input['videoUrl'] ?? '');
    if (!$videoUrl && $youtubeId) {
        $videoUrl = 'https://www.youtube.com/embed/' . $youtubeId . '?autoplay=1';
    }

    $thumbnail = trim($input['thumbnail'] ?? '');
    if (!$thumbnail && $youtubeId) {
        $thumbnail = 'https://img.youtube.com/vi/' . $youtubeId . '/hqdefault.jpg';
    }

    $newItem = [
        'id' => $newId,
        'title' => $title,
        'category' => trim($input['category'] ?? 'Cardiovascular'),
        'doctor' => trim($input['doctor'] ?? ($input['speaker'] ?? 'Medical Specialist')),
        'speaker' => trim($input['speaker'] ?? ($input['doctor'] ?? 'Medical Specialist')),
        'hospital' => trim($input['hospital'] ?? 'Healthcare Access Center NJ'),
        'duration' => trim($input['duration'] ?? '05:20'),
        'views' => trim($input['views'] ?? '12.5K views'),
        'youtubeId' => $youtubeId,
        'youtubeUrl' => $youtubeUrl,
        'videoUrl' => $videoUrl,
        'thumbnail' => $thumbnail,
        'summary' => trim($input['summary'] ?? ($input['description'] ?? '')),
        'description' => trim($input['description'] ?? ($input['summary'] ?? '')),
        'order' => (int)($input['order'] ?? $order),
        'status' => trim($input['status'] ?? 'published'),
        'active' => isset($input['active']) ? (bool)$input['active'] : true,
        'createdAt' => date('Y-m-d H:i:s')
    ];

    $videos[] = $newItem;
    usort($videos, function($a, $b) {
        return ((int)($a['order'] ?? 999)) <=> ((int)($b['order'] ?? 999));
    });
    $db['videos'] = array_values($videos);

    // Update categories
    if (!empty($newItem['category'])) {
        if (!isset($db['categories']['videos'])) {
            $db['categories']['videos'] = [];
        }
        if (!in_array($newItem['category'], $db['categories']['videos'])) {
            $db['categories']['videos'][] = $newItem['category'];
        }
    }

    save_db_data($db);
    send_json(['success' => true, 'message' => 'Video created successfully.', 'data' => $newItem]);
}

// PUT: Update
if ($method === 'PUT') {
    $id = $input['id'] ?? ($_GET['id'] ?? '');
    if (!$id) {
        send_json(['success' => false, 'error' => 'Video ID is required.'], 400);
    }

    $found = false;
    foreach ($videos as &$item) {
        if ($item['id'] === $id) {
            if (isset($input['title'])) $item['title'] = trim($input['title']);
            if (isset($input['category'])) $item['category'] = trim($input['category']);
            if (isset($input['doctor'])) $item['doctor'] = trim($input['doctor']);
            if (isset($input['speaker'])) $item['speaker'] = trim($input['speaker']);
            if (isset($input['hospital'])) $item['hospital'] = trim($input['hospital']);
            if (isset($input['duration'])) $item['duration'] = trim($input['duration']);
            if (isset($input['views'])) $item['views'] = trim($input['views']);
            if (isset($input['youtubeUrl']) || isset($input['youtubeId'])) {
                $rawYt = trim($input['youtubeId'] ?? ($input['youtubeUrl'] ?? ''));
                $ytId = extract_youtube_id($rawYt);
                if ($ytId) {
                    $item['youtubeId'] = $ytId;
                    $item['youtubeUrl'] = "https://www.youtube.com/watch?v={$ytId}";
                    if (empty($input['videoUrl']) && empty($item['videoUrl'])) {
                        $item['videoUrl'] = 'https://www.youtube.com/embed/' . $ytId . '?autoplay=1';
                    }
                } else if (isset($input['youtubeId'])) {
                    $item['youtubeId'] = trim($input['youtubeId']);
                }
            }
            if (isset($input['videoUrl'])) $item['videoUrl'] = trim($input['videoUrl']);
            if (isset($input['thumbnail'])) $item['thumbnail'] = trim($input['thumbnail']);
            if (isset($input['summary'])) $item['summary'] = trim($input['summary']);
            if (isset($input['description'])) $item['description'] = trim($input['description']);
            if (isset($input['order'])) $item['order'] = (int)$input['order'];
            if (isset($input['active'])) $item['active'] = (bool)$input['active'];
            if (isset($input['status'])) $item['status'] = trim($input['status']);

            $item['updatedAt'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }

    if (!$found) {
        send_json(['success' => false, 'error' => 'Video not found.'], 404);
    }

    usort($videos, function($a, $b) {
        return ((int)($a['order'] ?? 999)) <=> ((int)($b['order'] ?? 999));
    });
    $db['videos'] = array_values($videos);
    save_db_data($db);
    send_json(['success' => true, 'message' => 'Video updated successfully.']);
}

// DELETE: Delete
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? ($input['id'] ?? '');
    if (!$id) {
        send_json(['success' => false, 'error' => 'Video ID is required.'], 400);
    }

    $newVideos = [];
    $found = false;
    foreach ($videos as $item) {
        if ($item['id'] === $id) {
            $found = true;
        } else {
            $newVideos[] = $item;
        }
    }

    if (!$found) {
        send_json(['success' => false, 'error' => 'Video not found.'], 404);
    }

    $db['videos'] = $newVideos;
    save_db_data($db);
    send_json(['success' => true, 'message' => 'Video deleted successfully.']);
}

send_json(['success' => false, 'error' => 'Method not allowed.'], 405);
