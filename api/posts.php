<?php
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    send_json(['success' => true]);
}

$db = get_db_data();
$posts = $db['posts'] ?? [];

// Helper to make slug
function make_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'post-' . time() : $text;
}

// Helper to sanitize summary points
function sanitize_summary_points($points) {
    if (empty($points)) return [];
    if (is_string($points)) {
        $trimmed = trim($points);
        if (strpos($trimmed, '[') === 0 || strpos($trimmed, '{') === 0) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) $points = $decoded;
            else $points = explode("\n", $points);
        } else {
            $points = explode("\n", $points);
        }
    }
    if (!is_array($points)) return [];

    $clean = [];
    foreach ($points as $pt) {
        if (is_array($pt)) {
            $pt = implode(' ', array_filter($pt, 'is_string'));
        }
        if (is_string($pt)) {
            $t = trim($pt);
            if ($t !== '' && $t !== '[object Object]' && strpos($t, '[object Object]') === false) {
                $clean[] = $t;
            }
        }
    }
    return array_values($clean);
}

// GET: List or single post
if ($method === 'GET') {
    $id = $_GET['id'] ?? '';
    $slug = $_GET['slug'] ?? '';

    if ($id || $slug) {
        foreach ($posts as $item) {
            if (($id && ($item['id'] ?? '') === $id) || ($slug && ($item['slug'] ?? '') === $slug)) {
                send_json(['success' => true, 'data' => $item]);
            }
        }
        send_json(['success' => false, 'error' => 'Article not found.'], 404);
    }

    $category = $_GET['category'] ?? '';
    $q = trim(mb_strtolower($_GET['q'] ?? ''));

    $result = [];
    foreach ($posts as $item) {
        if ($category && $category !== 'Todos' && $category !== 'All' && ($item['category'] ?? '') !== $category) {
            continue;
        }
        if ($q) {
            $title = mb_strtolower($item['title'] ?? '');
            $excerpt = mb_strtolower($item['excerpt'] ?? '');
            $content = mb_strtolower($item['content'] ?? '');
            if (mb_strpos($title, $q) === false && mb_strpos($excerpt, $q) === false && mb_strpos($content, $q) === false) {
                continue;
            }
        }
        $result[] = $item;
    }

    // Sort by latest edit / activity DESC (latest edit at top)
    usort($result, function($a, $b) {
        $t1 = strtotime($a['updatedAt'] ?? $a['createdAt'] ?? $a['date'] ?? '1970-01-01');
        $t2 = strtotime($b['updatedAt'] ?? $b['createdAt'] ?? $b['date'] ?? '1970-01-01');
        return $t2 <=> $t1;
    });

    $defaultCats = ['Medical Column', 'FDA Recall', 'Health & Wellness', 'Medicare & ACA', 'Health Policy & Reports', 'Hospital News', 'Health News'];
    $dbCats = $db['categories']['news'] ?? [];
    $allCats = array_values(array_unique(array_merge($defaultCats, $dbCats)));

    send_json([
        'success' => true,
        'data' => $result,
        'total' => count($result),
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
        send_json(['success' => false, 'error' => 'Please enter an article title.'], 400);
    }

    $newId = 'p_' . time() . '_' . substr(md5(uniqid()), 0, 4);
    $slug = trim($input['slug'] ?? '');
    if (!$slug) {
        $slug = make_slug($title);
    }
    // ensure slug uniqueness
    $slugBase = $slug;
    $counter = 1;
    while (true) {
        $conflict = false;
        foreach ($posts as $p) {
            if (($p['slug'] ?? '') === $slug) {
                $conflict = true;
                break;
            }
        }
        if (!$conflict) break;
        $slug = $slugBase . '-' . $counter;
        $counter++;
    }

    $summaryPoints = sanitize_summary_points($input['summaryPoints'] ?? []);

    // Process multiple images
    $images = $input['images'] ?? [];
    if (is_string($images)) {
        $images = array_filter(array_map('trim', explode("\n", $images)));
    }
    if (!is_array($images)) {
        $images = [];
    }
    $coverImage = trim($input['coverImage'] ?? '');
    if (!empty($images)) {
        $coverImage = $images[0];
    }

    $isTopStory = !empty($input['isTopStory']);
    $isLiveUpdate = !empty($input['isLiveUpdate']);
    $isDoctorColumn = !empty($input['isDoctorColumn']);

    if ($isTopStory) {
        foreach ($posts as &$p) {
            $p['isTopStory'] = false;
        }
    }

    $now = date('Y-m-d H:i:s');
    $newItem = [
        'id' => $newId,
        'title' => $title,
        'slug' => $slug,
        'category' => trim($input['category'] ?? 'Medical Column'),
        'date' => trim($input['date'] ?? date('Y-m-d')),
        'author' => trim($input['author'] ?? 'Editorial Staff'),
        'readTime' => trim($input['readTime'] ?? '3 min read'),
        'coverImage' => $coverImage,
        'images' => array_values($images),
        'videoUrl' => trim($input['videoUrl'] ?? ''),
        'excerpt' => trim($input['excerpt'] ?? ''),
        'content' => trim($input['content'] ?? ''),
        'summaryPoints' => $summaryPoints,
        'isTopStory' => $isTopStory,
        'isLiveUpdate' => $isLiveUpdate,
        'isDoctorColumn' => $isDoctorColumn,
        'status' => trim($input['status'] ?? 'published'),
        'createdAt' => $now,
        'updatedAt' => $now
    ];

    // Put new post at the top of the array
    array_unshift($posts, $newItem);
    $db['posts'] = $posts;

    // Update categories
    if (!empty($newItem['category'])) {
        if (!isset($db['categories']['news'])) {
            $db['categories']['news'] = [];
        }
        if (!in_array($newItem['category'], $db['categories']['news'])) {
            $db['categories']['news'][] = $newItem['category'];
        }
    }

    save_db_data($db);
    send_json(['success' => true, 'message' => 'Article published successfully.', 'data' => $newItem]);
}

// PUT: Update
if ($method === 'PUT') {
    $id = $input['id'] ?? ($_GET['id'] ?? '');
    if (!$id) {
        send_json(['success' => false, 'error' => 'Article ID is required.'], 400);
    }

    $found = false;
    $isTopStory = !empty($input['isTopStory']);

    if ($isTopStory) {
        foreach ($posts as &$p) {
            if ($p['id'] !== $id) {
                $p['isTopStory'] = false;
            }
        }
    }

    $updatedItem = null;
    $newPosts = [];

    foreach ($posts as $item) {
        if ($item['id'] === $id) {
            if (isset($input['title'])) $item['title'] = trim($input['title']);
            if (isset($input['slug']) && trim($input['slug'])) $item['slug'] = trim($input['slug']);
            if (isset($input['category'])) $item['category'] = trim($input['category']);
            if (isset($input['date'])) $item['date'] = trim($input['date']);
            if (isset($input['isTopStory'])) $item['isTopStory'] = (bool)$input['isTopStory'];
            if (isset($input['isLiveUpdate'])) $item['isLiveUpdate'] = (bool)$input['isLiveUpdate'];
            if (isset($input['isDoctorColumn'])) $item['isDoctorColumn'] = (bool)$input['isDoctorColumn'];
            if (isset($input['excerpt'])) $item['excerpt'] = trim($input['excerpt']);
            if (isset($input['coverImage'])) $item['coverImage'] = trim($input['coverImage']);
            if (isset($input['videoUrl'])) $item['videoUrl'] = trim($input['videoUrl']);
            if (isset($input['readTime'])) $item['readTime'] = trim($input['readTime']);
            if (isset($input['author'])) $item['author'] = trim($input['author']);
            if (isset($input['content'])) $item['content'] = trim($input['content']);
            if (isset($input['status'])) $item['status'] = trim($input['status']);

            if (isset($input['summaryPoints'])) {
                $item['summaryPoints'] = sanitize_summary_points($input['summaryPoints']);
            }

            if (isset($input['images'])) {
                $imgs = $input['images'];
                if (is_string($imgs)) {
                    $imgs = array_filter(array_map('trim', explode("\n", $imgs)));
                }
                $item['images'] = is_array($imgs) ? array_values($imgs) : [];
                if (!empty($item['images']) && empty($item['coverImage'])) {
                    $item['coverImage'] = $item['images'][0];
                }
            }

            $item['updatedAt'] = date('Y-m-d H:i:s');
            $updatedItem = $item;
            $found = true;
        } else {
            $newPosts[] = $item;
        }
    }

    if (!$found || !$updatedItem) {
        send_json(['success' => false, 'error' => 'Article not found.'], 404);
    }

    // Move the latest edited post to the very top of the list
    array_unshift($newPosts, $updatedItem);
    $db['posts'] = $newPosts;

    save_db_data($db);
    send_json(['success' => true, 'message' => 'Article updated successfully.', 'data' => $updatedItem]);
}

// DELETE: Delete
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? ($input['id'] ?? '');
    if (!$id) {
        send_json(['success' => false, 'error' => 'Article ID is required.'], 400);
    }

    $newPosts = [];
    $found = false;
    foreach ($posts as $item) {
        if ($item['id'] === $id) {
            $found = true;
        } else {
            $newPosts[] = $item;
        }
    }

    if (!$found) {
        send_json(['success' => false, 'error' => 'Article not found.'], 404);
    }

    $db['posts'] = $newPosts;
    save_db_data($db);
    send_json(['success' => true, 'message' => 'Article deleted successfully.']);
}

send_json(['success' => false, 'error' => 'Method not allowed.'], 405);
