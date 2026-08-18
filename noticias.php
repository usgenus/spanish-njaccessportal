<?php
/**
 * Healthcare Access Portal (Spanish Edition) - News Archive (Dynamic PHP Engine)
 */
require_once __DIR__ . '/api/db.php';

$db = get_db_data();
$posts = $db['posts'] ?? [];
$categories = $db['categories']['news'] ?? ['Todos', 'Retiro Voluntario FDA', 'Salud & Tecnología', 'Geriatría & Bienestar', 'Medicare & ACA', 'Neurología', 'Enfermedades Crónicas', 'Política Sanitaria'];

// Sort by date DESC
usort($posts, function($a, $b) {
    $t1 = strtotime($a['date'] ?? $a['createdAt'] ?? '1970-01-01');
    $t2 = strtotime($b['date'] ?? $b['createdAt'] ?? '1970-01-01');
    return $t2 <=> $t1;
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Noticias &amp; Alertas FDA | EL PORTAL DE SALUD DE NJ</title>
  <meta name="description" content="Centro periodístico de noticias médicas, retiros de la FDA, política de salud de Medicare y bienestar comunitario en Nueva Jersey.">
  <link rel="stylesheet" href="/styles.css?v=<?= time() ?>">
</head>
<body>

  <!-- Top Marquee Flash Bar -->
  <div class="top-marquee">
    <div class="marquee-content">
      <div class="marquee-item">
        <span class="marquee-badge">ÚLTIMA HORA</span> NOTICIAS MÉDICAS, ALERTAS SANITARIAS Y COBERTURA EDITORIAL EN ESPAÑOL
      </div>
      <div class="marquee-item">
        <span class="marquee-badge">ÚLTIMA HORA</span> NOTICIAS MÉDICAS, ALERTAS SANITARIAS Y COBERTURA EDITORIAL EN ESPAÑOL
      </div>
    </div>
  </div>

  <!-- Sticky Header Container -->
  <header class="news-header-sticky">
    <div class="masthead-section">
      <div class="masthead-container">
        <a href="/" class="masthead-logo">
          <span class="masthead-title">EL PORTAL DE SALUD</span>
          <span class="masthead-sub">SECCIÓN DE NOTICIAS &amp; REPORTAJES MÉDICOS</span>
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
          <button class="mobile-menu-btn" id="mobileMenuToggle">☰</button>
        </div>
      </div>
    </nav>
  </header>

  <div class="header-spacer"></div>

  <main class="news-layout-wrapper">
    
    <div style="background:var(--color-news-dark); color:#ffffff; padding:2rem; border-left:5px solid var(--color-news-red); margin-bottom:2rem; border-radius:4px;">
      <span style="color:#fca5a5; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em;">EDICIÓN DIGITAL 2026</span>
      <h1 style="font-family:var(--font-serif); font-size:2rem; font-weight:900; color:#fff; margin-top:0.25rem;">
        Centro de Noticias Médicas y Seguridad al Paciente
      </h1>
      <p style="color:rgba(255,255,255,0.85); font-size:0.9rem; margin-top:0.5rem; max-width:800px;">
        Alertas de retiro de medicamentos de la FDA, descubrimientos en longevidad y noticias de política sanitaria en su idioma.
      </p>

      <div style="margin-top:1.25rem; max-width:600px;">
        <input type="text" id="newsSearchInput" class="form-input" placeholder="Buscar noticia por palabra clave (ej. FDA, gotas, demencia, Medicare)..." style="background:#ffffff; color:var(--color-news-black); border:none; padding:0.75rem 1rem;">
      </div>
    </div>

    <!-- Main Articles Grid -->
    <div class="news-cards-grid" style="grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));">
      <?php foreach ($posts as $item): 
        $slug = $item['slug'] ?: $item['id'];
        $cover = $item['coverImage'] ?: (!empty($item['images'][0]) ? $item['images'][0] : 'https://images.unsplash.com/photo-1628771065117-74ccb5690668?w=800');
      ?>
      <article class="news-card" id="<?= htmlspecialchars($slug) ?>" onclick="openArticleModal('<?= htmlspecialchars($slug) ?>')" style="cursor:pointer;">
        <div>
          <div class="card-img-box">
            <img src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($item['title'] ?? '') ?>">
          </div>
          <div class="card-kicker"><?= htmlspecialchars(mb_strtoupper($item['category'] ?? 'NOTICIA')) ?></div>
          <h2 class="card-head"><?= htmlspecialchars($item['title'] ?? '') ?></h2>
          <p class="card-body">
            <?= htmlspecialchars($item['excerpt'] ?? '') ?>
          </p>
        </div>
        <div class="card-foot">
          <span><?= htmlspecialchars($item['date'] ?? '') ?></span>
          <span style="color:var(--color-news-red); font-weight:700;">Leer artículo →</span>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

  </main>

  <footer class="commercial-footer">
    <div class="footer-inner">
      <div class="footer-legal">
        <div>© 2026 El Portal de Salud de Nueva Jersey — Archivo de Noticias.</div>
      </div>
    </div>
  </footer>

  <div class="modal-backdrop" id="modalBackdrop">
    <div class="modal-content">
      <button class="modal-close" id="modalCloseBtn">&times;</button>
      <h3 id="modalTitle" style="font-family:var(--font-serif); font-size:1.35rem; color:var(--color-news-black); margin-bottom:1rem;"></h3>
      <div id="modalBody"></div>
    </div>
  </div>

  <script src="/js/cms-client.js?v=<?= time() ?>"></script>
  <script type="module" src="/main.js?v=<?= time() ?>"></script>
</body>
</html>
