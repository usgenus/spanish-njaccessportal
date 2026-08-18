<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['cms_logged_in']) || $_SESSION['cms_logged_in'] !== true) {
    header('Location: /admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>El Portal de Salud NJ — CMS Panel de Administración</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/admin/admin.css?v=<?= time() ?>">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              red: '#c91818',
              redDark: '#991b1b',
              dark: '#1e1e24',
              darker: '#111111'
            }
          },
          fontFamily: {
            sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'system-ui', 'sans-serif'],
          }
        }
      }
    }
  </script>
</head>
<body class="bg-[#0f172a] text-slate-100 font-sans antialiased min-h-screen flex flex-col">

  <!-- Top Navigation Bar -->
  <header class="bg-slate-900/90 border-b border-slate-800 backdrop-blur-md sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <a href="/admin/" class="flex items-center gap-3 group">
          <div class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-700/80 flex items-center justify-center p-1.5 shadow-md group-hover:scale-105 transition-transform text-white font-black text-red-600">
            NJ
          </div>
          <div>
            <div class="font-extrabold text-base tracking-tight text-white flex items-center gap-2">
              El Portal de Salud
              <span class="text-[10px] bg-red-600 text-white font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">CMS 2.0</span>
            </div>
            <p class="text-[11px] text-slate-400">Panel de Administración de Contenido · Spanish Portal</p>
          </div>
        </a>
      </div>

      <!-- Center / Desktop Tabs -->
      <nav class="hidden md:flex items-center gap-1 bg-slate-950/60 p-1.5 rounded-2xl border border-slate-800/80 text-xs font-semibold">
        <button onclick="switchTab('dashboard')" id="nav-dashboard" class="tab-btn active px-3.5 py-2 rounded-xl transition-all flex items-center gap-2">
          <i class="fa-solid fa-chart-pie"></i>
          <span>Panel Principal</span>
        </button>
        <button onclick="switchTab('billboard')" id="nav-billboard" class="tab-btn px-3.5 py-2 rounded-xl transition-all flex items-center gap-2">
          <i class="fa-solid fa-panorama"></i>
          <span>Carteleras / Billboard</span>
        </button>
        <button onclick="switchTab('videos')" id="nav-videos" class="tab-btn px-3.5 py-2 rounded-xl transition-all flex items-center gap-2">
          <i class="fa-solid fa-play"></i>
          <span>Videos Médicos</span>
        </button>
        <button onclick="switchTab('posts')" id="nav-posts" class="tab-btn px-3.5 py-2 rounded-xl transition-all flex items-center gap-2">
          <i class="fa-solid fa-newspaper"></i>
          <span>Noticias & Artículos</span>
        </button>
        <button onclick="switchTab('comments')" id="nav-comments" class="tab-btn px-3.5 py-2 rounded-xl transition-all flex items-center gap-2">
          <i class="fa-solid fa-comments"></i>
          <span>Comentarios</span>
        </button>
        <button onclick="switchTab('media')" id="nav-media" class="tab-btn px-3.5 py-2 rounded-xl transition-all flex items-center gap-2">
          <i class="fa-solid fa-photo-film"></i>
          <span>Multimedia</span>
        </button>
      </nav>

      <!-- Right Action Tools -->
      <div class="flex items-center gap-3">
        <a href="/" target="_blank" class="text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-sm">
          <span>Ver Sitio Web</span>
          <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400"></i>
        </a>
        <button onclick="handleLogout()" class="text-xs font-semibold bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5">
          <i class="fa-solid fa-arrow-right-from-bracket text-[11px]"></i>
          <span class="hidden sm:inline">Cerrar Sesión</span>
        </button>
      </div>
    </div>

    <!-- Mobile Sub Tabs -->
    <div class="md:hidden flex overflow-x-auto px-4 py-2 bg-slate-950/80 border-t border-slate-800/80 gap-2 scrollbar-none text-xs font-semibold">
      <button onclick="switchTab('dashboard')" id="nav-m-dashboard" class="mobile-tab-btn active whitespace-nowrap px-3 py-1.5 rounded-lg">Panel</button>
      <button onclick="switchTab('billboard')" id="nav-m-billboard" class="mobile-tab-btn whitespace-nowrap px-3 py-1.5 rounded-lg">Cartelera</button>
      <button onclick="switchTab('videos')" id="nav-m-videos" class="mobile-tab-btn whitespace-nowrap px-3 py-1.5 rounded-lg">Videos</button>
      <button onclick="switchTab('posts')" id="nav-m-posts" class="mobile-tab-btn whitespace-nowrap px-3 py-1.5 rounded-lg">Noticias</button>
      <button onclick="switchTab('comments')" id="nav-m-comments" class="mobile-tab-btn whitespace-nowrap px-3 py-1.5 rounded-lg">Comentarios</button>
      <button onclick="switchTab('media')" id="nav-m-media" class="mobile-tab-btn whitespace-nowrap px-3 py-1.5 rounded-lg">Media</button>
    </div>
  </header>

  <!-- Main Content Container -->
  <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- ========================================================= -->
    <!-- TAB 1: DASHBOARD OVERVIEW -->
    <!-- ========================================================= -->
    <section id="tab-dashboard" class="tab-pane space-y-8">
      <!-- Welcome Banner -->
      <div class="bg-gradient-to-r from-red-950/60 via-slate-800 to-slate-900 border border-slate-700/80 rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-3xl">
          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold mb-3">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            CMS Operativo en Vivo · spanish2.njaccessportal.com
          </span>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">¡Bienvenido al Panel de Control!</h1>
          <p class="text-slate-300 text-sm mt-2 leading-relaxed">
            Administre en tiempo real la <strong>Cartelera Panorámica (Billboard)</strong> en la parte superior del portal, los <strong>Videos Médicos</strong>, las <strong>Noticias y Alertas Sanitarias</strong>, y los <strong>Comentarios de la Comunidad</strong>.
          </p>
        </div>
      </div>

      <!-- Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Stat 1 -->
        <div onclick="switchTab('billboard')" class="bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-red-500/50 rounded-2xl p-5 shadow-sm transition-all cursor-pointer group">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-red-400">Cartelera Principal</span>
            <div class="w-10 h-10 rounded-xl bg-red-500/10 text-red-400 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-panorama"></i>
            </div>
          </div>
          <div class="text-3xl font-extrabold text-white" id="stat-billboards-count">-</div>
          <p class="text-xs text-slate-400 mt-1">Banners activos en portada</p>
        </div>

        <!-- Stat 2 -->
        <div onclick="switchTab('videos')" class="bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-blue-500/50 rounded-2xl p-5 shadow-sm transition-all cursor-pointer group">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Videos Médicos</span>
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-play"></i>
            </div>
          </div>
          <div class="text-3xl font-extrabold text-white" id="stat-videos-count">-</div>
          <p class="text-xs text-slate-400 mt-1">Videos educativos en español</p>
        </div>

        <!-- Stat 3 -->
        <div onclick="switchTab('posts')" class="bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-emerald-500/50 rounded-2xl p-5 shadow-sm transition-all cursor-pointer group">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Noticias y Alertas</span>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-newspaper"></i>
            </div>
          </div>
          <div class="text-3xl font-extrabold text-white" id="stat-posts-count">-</div>
          <p class="text-xs text-slate-400 mt-1">Artículos publicados en el portal</p>
        </div>

        <!-- Stat 4 -->
        <div onclick="switchTab('media')" class="bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-purple-500/50 rounded-2xl p-5 shadow-sm transition-all cursor-pointer group">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-purple-400">Multimedia</span>
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-photo-film"></i>
            </div>
          </div>
          <div class="text-3xl font-extrabold text-white" id="stat-media-count">-</div>
          <p class="text-xs text-slate-400 mt-1">Archivos de imagen y video</p>
        </div>
      </div>

      <!-- Quick Actions & Recent Updates -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Quick Action Card -->
        <div class="lg:col-span-4 bg-slate-800/80 border border-slate-700/80 rounded-3xl p-6 shadow-sm space-y-4">
          <h2 class="text-base font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-bolt text-amber-400"></i>
            <span>Acciones Rápidas</span>
          </h2>
          <p class="text-xs text-slate-400">Cree nuevo contenido con un solo clic.</p>

          <div class="space-y-3 pt-2">
            <button onclick="openBillboardModal()" class="w-full bg-red-600 hover:bg-red-500 text-white font-bold p-3.5 rounded-2xl transition-all flex items-center justify-between text-xs shadow-md">
              <span class="flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-sm"></i>
                <span>Nuevo Banner de Cartelera</span>
              </span>
              <span>→</span>
            </button>

            <button onclick="openVideoModal()" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold p-3.5 rounded-2xl transition-all flex items-center justify-between text-xs shadow-md">
              <span class="flex items-center gap-2">
                <i class="fa-solid fa-video text-sm"></i>
                <span>Nuevo Video Médico</span>
              </span>
              <span>→</span>
            </button>

            <button onclick="openPostModal()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold p-3.5 rounded-2xl transition-all flex items-center justify-between text-xs shadow-md">
              <span class="flex items-center gap-2">
                <i class="fa-solid fa-pen-nib text-sm"></i>
                <span>Redactar Nueva Noticia</span>
              </span>
              <span>→</span>
            </button>
          </div>
        </div>

        <!-- Recent Content Activity -->
        <div class="lg:col-span-8 bg-slate-800/80 border border-slate-700/80 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-700">
              <h2 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-red-400"></i>
                <span>Actividad Reciente del Portal</span>
              </h2>
              <span class="text-xs text-slate-400">Sincronización en Vivo</span>
            </div>
            <div id="dash-recent-list" class="space-y-3">
              <div class="text-center py-8 text-slate-500 text-xs">Cargando datos...</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ========================================================= -->
    <!-- TAB 2: GALLERY BILLBOARDS (Top of Main Page) -->
    <!-- ========================================================= -->
    <section id="tab-billboard" class="tab-pane hidden space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-800/90 border border-slate-700/80 p-6 rounded-3xl">
        <div>
          <div class="flex items-center gap-2">
            <span class="p-2 bg-red-500/10 text-red-400 rounded-xl text-lg"><i class="fa-solid fa-panorama"></i></span>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white">Carteleras Panorámicas (Billboard Banners)</h1>
          </div>
          <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Administre los banners destacados de 100vw que aparecen en la <strong>parte superior de la portada principal</strong>.
          </p>
        </div>
        <button onclick="openBillboardModal()" class="bg-red-600 hover:bg-red-500 text-white font-bold px-4 py-3 rounded-2xl transition-all flex items-center gap-2 text-xs shadow-lg shadow-red-600/30 whitespace-nowrap self-start sm:self-auto">
          <i class="fa-solid fa-plus"></i>
          <span>Agregar Banner</span>
        </button>
      </div>

      <!-- Billboard Grid -->
      <div id="billboards-grid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Rendered via JS -->
      </div>
    </section>

    <!-- ========================================================= -->
    <!-- TAB 3: MEDICAL VIDEO NEWS -->
    <!-- ========================================================= -->
    <section id="tab-videos" class="tab-pane hidden space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-800/90 border border-slate-700/80 p-6 rounded-3xl">
        <div>
          <div class="flex items-center gap-2">
            <span class="p-2 bg-blue-500/10 text-blue-400 rounded-xl text-lg"><i class="fa-solid fa-play"></i></span>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white">Videos de Educación Médica</h1>
          </div>
          <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Videos en español sobre cardiología, neurología, prevención de cáncer y cuidados de salud.
          </p>
        </div>
        <button onclick="openVideoModal()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-4 py-3 rounded-2xl transition-all flex items-center gap-2 text-xs shadow-lg shadow-blue-600/30 whitespace-nowrap self-start sm:self-auto">
          <i class="fa-solid fa-plus"></i>
          <span>Agregar Video</span>
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none" id="video-category-filters">
        <!-- Rendered via JS -->
      </div>

      <!-- Videos Grid -->
      <div id="videos-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Rendered via JS -->
      </div>
    </section>

    <!-- ========================================================= -->
    <!-- TAB 4: BLOG & NEWS POSTS -->
    <!-- ========================================================= -->
    <section id="tab-posts" class="tab-pane hidden space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-800/90 border border-slate-700/80 p-6 rounded-3xl">
        <div>
          <div class="flex items-center gap-2">
            <span class="p-2 bg-emerald-500/10 text-emerald-400 rounded-xl text-lg"><i class="fa-solid fa-newspaper"></i></span>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white">Noticias de Salud &amp; Artículos</h1>
          </div>
          <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Redacte y edite artículos con fotos múltiples, puntos clave, designación de Top Story y alertas en vivo.
          </p>
        </div>
        <button onclick="openPostModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-3 rounded-2xl transition-all flex items-center gap-2 text-xs shadow-lg shadow-emerald-600/30 whitespace-nowrap self-start sm:self-auto">
          <i class="fa-solid fa-pen-nib"></i>
          <span>Redactar Noticia</span>
        </button>
      </div>

      <!-- Search & Filters -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto scrollbar-none" id="post-category-filters">
          <!-- Rendered via JS -->
        </div>
        <div class="relative w-full sm:w-72">
          <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
          <input type="text" id="post-search-input" oninput="handlePostSearch(this.value)" placeholder="Buscar por título..."
            class="w-full bg-slate-800 border border-slate-700 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 transition-all">
        </div>
      </div>

      <!-- Posts List / Grid -->
      <div id="posts-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Rendered via JS -->
      </div>
    </section>

    <!-- ========================================================= -->
    <!-- TAB 5: COMMENTS MODERATION -->
    <!-- ========================================================= -->
    <section id="tab-comments" class="tab-pane hidden space-y-6">
      <div class="bg-slate-800/90 border border-slate-700/80 p-6 rounded-3xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="p-2 bg-amber-500/10 text-amber-400 rounded-xl text-lg"><i class="fa-solid fa-comments"></i></span>
              <h1 class="text-xl sm:text-2xl font-extrabold text-white">Moderación de Comentarios</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
              Vea y modere los comentarios enviados por los lectores en los artículos del portal.
            </p>
          </div>
          <button onclick="fetchAdminComments()" class="bg-slate-700 hover:bg-slate-600 text-white font-bold px-3.5 py-2 rounded-xl text-xs flex items-center gap-1.5 self-start sm:self-auto">
            <i class="fa-solid fa-arrows-rotate"></i>
            <span>Actualizar Lista</span>
          </button>
        </div>

        <div id="comments-admin-list" class="space-y-3 pt-2">
          <div class="text-center py-8 text-slate-500 text-xs">Cargando comentarios...</div>
        </div>
      </div>
    </section>

    <!-- ========================================================= -->
    <!-- TAB 6: MEDIA LIBRARY -->
    <!-- ========================================================= -->
    <section id="tab-media" class="tab-pane hidden space-y-6">
      <div class="bg-slate-800/90 border border-slate-700/80 p-6 rounded-3xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="p-2 bg-purple-500/10 text-purple-400 rounded-xl text-lg"><i class="fa-solid fa-photo-film"></i></span>
              <h1 class="text-xl sm:text-2xl font-extrabold text-white">Galería Multimedia &amp; Subida de Archivos</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
              Suba imágenes y videos. Copie la URL con un clic para insertarla en artículos o carteleras.
            </p>
          </div>
        </div>

        <!-- Drag & Drop Uploader Box -->
        <div id="media-dropzone" class="border-2 border-dashed border-slate-700 hover:border-purple-500 bg-slate-900/60 rounded-2xl p-8 text-center transition-all cursor-pointer">
          <input type="file" id="media-file-input" class="hidden" accept="image/*,video/*" multiple onchange="handleDirectFileUpload(this.files)">
          <div class="flex flex-col items-center justify-center gap-3">
            <div class="w-14 h-14 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-2xl">
              <i class="fa-solid fa-cloud-arrow-up"></i>
            </div>
            <div>
              <p class="text-sm font-bold text-white">Haga clic o arrastre archivos aquí</p>
              <p class="text-xs text-slate-400 mt-1">JPG, PNG, WebP, GIF, SVG, MP4, WEBM (Hasta 100MB+)</p>
            </div>
            <button type="button" onclick="document.getElementById('media-file-input').click()" class="mt-2 bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-md">
              Seleccionar archivos de mi equipo
            </button>
          </div>
        </div>
      </div>

      <!-- Media Grid -->
      <div id="media-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <!-- Rendered via JS -->
      </div>
    </section>

  </main>

  <!-- ========================================================= -->
  <!-- MODAL: BILLBOARD ADD / EDIT -->
  <!-- ========================================================= -->
  <div id="modal-billboard" class="modal-backdrop hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm items-center justify-center p-4 overflow-y-auto">
    <div class="bg-slate-900 border border-slate-700 rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl space-y-5 my-8 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between border-b border-slate-800 pb-4">
        <h3 id="modal-billboard-title" class="text-lg font-bold text-white flex items-center gap-2">
          <i class="fa-solid fa-panorama text-red-500"></i>
          <span>Registrar Banner de Cartelera</span>
        </h3>
        <button onclick="closeModal('modal-billboard')" class="text-slate-400 hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <form id="form-billboard" onsubmit="handleSaveBillboard(event)" class="space-y-4 text-xs">
        <input type="hidden" id="billboard-id" name="id">

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Título del Banner (Headline) *</label>
          <input type="text" id="billboard-title-input" name="title" required
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500"
            placeholder="Ej: Centro de Orientación y Acceso a la Salud de NJ">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Etiqueta / Categoría (Badge)</label>
            <input type="text" id="billboard-category-input" name="category" value="CAMPAÑA ESPECIAL"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Orden de Visualización (Order)</label>
            <input type="number" id="billboard-order-input" name="order" value="1" min="1" max="100"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Descripción / Subtítulo *</label>
          <textarea id="billboard-subtitle-input" name="subtitle" rows="3" required
            class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-red-500 leading-relaxed"
            placeholder="Texto descriptivo que aparecerá sobre el banner"></textarea>
        </div>

        <!-- Media Upload / URL -->
        <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800 space-y-3">
          <label class="block font-bold text-slate-200">Imagen o Video de Fondo (Background Image / Video)</label>
          <div class="flex gap-2">
            <input type="text" id="billboard-media-input" name="mediaUrl" required
              class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-red-500"
              placeholder="URL de imagen o video (https://... o /uploads/...)">
            <label class="bg-red-600 hover:bg-red-500 text-white font-bold px-4 py-2 rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
              <i class="fa-solid fa-arrow-up-from-bracket"></i>
              <span>Subir Archivo</span>
              <input type="file" class="hidden" accept="image/*,video/*" onchange="uploadFieldFile(this, 'billboard-media-input', 'billboard-media-preview')">
            </label>
          </div>
          <!-- Preview container -->
          <div id="billboard-media-preview" class="relative h-36 rounded-xl overflow-hidden bg-slate-800 border border-slate-700 hidden"></div>
        </div>

        <!-- CTA Link & Text -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Enlace de Destino (Link URL)</label>
            <input type="text" id="billboard-linkurl-input" name="linkUrl" value="/about.html#contacto"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500">
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Texto del Botón (Button Text)</label>
            <input type="text" id="billboard-linktext-input" name="linkText" value="Más Información →"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500">
          </div>
        </div>

        <div class="flex items-center gap-2 pt-2">
          <input type="checkbox" id="billboard-active-input" name="active" checked class="w-4 h-4 rounded text-red-600 bg-slate-800 border-slate-700">
          <label for="billboard-active-input" class="font-semibold text-slate-300">Mostrar activamente en la portada del portal</label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
          <button type="button" onclick="closeModal('modal-billboard')" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold">Cancelar</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold shadow-lg shadow-red-600/30">Guardar Banner</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ========================================================= -->
  <!-- MODAL: MEDICAL VIDEO ADD / EDIT -->
  <!-- ========================================================= -->
  <div id="modal-video" class="modal-backdrop hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm items-center justify-center p-4 overflow-y-auto">
    <div class="bg-slate-900 border border-slate-700 rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl space-y-5 my-8 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between border-b border-slate-800 pb-4">
        <h3 id="modal-video-title" class="text-lg font-bold text-white flex items-center gap-2">
          <i class="fa-solid fa-video text-blue-500"></i>
          <span>Registrar Video Médico</span>
        </h3>
        <button onclick="closeModal('modal-video')" class="text-slate-400 hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <form id="form-video" onsubmit="handleSaveVideo(event)" class="space-y-4 text-xs">
        <input type="hidden" id="video-id" name="id">

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Título del Video *</label>
          <input type="text" id="video-title-input" name="title" required
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500"
            placeholder="Ej: Salud Cardiovascular: Consejos Médicos en Español">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Categoría *</label>
            <input type="text" id="video-category-input" name="category" required list="video-categories-datalist"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500"
              placeholder="Cardiovascular, Neurología, etc.">
            <datalist id="video-categories-datalist"></datalist>
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Enlace o ID de YouTube</label>
            <input type="text" id="video-youtube-input" name="youtubeId" oninput="autoFetchYtThumb(this.value)"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500"
              placeholder="Ej: https://www.youtube.com/watch?v=S41KqX2H_7g">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Médico / Especialista</label>
            <input type="text" id="video-doctor-input" name="doctor" value="Mayo Clinic en Español"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Duración</label>
            <input type="text" id="video-duration-input" name="duration" value="10:00"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Orden</label>
            <input type="number" id="video-order-input" name="order" value="1" min="1"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
          </div>
        </div>

        <!-- Thumbnail & Upload -->
        <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800 space-y-3">
          <label class="block font-bold text-slate-200">Miniatura Personalizada y Archivo de Video</label>
          <div class="flex gap-2">
            <input type="text" id="video-thumbnail-input" name="thumbnail"
              class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-blue-500"
              placeholder="URL de miniatura">
            <label class="bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold px-3 py-2 rounded-xl cursor-pointer transition-all flex items-center gap-1 text-xs">
              <i class="fa-solid fa-image"></i>
              <span>Subir Imagen</span>
              <input type="file" class="hidden" accept="image/*" onchange="uploadFieldFile(this, 'video-thumbnail-input', 'video-thumb-preview')">
            </label>
          </div>
          <div id="video-thumb-preview" class="relative h-28 rounded-xl overflow-hidden bg-slate-800 border border-slate-700 hidden"></div>
        </div>

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Resumen / Descripción del Video</label>
          <textarea id="video-summary-input" name="summary" rows="3"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-blue-500 leading-relaxed"
            placeholder="Breve resumen del contenido del video"></textarea>
        </div>

        <div class="flex items-center gap-2 pt-2">
          <input type="checkbox" id="video-active-input" name="active" checked class="w-4 h-4 rounded text-blue-600 bg-slate-800 border-slate-700">
          <label for="video-active-input" class="font-semibold text-slate-300">Mostrar activamente en el portal</label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
          <button type="button" onclick="closeModal('modal-video')" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold">Cancelar</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold shadow-lg shadow-blue-600/30">Guardar Video</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ========================================================= -->
  <!-- MODAL: NEWS / BLOG POST ADD / EDIT -->
  <!-- ========================================================= -->
  <div id="modal-post" class="modal-backdrop hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm items-center justify-center p-4 overflow-y-auto">
    <div class="bg-slate-900 border border-slate-700 rounded-3xl max-w-3xl w-full p-6 sm:p-8 shadow-2xl space-y-5 my-8 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between border-b border-slate-800 pb-4">
        <h3 id="modal-post-title" class="text-lg font-bold text-white flex items-center gap-2">
          <i class="fa-solid fa-pen-nib text-emerald-400"></i>
          <span>Redactar Noticia de Salud</span>
        </h3>
        <button onclick="closeModal('modal-post')" class="text-slate-400 hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <form id="form-post" onsubmit="handleSavePost(event)" class="space-y-4 text-xs">
        <input type="hidden" id="post-id" name="id">

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Título del Artículo *</label>
          <input type="text" id="post-title-input" name="title" required
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500"
            placeholder="Ej: Retiro Voluntario de la FDA de Gotas Oftálmicas">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Categoría *</label>
            <input type="text" id="post-category-input" name="category" required list="post-categories-datalist"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500"
              placeholder="Retiro Voluntario FDA, Medicare & ACA, etc.">
            <datalist id="post-categories-datalist"></datalist>
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Fecha de Publicación</label>
            <input type="date" id="post-date-input" name="date"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Autor / Redacción</label>
            <input type="text" id="post-author-input" name="author" value="Redacción Médica y Salud Pública"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
          </div>
        </div>

        <!-- Multi-Images Upload Manager -->
        <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800 space-y-3">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
              <label class="block font-bold text-slate-200 text-xs sm:text-sm">Imágenes del Artículo</label>
              <p class="text-[11px] text-slate-400 mt-0.5">
                La <span class="text-emerald-400 font-bold">1ra foto</span> se utiliza como portada principal y las demás en la galería del artículo.
              </p>
            </div>
            <label class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3.5 py-2 rounded-xl cursor-pointer transition-all flex items-center justify-center gap-1.5 whitespace-nowrap text-xs shadow-md self-start sm:self-auto">
              <i class="fa-solid fa-cloud-arrow-up"></i>
              <span>Subir Fotos</span>
              <input type="file" class="hidden" accept="image/*" multiple onchange="uploadMultiplePostImages(this)">
            </label>
          </div>

          <div class="flex gap-2">
            <input type="text" id="post-add-image-url-input"
              class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500"
              placeholder="URL de imagen (https://... o /uploads/...)">
            <button type="button" onclick="addPostImageUrlManual()" class="bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold px-3.5 py-2 rounded-xl border border-slate-700 transition-all flex items-center gap-1 text-xs whitespace-nowrap">
              <i class="fa-solid fa-plus"></i> Agregar URL
            </button>
          </div>

          <div id="post-images-manager-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 pt-1"></div>
          <input type="hidden" id="post-cover-input" name="coverImage">
        </div>

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Resumen Corto (Excerpt) *</label>
          <input type="text" id="post-excerpt-input" name="excerpt" required
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500"
            placeholder="Breve resumen visible en las tarjetas de noticias">
        </div>

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Puntos Clave del Resumen (3 puntos, uno por línea)</label>
          <textarea id="post-summarypoints-input" name="summaryPoints" rows="3"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-emerald-500 leading-relaxed"
            placeholder="Punto clave 1&#10;Punto clave 2&#10;Punto clave 3"></textarea>
        </div>

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Contenido Completo del Artículo *</label>
          <textarea id="post-content-input" name="content" rows="6" required
            class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3.5 text-sm text-white focus:outline-none focus:border-emerald-500 leading-relaxed font-sans"
            placeholder="Escriba el texto completo del artículo (admite formato HTML con subtítulos y listas)..."></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
          <div class="flex items-center gap-2">
            <input type="checkbox" id="post-topstory-input" name="isTopStory" class="w-4 h-4 rounded text-red-600 bg-slate-800 border-slate-700">
            <label for="post-topstory-input" class="font-bold text-red-400">🔥 Destacar como Noticia Principal (Top Story en Portada)</label>
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" id="post-liveupdate-input" name="isLiveUpdate" class="w-4 h-4 rounded text-blue-600 bg-slate-800 border-slate-700">
            <label for="post-liveupdate-input" class="font-semibold text-slate-300">Mostrar en el cintillo de Última Hora</label>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
          <button type="button" onclick="closeModal('modal-post')" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold">Cancelar</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold shadow-lg shadow-emerald-600/30">Publicar Noticia</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast Notification System -->
  <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 max-w-sm w-full bg-slate-800 border border-slate-700 text-white p-4 rounded-2xl shadow-2xl flex items-center gap-3">
    <div id="toast-icon" class="text-xl">✅</div>
    <div class="flex-1 text-xs font-semibold" id="toast-msg">Operación completada.</div>
  </div>

  <script src="/admin/admin.js?v=<?= time() ?>"></script>
</body>
</html>
