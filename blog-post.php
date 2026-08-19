<?php
/**
 * Healthcare Access Portal (Spanish Edition) - Full Screen Article Reader Page
 */
require_once __DIR__ . '/api/db.php';

$slug = $_GET['slug'] ?? '';
$id = $_GET['id'] ?? '';

$db = get_db_data();
$posts = $db['posts'] ?? [];

$post = null;
$prevPost = null;
$nextPost = null;
$relatedPosts = [];

if ($slug || $id) {
    foreach ($posts as $idx => $p) {
        if (($slug && ($p['slug'] ?? '') === $slug) || ($id && ($p['id'] ?? '') === $id)) {
            $post = $p;
            $prevPost = $posts[$idx + 1] ?? null;
            $nextPost = $posts[$idx - 1] ?? null;
            break;
        }
    }
}

// Fallback: search by title or substring if slug not exact
if (!$post && ($slug || $id)) {
    $searchTarget = $slug ?: $id;
    foreach ($posts as $p) {
        if (($p['id'] ?? '') === $searchTarget || ($p['slug'] ?? '') === $searchTarget) {
            $post = $p;
            break;
        }
    }
}

// If still not found, default to first post
if (!$post && !empty($posts)) {
    $post = $posts[0];
}

$title = htmlspecialchars($post['title'] ?? 'Noticia de Salud y Política Sanitaria');
$category = htmlspecialchars($post['category'] ?? 'Noticias de Salud');
$date = htmlspecialchars($post['date'] ?? ($post['createdAt'] ?? date('Y-m-d')));
$author = htmlspecialchars($post['author'] ?? 'Redacción Médica y Salud Pública');
$readTime = htmlspecialchars($post['readTime'] ?? '3 min de lectura');
$excerpt = htmlspecialchars($post['excerpt'] ?? '');
$images = $post['images'] ?? [];
if (empty($images) && !empty($post['coverImage'])) {
    $images = [$post['coverImage']];
}
$coverImage = htmlspecialchars(!empty($images[0]) ? $images[0] : ($post['coverImage'] ?: 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=1200&q=80&auto=format'));
$articleImages = array_slice($images, 1);
$content = $post['content'] ?? '';
$summaryPoints = $post['summaryPoints'] ?? [];
$videoUrl = $post['videoUrl'] ?? '';
$postSlug = htmlspecialchars($post['slug'] ?? ($post['id'] ?? 'noticia'));

// Find related articles (same category or others)
foreach ($posts as $p) {
    if (($p['id'] ?? '') !== ($post['id'] ?? '') && count($relatedPosts) < 3) {
        $relatedPosts[] = $p;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?> | EL PORTAL DE SALUD DE NJ</title>
  <meta name="description" content="<?= $excerpt ?>">
  <meta property="og:title" content="<?= $title ?>">
  <meta property="og:description" content="<?= $excerpt ?>">
  <meta property="og:image" content="<?= $coverImage ?>">
  <meta property="og:type" content="article">
  <link rel="stylesheet" href="/styles.css?v=<?= time() ?>">
  <style>
    .article-hero {
      background: linear-gradient(180deg, #111111 0%, #1e1e24 100%);
      color: #ffffff;
      padding: 3.5rem 1.5rem 2.5rem;
      border-bottom: 4px solid var(--color-news-red);
    }
    .article-container {
      max-width: 860px;
      margin: 0 auto;
    }
    .article-body-text {
      font-family: var(--font-serif);
      font-size: 1.05rem;
      line-height: 1.85;
      color: #1f2937;
    }
    .article-body-text p {
      margin-bottom: 1.35rem;
    }
    .article-body-text h2, .article-body-text h3 {
      margin: 2rem 0 1rem;
      color: #111111;
      font-size: 1.4rem;
    }
    .article-body-text ul, .article-body-text ol {
      margin: 1rem 0 1.5rem 1.5rem;
    }
    .article-body-text li {
      margin-bottom: 0.5rem;
    }
    .post-nav-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      padding: 1rem 1.25rem;
      border-radius: 4px;
      transition: border-color 0.2s, transform 0.2s;
      flex: 1;
      min-width: 260px;
    }
    .post-nav-card:hover {
      border-color: var(--color-news-red);
      transform: translateY(-2px);
    }
  </style>
</head>
<body>

  <!-- Top Marquee Flash Bar -->
  <div class="top-marquee">
    <div class="marquee-content">
      <div class="marquee-item">
        <span class="marquee-badge">COBERTURA ESPECIAL</span> NOTICIAS MÉDICAS, ALERTAS SANITARIAS Y ASISTENCIA AL PACIENTE EN ESPAÑOL
      </div>
      <div class="marquee-item">
        <span class="marquee-badge">LÍNEA GRATUITA</span> ORIENTACIÓN DE MEDICARE &amp; ACA OBAMACARE EN NUEVA JERSEY: 1-800-999-7200
      </div>
    </div>
  </div>

  <!-- Sticky Header Container -->
  <header class="news-header-sticky">
    <div class="masthead-section">
      <div class="masthead-container">
        <a href="/" class="masthead-logo">
          <span class="masthead-title">EL PORTAL DE SALUD</span>
          <span class="masthead-sub">PORTAL DE INFORMACIÓN Y NAVEGACIÓN SANITARIA DE NJ</span>
        </a>
      </div>
    </div>

    <nav class="category-nav">
      <div class="nav-container">
        <ul class="nav-links-row">
          <li><a href="/" class="nav-item-link">Portada</a></li>
          <li><a href="/noticias.html" class="nav-item-link active">Noticias de Salud</a></li>
          <li><a href="/medicare.html" class="nav-item-link">Medicare &amp; ACA</a></li>
          <li><a href="/servicio-premium-de-navegacion-y-acceso-a-la-salud.html" class="nav-item-link">Servicio Premium AI</a></li>
          <li><a href="/about.html" class="nav-item-link">Sobre Nosotros</a></li>
        </ul>

        <div style="display:flex; align-items:center; gap:1rem;">
          <a href="/admin/" class="nav-item-link" style="font-size:0.75rem; background:rgba(0,0,0,0.06); padding:0.3rem 0.6rem; border-radius:3px;">CMS Admin</a>
          <button class="mobile-menu-btn" id="mobileMenuToggle">☰</button>
        </div>
      </div>
    </nav>
  </header>

  <div class="header-spacer"></div>

  <!-- Full Screen Article Main Container -->
  <main style="flex:1; background:#fcfcfc;">

    <!-- Article Hero Banner -->
    <section class="article-hero">
      <div class="article-container">
        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem; flex-wrap:wrap;">
          <span style="background:var(--color-news-red); color:#fff; font-size:0.75rem; font-weight:800; padding:0.25rem 0.65rem; border-radius:2px; text-transform:uppercase; letter-spacing:0.05em;">
            <?= $category ?>
          </span>
          <span style="color:rgba(255,255,255,0.6); font-size:0.8rem;">·</span>
          <span style="color:rgba(255,255,255,0.85); font-size:0.8rem;">📅 <?= $date ?></span>
          <span style="color:rgba(255,255,255,0.6); font-size:0.8rem;">·</span>
          <span style="color:rgba(255,255,255,0.85); font-size:0.8rem;">⏱ <?= $readTime ?></span>
        </div>

        <h1 style="font-family:var(--font-serif); font-size:clamp(1.75rem, 4vw, 2.75rem); font-weight:900; line-height:1.2; color:#ffffff; margin-bottom:1.25rem;">
          <?= $title ?>
        </h1>

        <div style="display:flex; align-items:center; gap:0.75rem; font-size:0.85rem; color:rgba(255,255,255,0.75); border-top:1px solid rgba(255,255,255,0.15); padding-top:0.85rem;">
          <div style="width:32px; height:32px; border-radius:50%; background:var(--color-news-red); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.8rem;">
            <?= mb_substr($author, 0, 1) ?>
          </div>
          <div>
            <div style="font-weight:700; color:#ffffff;"><?= $author ?></div>
            <div style="font-size:0.75rem; color:rgba(255,255,255,0.6);">Servicio Informativo para la Comunidad Hispana de NJ</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content Container -->
    <div class="article-container" style="padding: 2.5rem 1.5rem 4rem;">

      <!-- Big Feature Image -->
      <?php if (!empty($coverImage)): ?>
      <div style="width:100%; max-height:480px; overflow:hidden; border-radius:4px; margin-bottom:2rem; box-shadow:0 4px 15px rgba(0,0,0,0.06); background:#000;">
        <img src="<?= $coverImage ?>" alt="<?= $title ?>" style="width:100%; height:100%; object-fit:cover; display:block;">
      </div>
      <?php endif; ?>

      <!-- Excerpt Box -->
      <?php if (!empty($excerpt)): ?>
      <div style="background:#ffffff; border-left:4px solid var(--color-news-red); border:1px solid #e2e8f0; border-left-width:4px; padding:1.25rem 1.5rem; margin-bottom:2rem; border-radius:2px; box-shadow:0 2px 4px rgba(0,0,0,0.02);">
        <p style="font-family:var(--font-sans); font-size:1rem; font-weight:600; color:#1e293b; line-height:1.6; margin:0;">
          <?= $excerpt ?>
        </p>
      </div>
      <?php endif; ?>

      <!-- Key Summary Points -->
      <?php if (!empty($summaryPoints)): ?>
      <div style="background:#f8fafc; border:1px solid #cbd5e1; padding:1.25rem 1.5rem; border-radius:4px; margin-bottom:2rem;">
        <h4 style="font-family:var(--font-sans); font-size:0.8rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--color-news-red); margin-bottom:0.75rem;">
          Puntos Clave del Reporte
        </h4>
        <ul style="margin:0; padding-left:1.25rem; font-size:0.95rem; color:#334155; line-height:1.6;">
          <?php foreach ($summaryPoints as $pt): ?>
            <?php if (trim($pt)): ?>
              <li style="margin-bottom:0.4rem;"><?= htmlspecialchars($pt) ?></li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <!-- Video Embed (if attached) -->
      <?php if (!empty($videoUrl)): ?>
      <div style="width:100%; aspect-ratio:16/9; background:#000; border-radius:4px; overflow:hidden; margin-bottom:2rem; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
        <?php if (str_contains($videoUrl, '.mp4')): ?>
          <video src="<?= htmlspecialchars($videoUrl) ?>" controls style="width:100%; height:100%; object-fit:cover;"></video>
        <?php elseif (preg_match('~(?:youtu\.be/|youtube\.com/(?:embed/|v/|watch\?v=))([\w-]{11})~', $videoUrl, $m)): ?>
          <iframe src="https://www.youtube-nocookie.com/embed/<?= $m[1] ?>?autoplay=0&rel=0" style="width:100%; height:100%; border:none;" allowfullscreen></iframe>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Article Body Paragraphs -->
      <div class="article-body-text">
        <?= $content ?>
      </div>

      <!-- Additional Photos Gallery -->
      <?php if (!empty($articleImages)): ?>
      <div style="margin: 3rem 0; padding-top: 1.5rem; border-top: 1px solid #e2e8f0;">
        <h3 style="font-family:var(--font-sans); font-size:0.85rem; font-weight:800; text-transform:uppercase; color:var(--color-news-black); margin-bottom:1rem;">
          Galería de Imágenes &amp; Documentos (<?= count($articleImages) ?>)
        </h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:1rem;">
          <?php foreach ($articleImages as $gImg): ?>
          <div style="border-radius:4px; overflow:hidden; border:1px solid #e2e8f0; height:200px; background:#f1f5f9;">
            <img src="<?= htmlspecialchars($gImg) ?>" alt="<?= $title ?>" style="width:100%; height:100%; object-fit:cover;">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>


      <!-- Next & Previous Post Navigation -->
      <div style="display:flex; flex-wrap:wrap; gap:1rem; margin-bottom:3rem; padding-top:1.5rem; border-top:1px solid #e2e8f0;">
        <?php if ($prevPost): 
          $prevSlug = $prevPost['slug'] ?: $prevPost['id'];
        ?>
        <a href="/blog-post.php?slug=<?= urlencode($prevSlug) ?>" class="post-nav-card">
          <span style="font-size:0.7rem; font-weight:800; color:#64748b; text-transform:uppercase;">← Noticia Anterior</span>
          <h4 style="font-size:0.9rem; font-weight:700; color:#111111; margin-top:0.25rem; line-height:1.3;"><?= htmlspecialchars($prevPost['title'] ?? '') ?></h4>
        </a>
        <?php endif; ?>

        <?php if ($nextPost): 
          $nextSlug = $nextPost['slug'] ?: $nextPost['id'];
        ?>
        <a href="/blog-post.php?slug=<?= urlencode($nextSlug) ?>" class="post-nav-card" style="text-align:right;">
          <span style="font-size:0.7rem; font-weight:800; color:#64748b; text-transform:uppercase;">Siguiente Noticia →</span>
          <h4 style="font-size:0.9rem; font-weight:700; color:#111111; margin-top:0.25rem; line-height:1.3;"><?= htmlspecialchars($nextPost['title'] ?? '') ?></h4>
        </a>
        <?php endif; ?>
      </div>

      <!-- Real-Time Community Comments Section -->
      <section style="background:#ffffff; border:1px solid #e2e8f0; border-top:3px solid var(--color-news-red); padding:1.75rem; border-radius:4px; margin-bottom:3rem; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="font-family:var(--font-sans); font-size:1.15rem; font-weight:900; color:#111111; margin-bottom:1rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
          <span>💬 Comentarios de la Comunidad (<span id="commentCount-<?= $postSlug ?>">0</span>)</span>
          <span style="font-size:0.75rem; color:#c91818; background:#fef2f2; padding:0.2rem 0.6rem; border-radius:2px; font-weight:800;">Sin necesidad de registro</span>
        </h3>

        <!-- Comment Input Form -->
        <form onsubmit="event.preventDefault(); (window.submitCommentApi ? window.submitCommentApi('<?= $postSlug ?>') : null)" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; padding:1.25rem; margin-bottom:1.5rem;">
          <div style="margin-bottom:0.85rem;">
            <label style="display:block; font-size:0.75rem; font-weight:800; color:#111111; text-transform:uppercase; margin-bottom:0.3rem;">Apodo / Nickname:</label>
            <input type="text" id="commentNickname-<?= $postSlug ?>" placeholder="Ej. Maria_Passaic o Juan_Bergen" required style="width:100%; padding:0.6rem 0.85rem; border-radius:2px; border:1px solid #cbd5e1; font-size:0.875rem; outline:none; font-family:var(--font-sans);">
          </div>
          <div style="margin-bottom:0.85rem;">
            <label style="display:block; font-size:0.75rem; font-weight:800; color:#111111; text-transform:uppercase; margin-bottom:0.3rem;">Su Comentario o Consulta:</label>
            <textarea id="commentText-<?= $postSlug ?>" rows="3" placeholder="Escriba su opinión o pregunta sobre este reporte..." required style="width:100%; padding:0.6rem 0.85rem; border-radius:2px; border:1px solid #cbd5e1; font-size:0.875rem; outline:none; font-family:var(--font-sans);"></textarea>
          </div>
          <button type="submit" class="btn-news-red" style="font-size:0.8rem; padding:0.5rem 1.25rem; cursor:pointer;">Publicar Comentario</button>
        </form>

        <!-- Comments Output List -->
        <div id="commentsList-<?= $postSlug ?>" style="display:flex; flex-direction:column; gap:0.85rem;">
          <p style="font-size:0.85rem; color:#64748b; font-style:italic;">Cargando comentarios...</p>
        </div>
      </section>

      <!-- Related News Section -->
      <?php if (!empty($relatedPosts)): ?>
      <section style="border-top: 2px solid #111111; padding-top: 2rem;">
        <div class="news-section-header" style="margin-bottom:1.5rem;">
          <h2 class="news-section-title">NOTICIAS RELACIONADAS</h2>
          <a href="/noticias.html" class="news-section-more">Ver Todo →</a>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1.5rem;">
          <?php foreach ($relatedPosts as $rel): 
            $relSlug = $rel['slug'] ?: $rel['id'];
            $relCover = $rel['coverImage'] ?: (!empty($rel['images'][0]) ? $rel['images'][0] : 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=800');
          ?>
          <a href="/blog-post.php?slug=<?= urlencode($relSlug) ?>" class="news-card" style="cursor:pointer; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <div class="card-img-box">
                <img src="<?= htmlspecialchars($relCover) ?>" alt="<?= htmlspecialchars($rel['title'] ?? '') ?>">
              </div>
              <div class="card-kicker"><?= htmlspecialchars(mb_strtoupper($rel['category'] ?? 'SALUD')) ?></div>
              <h3 class="card-head"><?= htmlspecialchars($rel['title'] ?? '') ?></h3>
              <p class="card-body"><?= htmlspecialchars($rel['excerpt'] ?? '') ?></p>
            </div>
            <div class="card-foot">
              <span><?= htmlspecialchars($rel['date'] ?? '') ?></span>
              <span style="color:var(--color-news-red); font-weight:700;">Leer artículo →</span>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

    </div>
  </main>

  <!-- Footer -->
  <footer class="commercial-footer">
    <div class="footer-inner">
      <div class="footer-top-grid">
        <div>
          <div class="footer-column-title">EL PORTAL DE SALUD</div>
          <p style="font-size:0.8rem; color:rgba(255,255,255,0.7); line-height:1.5;">
            Centro de información médica independiente, cobertura de políticas de salud y asistencia de navegación sanitaria para las familias de Nueva Jersey.
          </p>
        </div>
        <div>
          <div class="footer-column-title">NAVEGACIÓN DIRECTA</div>
          <ul class="footer-link-list">
            <li><a href="/">Portada Principal</a></li>
            <li><a href="/noticias.html">Archivo de Noticias</a></li>
            <li><a href="/medicare.html">Guía de Medicare 2026</a></li>
            <li><a href="/servicio-premium-de-navegacion-y-acceso-a-la-salud.html">Servicio Premium AI</a></li>
          </ul>
        </div>
        <div>
          <div class="footer-column-title">LÍNEA DE AYUDA</div>
          <ul class="footer-link-list">
            <li><a href="tel:+18009997200">📞 1-800-999-7200 (Línea Gratuita)</a></li>
            <li><a href="/about.html#contacto">Formulario de Contacto</a></li>
            <li><a href="/admin/">Acceso CMS Administrador</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-legal">
        <div>© 2026 El Portal de Salud de Nueva Jersey. Todos los derechos reservados.</div>
      </div>
    </div>
  </footer>

  <script src="/js/cms-client.js?v=<?= time() ?>"></script>
  <script type="module" src="/main.js?v=<?= time() ?>"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      if (window.fetchComments) {
        window.fetchComments('<?= $postSlug ?>');
      }
    });
  </script>
</body>
</html>
