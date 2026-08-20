<?php
/**
 * Healthcare Access Portal (Spanish Edition) - Main Homepage (Instant Dynamic PHP Engine)
 * Renders the latest CMS Billboard, Top Story, Real-time News, Policy Reports, and Video News directly on the server.
 */
require_once __DIR__ . '/api/db.php';

$db = get_db_data();
$billboards = $db['billboards'] ?? [];
$videos = $db['videos'] ?? [];
$posts = $db['posts'] ?? [];

// Sort posts by latest edit / creation / date DESC
usort($posts, function($a, $b) {
    $t1 = strtotime($a['updatedAt'] ?? $a['createdAt'] ?? $a['date'] ?? '1970-01-01');
    $t2 = strtotime($b['updatedAt'] ?? $b['createdAt'] ?? $b['date'] ?? '1970-01-01');
    return $t2 <=> $t1;
});

// Filter active billboards
$activeBillboards = array_values(array_filter($billboards, function($b) {
    return !isset($b['active']) || $b['active'] !== false;
}));
if (empty($activeBillboards) && !empty($billboards)) {
    $activeBillboards = $billboards;
}

// Find Top Story and other posts
$topStory = null;
foreach ($posts as $p) {
    if (!empty($p['isTopStory'])) {
        $topStory = $p;
        break;
    }
}
if (!$topStory && !empty($posts)) {
    $topStory = $posts[0];
}

$otherPosts = array_values(array_filter($posts, function($p) use ($topStory) {
    return !$topStory || ($p['id'] ?? '') !== ($topStory['id'] ?? '');
}));

// REPORTAJES DESTACADOS: Prioritize up to 4 posts marked with isLiveUpdate (Featured Reports)
$featuredForSecondary = [];
foreach ($otherPosts as $p) {
    if (!empty($p['isLiveUpdate'])) {
        $featuredForSecondary[] = $p;
        if (count($featuredForSecondary) === 4) break;
    }
}
if (count($featuredForSecondary) < 4) {
    foreach ($otherPosts as $p) {
        if (!in_array($p['id'], array_column($featuredForSecondary, 'id'))) {
            $featuredForSecondary[] = $p;
            if (count($featuredForSecondary) === 4) break;
        }
    }
}
$secondaryStories = array_slice($featuredForSecondary, 0, 4);

// Filter specifically for 4 postings categorized as Medicare & ACA
$medicareAcaPosts = array_values(array_filter($posts, function($p) {
    $cat = mb_strtolower(trim($p['category'] ?? ''));
    return str_contains($cat, 'medicare') || str_contains($cat, 'aca') || str_contains($cat, 'política') || str_contains($cat, 'politica') || str_contains($cat, 'cobertura');
}));

if (count($medicareAcaPosts) < 4) {
    foreach ($posts as $p) {
        if (!in_array($p['id'], array_column($medicareAcaPosts, 'id'))) {
            $medicareAcaPosts[] = $p;
            if (count($medicareAcaPosts) === 4) break;
        }
    }
}
$medicareAcaPosts = array_slice($medicareAcaPosts, 0, 4);

// Live update headline
$liveHeadline = !empty($posts[0]['title']) ? $posts[0]['title'] : 'Guía de elegibilidad para Medicare y ACA Obamacare 2026 en Nueva Jersey disponible para residentes hispanos';

// Active videos
$activeVideos = array_values(array_filter($videos, function($v) {
    return !isset($v['active']) || $v['active'] !== false;
}));
if (empty($activeVideos) && !empty($videos)) {
    $activeVideos = $videos;
}
usort($activeVideos, function($a, $b) {
    return ((int)($a['order'] ?? 999)) <=> ((int)($b['order'] ?? 999));
});
$mainVideo = $activeVideos[0] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EL PORTAL DE SALUD DE NJ | Noticias Médicas y Acceso a la Salud en Nueva Jersey</title>
  <meta name="description" content="Diario digital líder de información médica, política sanitaria, retiros de la FDA y orientación sobre Medicare & ACA para la comunidad hispana de Nueva Jersey.">
  <meta property="og:title" content="EL PORTAL DE SALUD DE NJ | Noticias Médicas y Acceso a la Salud" />
  <meta property="og:description" content="Diario digital líder de información médica, política sanitaria, retiros de la FDA y orientación sobre Medicare & ACA para la comunidad hispana de Nueva Jersey." />
  <meta property="og:image" content="<?= htmlspecialchars($topStory['coverImage'] ?? 'https://images.unsplash.com/photo-1628771065117-74ccb5690668?w=1200') ?>" />
  <link rel="stylesheet" href="/styles.css?v=<?= time() ?>">
</head>
<body>

  <!-- Top Marquee Flash Bar -->
  <div class="top-marquee">
    <div class="marquee-content">
      <div class="marquee-item">
        <span class="marquee-badge">ÚLTIMA HORA</span> DIARIO DIGITAL LÍDER DE ACCESO A LA SALUD Y MEDICINA EN NUEVA JERSEY — EDICIÓN EN ESPAÑOL
      </div>
      <div class="marquee-item">
        <span class="marquee-badge">ÚLTIMA HORA</span> DIARIO DIGITAL LÍDER DE ACCESO A LA SALUD Y MEDICINA EN NUEVA JERSEY — EDICIÓN EN ESPAÑOL
      </div>
      <div class="marquee-item">
        <span class="marquee-badge">ÚLTIMA HORA</span> DIARIO DIGITAL LÍDER DE ACCESO A LA SALUD Y MEDICINA EN NUEVA JERSEY — EDICIÓN EN ESPAÑOL
      </div>
    </div>
  </div>

  <!-- Sticky Header Container -->
  <header class="news-header-sticky">
    
    <!-- Commercial Masthead Logo Section -->
    <div class="masthead-section">
      <div class="masthead-container">
        <a href="/" class="masthead-logo">
          <span class="masthead-title">EL PORTAL DE SALUD</span>
          <span class="masthead-sub">NUEVA JERSEY · PERIÓDICO DIGITAL DE MEDICINA &amp; ACCESO</span>
        </a>
      </div>
    </div>

    <!-- Main Navigation Category Bar -->
    <nav class="category-nav">
      <div class="nav-container">
        <ul class="nav-links-row">
          <li><a href="/" class="nav-item-link active">Portada</a></li>
          <li><a href="/noticias.html" class="nav-item-link">Noticias de Salud</a></li>
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

  <!-- ========================================================= -->
  <!-- 1. 100VW PANORAMIC BILLBOARD SECTION (TOP OF MAIN PAGE)    -->
  <!-- ========================================================= -->
  <section id="gallery-billboard-section">
    <div id="gallery-billboard-container">
      <?php if (!empty($activeBillboards)): 
        $b = $activeBillboards[0];
        $isVideo = ($b['mediaType'] ?? '') === 'video' || (isset($b['mediaUrl']) && (str_ends_with($b['mediaUrl'], '.mp4') || str_ends_with($b['mediaUrl'], '.webm')));
        $targetLink = $b['linkUrl'] ?? '/about.html#contacto';
        $total = count($activeBillboards);
      ?>
      <div class="billboard-slider-box" onmouseenter="window.cmsPauseBillboard()" onmouseleave="window.cmsResumeBillboard()">
        <a href="<?= htmlspecialchars($targetLink) ?>" class="billboard-slide-link" title="<?= htmlspecialchars($b['title'] ?? '') ?>">
          <div class="billboard-media-wrap">
            <?php if ($isVideo): ?>
              <video src="<?= htmlspecialchars($b['mediaUrl']) ?>" class="billboard-media-img" autoplay muted loop playsinline></video>
            <?php else: ?>
              <img id="billboard-active-img" 
                src="<?= htmlspecialchars($b['mediaUrl'] ?: 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=2000&q=85&auto=format') ?>" 
                alt="<?= htmlspecialchars($b['title'] ?? '') ?>" 
                class="billboard-media-img">
            <?php endif; ?>
            <div class="billboard-gradient-bottom"></div>
            <div class="billboard-gradient-side"></div>
          </div>

          <div class="billboard-content-overlay">
            <div class="billboard-inner-container">
              <div class="billboard-text-col">
                <div class="billboard-badges-row">
                  <span class="billboard-badge-cat">
                    <?= htmlspecialchars($b['category'] ?? 'CAMPAÑA ESPECIAL') ?>
                  </span>
                  <span class="billboard-badge-counter">
                    1 / <?= $total ?>
                  </span>
                </div>
                <h3 class="billboard-headline">
                  <?= htmlspecialchars($b['title'] ?? '') ?>
                </h3>
                <p class="billboard-subtitle">
                  <?= htmlspecialchars($b['subtitle'] ?? '') ?>
                </p>
              </div>

              <div class="billboard-btn-col">
                <span class="billboard-cta-btn">
                  <span><?= htmlspecialchars($b['linkText'] ?? 'Más Información') ?></span>
                  <span>→</span>
                </span>
              </div>
            </div>
          </div>
        </a>

        <?php if ($total > 1): ?>
          <button onclick="event.stopPropagation(); event.preventDefault(); window.cmsPrevBillboard();" 
            class="billboard-nav-arrow billboard-nav-prev" aria-label="Anterior">
            ‹
          </button>

          <button onclick="event.stopPropagation(); event.preventDefault(); window.cmsNextBillboard();" 
            class="billboard-nav-arrow billboard-nav-next" aria-label="Siguiente">
            ›
          </button>

          <div class="billboard-dots-row">
            <?php foreach ($activeBillboards as $idx => $dummy): ?>
              <button onclick="event.stopPropagation(); event.preventDefault(); window.cmsGoBillboard(<?= $idx ?>);" 
                class="billboard-dot-pill <?= $idx === 0 ? 'active' : '' ?>">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Main News Layout Container -->
  <main class="news-layout-wrapper">
    
    <!-- Breaking News Ticker Banner -->
    <div class="breaking-banner">
      <div style="display:flex; align-items:center; gap:0.75rem; overflow:hidden;">
        <span class="breaking-tag">⚡ ALERTA SANITARIA</span>
        <p class="breaking-text"><?= htmlspecialchars($liveHeadline) ?></p>
      </div>
      <a href="/noticias.html" style="color:var(--color-news-red); font-weight:800; text-transform:uppercase; font-size:0.75rem; white-space:nowrap;">Ver Alertas →</a>
    </div>

    <!-- 3-Column Commercial Editorial Grid -->
    <section class="editorial-grid">
      
      <!-- COLUMN 1: Lead Story (Main Headline) -->
      <?php if ($topStory): 
        $topSlug = $topStory['slug'] ?: $topStory['id'];
        $topCover = $topStory['coverImage'] ?: (!empty($topStory['images'][0]) ? $topStory['images'][0] : 'https://images.unsplash.com/photo-1628771065117-74ccb5690668?w=1200');
      ?>
      <article class="lead-story-card">
        <div>
          <div class="kicker-tag">
            <span style="width:8px; height:8px; background:var(--color-news-red); display:inline-block;"></span>
            <span><?= htmlspecialchars($topStory['category'] ?? 'RETIRO VOLUNTARIO FDA') ?></span>
            <span style="color:var(--color-news-muted); font-weight:400;">· <?= htmlspecialchars($topStory['date'] ?? '') ?></span>
          </div>

          <a href="/blog-post.php?slug=<?= urlencode($topSlug) ?>">
            <h1 class="lead-headline">
              <?= htmlspecialchars($topStory['title'] ?? '') ?>
            </h1>
          </a>

          <div class="lead-image-box">
            <a href="/blog-post.php?slug=<?= urlencode($topSlug) ?>">
              <img src="<?= htmlspecialchars($topCover) ?>" alt="<?= htmlspecialchars($topStory['title'] ?? '') ?>">
            </a>
          </div>

          <div class="lead-caption">
            Cobertura especial: <?= htmlspecialchars($topStory['title'] ?? '') ?>
          </div>

          <p class="lead-excerpt">
            <?= htmlspecialchars($topStory['excerpt'] ?? '') ?>
          </p>
        </div>

        <div class="byline-row">
          <div>
            <strong>POR <?= htmlspecialchars(mb_strtoupper($topStory['author'] ?? 'REDACCIÓN MÉDICA')) ?></strong> · ⏱ <?= htmlspecialchars($topStory['readTime'] ?? '2 MIN DE LECTURA') ?>
          </div>
          <a href="/blog-post.php?slug=<?= urlencode($topSlug) ?>" style="color:var(--color-news-red); font-weight:800; text-transform:uppercase;">LEER COMPLETO →</a>
        </div>
      </article>
      <?php endif; ?>

      <!-- COLUMN 2: Stacked Secondary News Stories -->
      <section class="secondary-news-col">
        <div style="font-family:var(--font-sans); font-size:0.8rem; font-weight:900; text-transform:uppercase; letter-spacing:0.08em; border-bottom:2px solid var(--color-news-black); padding-bottom:0.4rem; margin-bottom:0.5rem;">
          REPORTAJES DESTACADOS
        </div>

        <?php foreach ($secondaryStories as $item): 
          $sSlug = $item['slug'] ?: $item['id'];
          $sImg = $item['coverImage'] ?: (!empty($item['images'][0]) ? $item['images'][0] : 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600');
        ?>
        <article class="editorial-story-item">
          <span class="kicker-tag"><?= htmlspecialchars($item['category'] ?? 'SALUD') ?></span>
          <div class="story-thumb-row">
            <div style="flex:1;">
              <a href="/blog-post.php?slug=<?= urlencode($sSlug) ?>">
                <h3 class="story-headline-sm"><?= htmlspecialchars($item['title'] ?? '') ?></h3>
              </a>
              <span style="font-size:0.75rem; color:var(--color-news-muted); margin-top:0.25rem; display:block;"><?= htmlspecialchars($item['date'] ?? '') ?></span>
            </div>
            <div class="story-thumb-box">
              <a href="/blog-post.php?slug=<?= urlencode($sSlug) ?>">
                <img src="<?= htmlspecialchars($sImg) ?>" alt="<?= htmlspecialchars($item['title'] ?? '') ?>">
              </a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </section>

      <!-- COLUMN 3: Trending Ranking ("Lo Más Leído") -->
      <aside class="trending-col">
        <div class="trending-col-title">
          <span>LO MÁS LEÍDO</span>
          <span style="color:var(--color-news-red);">TOP 5</span>
        </div>

        <div class="rank-list">
          <?php 
          $rankPosts = array_slice($posts, 0, 5);
          foreach ($rankPosts as $idx => $rItem): 
            $rSlug = $rItem['slug'] ?: $rItem['id'];
          ?>
          <a href="/blog-post.php?slug=<?= urlencode($rSlug) ?>" class="rank-item">
            <span class="rank-num"><?= $idx + 1 ?></span>
            <div class="rank-content">
              <span style="font-size:0.65rem; font-weight:800; color:var(--color-news-red); text-transform:uppercase;"><?= htmlspecialchars($rItem['category'] ?? 'SALUD') ?></span>
              <h4 class="rank-title"><?= htmlspecialchars($rItem['title'] ?? '') ?></h4>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </aside>

    </section>

    <!-- Commercial Public Announcement Sponsorship Banner -->
    <div class="commercial-ad-banner">
      <div>
        <div class="ad-label">ANUNCIO DE SERVICIO PÚBLICO DE SALUD DE NJ</div>
        <div class="ad-title">¿Tiene dudas sobre si califica para Medicare o NJ FamilyCare Gratuito?</div>
        <div class="ad-desc">Obtenga asesoría oficial en su idioma sin costo alguno llamando al 1-800-999-7200.</div>
      </div>
      <a href="tel:+18009997200" class="btn-news-red">Llamar Ahora</a>
    </div>

    <!-- Health Policy & Medicare Section (4 Postings) -->
    <section style="margin-bottom: 2.5rem;">
      <div class="news-section-header">
        <h2 class="news-section-title">INFORMES DE POLÍTICA SANITARIA Y MEDICARE</h2>
        <a href="/noticias.html" class="news-section-more">Ver Sección Completa →</a>
      </div>

      <div class="news-cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
        <?php foreach ($medicareAcaPosts as $pItem): 
          $pSlug = $pItem['slug'] ?: $pItem['id'];
          $pImg = $pItem['coverImage'] ?: (!empty($pItem['images'][0]) ? $pItem['images'][0] : 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=800');
        ?>
        <a href="/blog-post.php?slug=<?= urlencode($pSlug) ?>" class="news-card" style="cursor:pointer; display:flex; flex-direction:column; justify-content:space-between;">
          <div>
            <div class="card-img-box">
              <img src="<?= htmlspecialchars($pImg) ?>" alt="<?= htmlspecialchars($pItem['title'] ?? '') ?>">
            </div>
            <div class="card-kicker"><?= htmlspecialchars(mb_strtoupper($pItem['category'] ?? 'MEDICARE & ACA')) ?></div>
            <h3 class="card-head"><?= htmlspecialchars($pItem['title'] ?? '') ?></h3>
            <p class="card-body"><?= htmlspecialchars($pItem['excerpt'] ?? '') ?></p>
          </div>
          <div class="card-foot">
            <span><?= htmlspecialchars($pItem['date'] ?? '') ?></span>
            <span style="color:var(--color-news-red); font-weight:700;">Leer artículo →</span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Commercial Interactive Patient Tools Section -->
    <section class="dark-newsroom-block">
      <div class="newsroom-header">
        <div class="newsroom-tag">CENTRO DE SERVICIOS DIGITALES Y HERRAMIENTAS INTERACTIVAS</div>
        <h2 class="newsroom-title">Asistencia Directa y Diagnóstico de Cobertura Médica</h2>
      </div>

      <div class="tools-row-grid">
        <div class="tool-block-item" onclick="openInsuranceMatcher()">
          <div>
            <div style="font-size:1.75rem;">🏥</div>
            <h3 class="tool-block-title">Diagnóstico Medicare &amp; ACA</h3>
            <p class="tool-block-desc">Evalúe de forma confidencial si califica para Medicare, NJ FamilyCare gratuito o subsidios GetCoveredNJ.</p>
          </div>
          <div class="tool-block-link">Iniciar Diagnóstico →</div>
        </div>

        <div class="tool-block-item" onclick="openSubsidyCalculator()">
          <div>
            <div style="font-size:1.75rem;">🧮</div>
            <h3 class="tool-block-title">Calculadora de Subsidios</h3>
            <p class="tool-block-desc">Estime el valor del crédito fiscal para reducir la prima mensual de su seguro médico familiar.</p>
          </div>
          <div class="tool-block-link">Calcular Ahorros →</div>
        </div>

        <div class="tool-block-item" onclick="openMedicalDict()">
          <div>
            <div style="font-size:1.75rem;">📖</div>
            <h3 class="tool-block-title">Diccionario Inglés-Español</h3>
            <p class="tool-block-desc">Glosario de términos médicos, copagos, deducibles y frases de hospital frecuentemente usadas.</p>
          </div>
          <div class="tool-block-link">Consultar Glosario →</div>
        </div>

        <div class="tool-block-item" onclick="location.href='/servicio-premium-de-navegacion-y-acceso-a-la-salud.html'">
          <div>
            <div style="font-size:1.75rem;">🤖</div>
            <h3 class="tool-block-title">Servicio Premium AI</h3>
            <p class="tool-block-desc">Asistente de inteligencia artificial dedicado a responder consultas médicas e interpretar análisis de laboratorio.</p>
          </div>
          <div class="tool-block-link">Usar Servicio Premium →</div>
        </div>
      </div>
    </section>

    <!-- Commercial Video News Section -->
    <?php if (!empty($activeVideos)): ?>
    <section class="video-newsroom-block">
      <div style="display:flex; justify-content:space-between; align-items:flex-end; border-bottom:1px solid rgba(255,255,255,0.15); padding-bottom:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
        <h2 style="font-family:var(--font-serif); font-size:1.6rem; font-weight:900; color:#ffffff;">
          🎬 SECCIÓN DE VIDEO REPORTAJES MÉDICOS EN ESPAÑOL
        </h2>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
          <button class="video-cat-btn active" data-cat="all" style="background:var(--color-news-red); color:#fff; border:none; padding:0.35rem 0.85rem; font-size:0.75rem; font-weight:700; cursor:pointer;">Todos</button>
          <button class="video-cat-btn" data-cat="Cardiovascular" style="background:rgba(255,255,255,0.1); color:#fff; border:none; padding:0.35rem 0.85rem; font-size:0.75rem; font-weight:700; cursor:pointer;">Cardiovascular</button>
          <button class="video-cat-btn" data-cat="Neurología" style="background:rgba(255,255,255,0.1); color:#fff; border:none; padding:0.35rem 0.85rem; font-size:0.75rem; font-weight:700; cursor:pointer;">Neurología</button>
          <button class="video-cat-btn" data-cat="Prevención de Cáncer" style="background:rgba(255,255,255,0.1); color:#fff; border:none; padding:0.35rem 0.85rem; font-size:0.75rem; font-weight:700; cursor:pointer;">Cáncer</button>
        </div>
      </div>

      <div class="video-player-grid">
        <div class="player-wrapper">
          <div class="video-screen-box" id="videoScreen" style="cursor:pointer;">
            <img id="videoPreviewImg" src="<?= htmlspecialchars($mainVideo['thumbnail'] ?? 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1200') ?>" alt="<?= htmlspecialchars($mainVideo['title'] ?? '') ?>">
            <div class="play-overlay-btn" id="videoPlayOverlay">
              <div class="play-circle-icon">▶</div>
            </div>
          </div>

          <div class="video-meta-box">
            <div style="font-size:0.75rem; color:#fca5a5; font-weight:800;" id="activeVideoSpeaker">
              <?= htmlspecialchars($mainVideo['doctor'] ?? ($mainVideo['speaker'] ?? 'Especialista Médico')) ?> · 👁️ <?= htmlspecialchars($mainVideo['views'] ?? '125,000 Vistas') ?>
            </div>
            <h3 style="font-family:var(--font-serif); font-size:1.2rem; color:#ffffff; margin:0.35rem 0;" id="activeVideoTitle">
              <?= htmlspecialchars($mainVideo['title'] ?? '') ?>
            </h3>
            <p style="font-size:0.825rem; color:rgba(255,255,255,0.7); line-height:1.45;" id="activeVideoDesc">
              <?= htmlspecialchars($mainVideo['summary'] ?? ($mainVideo['description'] ?? '')) ?>
            </p>
          </div>
        </div>

        <div class="playlist-column" id="videoPlaylist">
          <?php foreach ($activeVideos as $idx => $vid): 
            $ytId = $vid['youtubeId'] ?? '';
            if (!$ytId && !empty($vid['youtubeUrl'])) {
                preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=))([\w-]{11})/', $vid['youtubeUrl'], $m);
                $ytId = $m[1] ?? '';
            }
          ?>
          <div class="playlist-card <?= $idx === 0 ? 'active' : '' ?>"
               data-cat="<?= htmlspecialchars($vid['category'] ?? 'all') ?>"
               data-idx="<?= (int)$idx ?>"
               data-ytid="<?= htmlspecialchars($ytId) ?>"
               data-title="<?= htmlspecialchars($vid['title'] ?? '') ?>"
               data-speaker="<?= htmlspecialchars($vid['doctor'] ?? ($vid['speaker'] ?? 'Especialista Médico')) ?>"
               data-views="<?= htmlspecialchars($vid['views'] ?? '') ?>"
               data-desc="<?= htmlspecialchars($vid['summary'] ?? ($vid['description'] ?? '')) ?>"
               style="cursor:pointer;">
            <div class="playlist-thumb">
              <img src="<?= htmlspecialchars($vid['thumbnail'] ?? '') ?>" alt="<?= htmlspecialchars($vid['title'] ?? '') ?>">
            </div>
            <div style="flex:1; min-width:0;">
              <span style="font-size:0.65rem; color:#fca5a5; font-weight:800; text-transform:uppercase;"><?= htmlspecialchars($vid['category'] ?? 'VIDEO') ?></span>
              <h4 style="font-size:0.8rem; font-weight:700; color:#fff; line-height:1.3; margin-top:2px;"><?= htmlspecialchars($vid['title'] ?? '') ?></h4>
              <span style="font-size:0.7rem; color:rgba(255,255,255,0.5);"><?= htmlspecialchars($vid['doctor'] ?? ($vid['speaker'] ?? '')) ?> · <?= htmlspecialchars($vid['duration'] ?? '10:00') ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Inline Video Player Script (non-module, runs immediately) -->
    <script>
    (function() {
      function playYouTube(ytId, title) {
        var screen = document.getElementById('videoScreen');
        if (!screen || !ytId) return;
        var embedUrl = 'https://www.youtube-nocookie.com/embed/' + ytId + '?autoplay=1&rel=0&modestbranding=1';
        screen.innerHTML = '<iframe src="' + embedUrl + '" title="' + (title || '').replace(/"/g, '&quot;') + '" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        screen.style.cursor = 'default';
      }

      function setActiveCard(activeIdx) {
        document.querySelectorAll('.playlist-card').forEach(function(c) {
          c.classList.toggle('active', parseInt(c.getAttribute('data-idx')) === activeIdx);
        });
      }

      document.addEventListener('DOMContentLoaded', function() {
        // Main screen click
        var screen = document.getElementById('videoScreen');
        if (screen) {
          screen.addEventListener('click', function() {
            var firstCard = document.querySelector('.playlist-card.active') || document.querySelector('.playlist-card');
            if (firstCard) {
              var ytId = firstCard.getAttribute('data-ytid');
              var title = firstCard.getAttribute('data-title');
              playYouTube(ytId, title);
            }
          });
        }

        // Playlist card clicks
        document.querySelectorAll('.playlist-card').forEach(function(card) {
          card.addEventListener('click', function() {
            var ytId = card.getAttribute('data-ytid');
            var title = card.getAttribute('data-title');
            var speaker = card.getAttribute('data-speaker');
            var views = card.getAttribute('data-views');
            var desc = card.getAttribute('data-desc');
            var idx = parseInt(card.getAttribute('data-idx'));

            // Update meta
            var titleEl = document.getElementById('activeVideoTitle');
            var descEl = document.getElementById('activeVideoDesc');
            var speakerEl = document.getElementById('activeVideoSpeaker');
            if (titleEl) titleEl.textContent = title || '';
            if (descEl) descEl.textContent = desc || '';
            if (speakerEl) speakerEl.textContent = (speaker || '') + ' · 👁️ ' + (views || '');

            setActiveCard(idx);
            playYouTube(ytId, title);
          });
        });

        // Category filter buttons
        document.querySelectorAll('.video-cat-btn').forEach(function(btn) {
          btn.addEventListener('click', function() {
            document.querySelectorAll('.video-cat-btn').forEach(function(b) {
              b.style.background = 'rgba(255,255,255,0.1)';
            });
            btn.style.background = 'var(--color-news-red)';
            var cat = btn.getAttribute('data-cat');
            document.querySelectorAll('.playlist-card').forEach(function(card) {
              var itemCat = card.getAttribute('data-cat');
              card.style.display = (cat === 'all' || cat === 'Todos' || itemCat === cat) ? 'flex' : 'none';
            });
          });
        });

        // Also expose for ES module compat
        window.cmsPlayVideo = function(idx) {
          var card = document.querySelector('.playlist-card[data-idx="' + idx + '"]');
          if (card) card.click();
        };
      });
    })();
    </script>
    <?php endif; ?>

  </main>

  <!-- Commercial News Footer -->
  <footer class="commercial-footer">
    <div class="footer-inner">
      <div class="footer-grid">
        <div>
          <div style="font-family:var(--font-serif); font-size:1.25rem; font-weight:900; color:#ffffff; margin-bottom:0.75rem;">
            EL PORTAL DE SALUD DE NJ
          </div>
          <p style="font-size:0.85rem; color:#9ca3af; line-height:1.6; margin-bottom:1rem;">
            Periódico digital independiente dedicado a informar sobre salud pública, alertas sanitarias, política médica y beneficios de Medicare &amp; ACA para la comunidad hispanohablante de Nueva Jersey.
          </p>
          <div style="font-size:0.75rem; color:#6b7280;">
            ISSN 2836-9214 · Publicación diaria en línea
          </div>
        </div>

        <div>
          <div class="footer-col-head">SECCIONES</div>
          <ul class="footer-link-list">
            <li><a href="/">Portada Principal</a></li>
            <li><a href="/noticias.html">Noticias y Alertas FDA</a></li>
            <li><a href="/medicare.html">Guía de Medicare &amp; ACA</a></li>
            <li><a href="/servicio-premium-de-navegacion-y-acceso-a-la-salud.html">Servicio Premium AI</a></li>
            <li><a href="/about.html">Sobre Nosotros</a></li>
          </ul>
        </div>

        <div>
          <div class="footer-col-head">HERRAMIENTAS</div>
          <ul class="footer-link-list">
            <li><a href="#" onclick="openInsuranceMatcher(); return false;">Diagnóstico Medicare</a></li>
            <li><a href="#" onclick="openSubsidyCalculator(); return false;">Calculadora de Subsidios</a></li>
            <li><a href="#" onclick="openMedicalDict(); return false;">Diccionario Médico</a></li>
            <li><a href="/servicio-premium-de-navegacion-y-acceso-a-la-salud.html">Asistente Clínico AI</a></li>
            <li><a href="/admin/" target="_blank">Acceso CMS Administrador</a></li>
          </ul>
        </div>

        <div>
          <div class="footer-col-head">CONTACTO &amp; REDACCIÓN</div>
          <ul class="footer-link-list" style="font-size:0.85rem; color:#9ca3af;">
            <li>Línea Gratuita: 1-800-999-7200</li>
            <li>Atención en Español: Lunes a Sábado</li>
            <li>Redacción: Bergen, Hudson, Essex, NJ</li>
            <li>Email: contacto@njaccessportal.com</li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom-row">
        <div>
          © 2026 EL PORTAL DE SALUD DE NJ. Todos los derechos reservados.
        </div>
        <div>
          Información con fines educativos y de orientación social.
        </div>
      </div>
    </div>
  </footer>

  <!-- Interactive Reader Modal -->
  <div class="modal-backdrop" id="modalBackdrop">
    <div class="modal-content">
      <button class="modal-close" id="modalCloseBtn">✕</button>
      <div id="modalTitle" style="font-family:var(--font-serif); font-size:1.4rem; font-weight:800; color:var(--color-news-black); margin-bottom:1rem;"></div>
      <div id="modalBody"></div>
    </div>
  </div>

  <script src="/js/cms-client.js?v=<?= time() ?>"></script>
  <script type="module" src="/main.js?v=<?= time() ?>"></script>
</body>
</html>
