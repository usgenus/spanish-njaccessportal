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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Healthcare Access Portal — Content Management System (CMS)</title>
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
              blue: '#1E3A8A',
              lightBlue: '#3B82F6',
              dark: '#0B192C',
              darker: '#070F1E',
              red: '#DC2626',
              accent: '#EF4444'
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
              Healthcare Access Portal
              <span class="text-[10px] bg-red-600 text-white font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">CMS 2.0</span>
            </div>
            <p class="text-[11px] text-slate-400">Integrated Content Management · Spanish Portal NJAP</p>
          </div>
        </a>
      </div>

      <!-- Center / Desktop Tabs -->
      <nav class="hidden md:flex items-center gap-1 bg-slate-950/60 p-1.5 rounded-2xl border border-slate-800/80 text-xs font-semibold">
        <button onclick="switchTab('dashboard')" id="nav-dashboard" class="tab-btn active px-3.5 py-2 rounded-xl transition-all flex items-center gap-2">
          <i class="fa-solid fa-chart-pie"></i>
          <span>Dashboard</span>
        </button>
        <button onclick="switchTab('billboard')" id="nav-billboard" class="tab-btn px-3.5 py-2 rounded-xl transition-all flex items-center gap-2">
          <i class="fa-solid fa-panorama"></i>
          <span>Gallery Billboard (3~4)</span>
        </button>
        <button onclick="switchTab('videos')" id="nav-videos" class="tab-btn px-3.5 py-2 rounded-xl transition-all flex items-center gap-2">
          <i class="fa-solid fa-play"></i>
          <span>Medical Video News</span>
        </button>
        <button onclick="switchTab('posts')" id="nav-posts" class="tab-btn px-3.5 py-2 rounded-xl transition-all flex items-center gap-2">
          <i class="fa-solid fa-newspaper"></i>
          <span>Health News / Blog</span>
        </button>
        <button onclick="switchTab('media')" id="nav-media" class="tab-btn px-3.5 py-2 rounded-xl transition-all flex items-center gap-2">
          <i class="fa-solid fa-photo-film"></i>
          <span>Media Library</span>
        </button>
      </nav>

      <!-- Right Action Tools -->
      <div class="flex items-center gap-3">
        <a href="/" target="_blank" class="text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-sm">
          <span>View Site</span>
          <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400"></i>
        </a>
        <button onclick="handleLogout()" class="text-xs font-semibold bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5">
          <i class="fa-solid fa-arrow-right-from-bracket text-[11px]"></i>
          <span class="hidden sm:inline">Logout</span>
        </button>
      </div>
    </div>

    <!-- Mobile Sub Tabs -->
    <div class="md:hidden flex overflow-x-auto px-4 py-2 bg-slate-950/80 border-t border-slate-800/80 gap-2 scrollbar-none text-xs font-semibold">
      <button onclick="switchTab('dashboard')" id="nav-m-dashboard" class="mobile-tab-btn active whitespace-nowrap px-3 py-1.5 rounded-lg">Dashboard</button>
      <button onclick="switchTab('billboard')" id="nav-m-billboard" class="mobile-tab-btn whitespace-nowrap px-3 py-1.5 rounded-lg">Billboard</button>
      <button onclick="switchTab('videos')" id="nav-m-videos" class="mobile-tab-btn whitespace-nowrap px-3 py-1.5 rounded-lg">Videos</button>
      <button onclick="switchTab('posts')" id="nav-m-posts" class="mobile-tab-btn whitespace-nowrap px-3 py-1.5 rounded-lg">News</button>
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
      <div class="bg-gradient-to-r from-blue-900/50 via-slate-800 to-red-950/40 border border-slate-700/80 rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-3xl">
          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold mb-3">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            CMS Operational &amp; Live Sync Active
          </span>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Welcome, Administrator!</h1>
          <p class="text-slate-300 text-sm mt-2 leading-relaxed">
            Manage the <strong>Gallery Billboard</strong> banners, <strong>Medical Video News</strong>, and <strong>Health News &amp; Blog Posts</strong> for the portal in real-time.
          </p>
        </div>
      </div>

      <!-- Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Stat 1 -->
        <div onclick="switchTab('billboard')" class="bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-blue-500/50 rounded-2xl p-5 shadow-sm transition-all cursor-pointer group">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Gallery Billboard</span>
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-panorama"></i>
            </div>
          </div>
          <div class="text-3xl font-extrabold text-white" id="stat-billboards-count">-</div>
          <p class="text-xs text-slate-400 mt-1">3~4 Featured banners on homepage</p>
        </div>

        <!-- Stat 2 -->
        <div onclick="switchTab('videos')" class="bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-red-500/50 rounded-2xl p-5 shadow-sm transition-all cursor-pointer group">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-red-400">Medical Video News</span>
            <div class="w-10 h-10 rounded-xl bg-red-500/10 text-red-400 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-play"></i>
            </div>
          </div>
          <div class="text-3xl font-extrabold text-white" id="stat-videos-count">-</div>
          <p class="text-xs text-slate-400 mt-1">YouTube &amp; uploaded medical videos</p>
        </div>

        <!-- Stat 3 -->
        <div onclick="switchTab('posts')" class="bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-emerald-500/50 rounded-2xl p-5 shadow-sm transition-all cursor-pointer group">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Health News Posts</span>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-newspaper"></i>
            </div>
          </div>
          <div class="text-3xl font-extrabold text-white" id="stat-posts-count">-</div>
          <p class="text-xs text-slate-400 mt-1">Main news articles &amp; blog archive</p>
        </div>

        <!-- Stat 4 -->
        <div onclick="switchTab('media')" class="bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-purple-500/50 rounded-2xl p-5 shadow-sm transition-all cursor-pointer group">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-purple-400">Media Library</span>
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-photo-film"></i>
            </div>
          </div>
          <div class="text-3xl font-extrabold text-white" id="stat-media-count">-</div>
          <p class="text-xs text-slate-400 mt-1">Stored images &amp; video files</p>
        </div>
      </div>

      <!-- Quick Actions & Recent Updates -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Quick Action Card -->
        <div class="lg:col-span-4 bg-slate-800/80 border border-slate-700/80 rounded-3xl p-6 shadow-sm space-y-4">
          <h2 class="text-base font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-bolt text-amber-400"></i>
            <span>Quick Content Actions</span>
          </h2>
          <p class="text-xs text-slate-400">Click any option to immediately register and publish new content.</p>

          <div class="space-y-3 pt-2">
            <button onclick="openBillboardModal()" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold p-3.5 rounded-2xl transition-all flex items-center justify-between text-xs shadow-md">
              <span class="flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-sm"></i>
                <span>Add Gallery Billboard</span>
              </span>
              <span>→</span>
            </button>

            <button onclick="openVideoModal()" class="w-full bg-red-600 hover:bg-red-500 text-white font-bold p-3.5 rounded-2xl transition-all flex items-center justify-between text-xs shadow-md">
              <span class="flex items-center gap-2">
                <i class="fa-solid fa-video text-sm"></i>
                <span>Add Medical Video</span>
              </span>
              <span>→</span>
            </button>

            <button onclick="openPostModal()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold p-3.5 rounded-2xl transition-all flex items-center justify-between text-xs shadow-md">
              <span class="flex items-center gap-2">
                <i class="fa-solid fa-pen-nib text-sm"></i>
                <span>Write Health News Article</span>
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
                <i class="fa-solid fa-clock-rotate-left text-blue-400"></i>
                <span>Live Content Feed Preview</span>
              </h2>
              <span class="text-xs text-slate-400">Live Sync</span>
            </div>
            <div id="dash-recent-list" class="space-y-3">
              <div class="text-center py-8 text-slate-500 text-xs">Loading content data...</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ========================================================= -->
    <!-- TAB 2: GALLERY BILLBOARDS (3~4 Different Billboards) -->
    <!-- ========================================================= -->
    <section id="tab-billboard" class="tab-pane hidden space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-800/90 border border-slate-700/80 p-6 rounded-3xl">
        <div>
          <div class="flex items-center gap-2">
            <span class="p-2 bg-blue-500/10 text-blue-400 rounded-xl text-lg"><i class="fa-solid fa-panorama"></i></span>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white">Gallery Billboard Management</h1>
          </div>
          <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Manage the 3~4 large highlight banners displayed in the <strong>Gallery Billboard section</strong> on the homepage.
          </p>
        </div>
        <button onclick="openBillboardModal()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-4 py-3 rounded-2xl transition-all flex items-center gap-2 text-xs shadow-lg shadow-blue-600/30 whitespace-nowrap self-start sm:self-auto">
          <i class="fa-solid fa-plus"></i>
          <span>Add New Billboard</span>
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
            <span class="p-2 bg-red-500/10 text-red-400 rounded-xl text-lg"><i class="fa-solid fa-play"></i></span>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white">Medical Video News Management</h1>
          </div>
          <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Publish and organize YouTube and directly uploaded medical lecture videos by category.
          </p>
        </div>
        <button onclick="openVideoModal()" class="bg-red-600 hover:bg-red-500 text-white font-bold px-4 py-3 rounded-2xl transition-all flex items-center gap-2 text-xs shadow-lg shadow-red-600/30 whitespace-nowrap self-start sm:self-auto">
          <i class="fa-solid fa-plus"></i>
          <span>Add Medical Video</span>
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
            <h1 class="text-xl sm:text-2xl font-extrabold text-white">Health News &amp; Blog Management</h1>
          </div>
          <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Publish and edit articles across Medical Columns, FDA Recalls, Health &amp; Wellness, Medicare &amp; ACA, and more.
          </p>
        </div>
        <button onclick="openPostModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-3 rounded-2xl transition-all flex items-center gap-2 text-xs shadow-lg shadow-emerald-600/30 whitespace-nowrap self-start sm:self-auto">
          <i class="fa-solid fa-pen-nib"></i>
          <span>Write New Article</span>
        </button>
      </div>

      <!-- Search & Filters -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto scrollbar-none" id="post-category-filters">
          <!-- Rendered via JS -->
        </div>
        <div class="relative w-full sm:w-72">
          <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
          <input type="text" id="post-search-input" oninput="handlePostSearch(this.value)" placeholder="Search articles by title..."
            class="w-full bg-slate-800 border border-slate-700 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 transition-all">
        </div>
      </div>

      <!-- Posts List / Grid -->
      <div id="posts-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Rendered via JS -->
      </div>
    </section>

    <!-- ========================================================= -->
    <!-- TAB 5: MEDIA LIBRARY -->
    <!-- ========================================================= -->
    <section id="tab-media" class="tab-pane hidden space-y-6">
      <div class="bg-slate-800/90 border border-slate-700/80 p-6 rounded-3xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="p-2 bg-purple-500/10 text-purple-400 rounded-xl text-lg"><i class="fa-solid fa-photo-film"></i></span>
              <h1 class="text-xl sm:text-2xl font-extrabold text-white">Media Library &amp; File Uploader</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
              Upload images (JPG, PNG, WEBP) and videos (MP4, WEBM) safely and copy their URLs for use across the site.
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
              <p class="text-sm font-bold text-white">Click or drag and drop files here to upload</p>
              <p class="text-xs text-slate-400 mt-1">JPG, PNG, WebP, GIF, SVG, MP4, WEBM (Up to 100MB+)</p>
            </div>
            <button type="button" onclick="document.getElementById('media-file-input').click()" class="mt-2 bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-md">
              Select Files from Computer
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
          <i class="fa-solid fa-panorama text-blue-400"></i>
          <span>Add Gallery Billboard</span>
        </h3>
        <button onclick="closeModal('modal-billboard')" class="text-slate-400 hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <form id="form-billboard" onsubmit="handleSaveBillboard(event)" class="space-y-4 text-xs">
        <input type="hidden" id="billboard-id" name="id">

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Billboard Headline / Title *</label>
          <input type="text" id="billboard-title-input" name="title" required
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500"
            placeholder="e.g., 2026 Comprehensive Healthcare Coverage &amp; Enrollment Guidance">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Display Order</label>
            <input type="number" id="billboard-order-input" name="order" value="1" min="1" max="100"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500">
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Category Tag (e.g. SPECIAL CAMPAIGN)</label>
            <input type="text" id="billboard-category-input" name="category" value="SPECIAL CAMPAIGN"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Main Subtitle / Description *</label>
          <textarea id="billboard-subtitle-input" name="subtitle" rows="3" required
            class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-blue-500 leading-relaxed"
            placeholder="Enter detailed description displayed at the bottom of the banner."></textarea>
        </div>

        <!-- Media Upload / URL -->
        <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800 space-y-3">
          <label class="block font-bold text-slate-200">Background Image or Video (Image / Video)</label>
          <div class="flex gap-2">
            <input type="text" id="billboard-media-input" name="mediaUrl" required
              class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-blue-500"
              placeholder="Image or Video URL (https://... or /uploads/...)">
            <label class="bg-blue-600 hover:bg-blue-500 text-white font-bold px-4 py-2 rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
              <i class="fa-solid fa-arrow-up-from-bracket"></i>
              <span>Upload File</span>
              <input type="file" class="hidden" accept="image/*,video/*" onchange="uploadFieldFile(this, 'billboard-media-input', 'billboard-media-preview')">
            </label>
          </div>
          <!-- Preview container -->
          <div id="billboard-media-preview" class="relative h-36 rounded-xl overflow-hidden bg-slate-800 border border-slate-700 hidden"></div>
        </div>

        <!-- CTA Link & Text -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Button Action URL (Link URL)</label>
            <input type="text" id="billboard-linkurl-input" name="linkUrl" value="/about#contact"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Button Text (CTA Label)</label>
            <input type="text" id="billboard-linktext-input" name="linkText" value="Learn More →"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-blue-500">
          </div>
        </div>

        <div class="flex items-center gap-2 pt-2">
          <input type="checkbox" id="billboard-active-input" name="active" checked class="w-4 h-4 rounded text-blue-600 bg-slate-800 border-slate-700">
          <label for="billboard-active-input" class="font-semibold text-slate-300">Set active to display immediately on homepage</label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
          <button type="button" onclick="closeModal('modal-billboard')" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold">Cancel</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold shadow-lg shadow-blue-600/30">Save Billboard</button>
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
          <i class="fa-solid fa-video text-red-500"></i>
          <span>Add Medical Video News</span>
        </h3>
        <button onclick="closeModal('modal-video')" class="text-slate-400 hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <form id="form-video" onsubmit="handleSaveVideo(event)" class="space-y-4 text-xs">
        <input type="hidden" id="video-id" name="id">

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Video Title *</label>
          <input type="text" id="video-title-input" name="title" required
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500"
            placeholder="e.g., Essential Prevention and Management of Cardiovascular Disease">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Category *</label>
            <input type="text" id="video-category-input" name="category" required list="video-categories-datalist"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500"
              placeholder="Cardiovascular, Neurology, etc.">
            <datalist id="video-categories-datalist">
              <option value="Cardiovascular">
              <option value="Neurology">
              <option value="Cancer Prevention &amp; Screening">
              <option value="Orthopedics &amp; Joints">
              <option value="Chronic Disease Care">
              <option value="Medicare &amp; Healthcare Access">
              <option value="Health News">
            </datalist>
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">YouTube URL or Video ID</label>
            <input type="text" id="video-youtube-input" name="youtubeId" oninput="autoFetchYtThumb(this.value)"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500"
              placeholder="e.g., https://www.youtube.com/watch?v=TsdumJbTpTY or TsdumJbTpTY">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Medical Specialist / Speaker</label>
            <input type="text" id="video-doctor-input" name="doctor" value="Medical Access Specialist"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500">
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Hospital / Organization</label>
            <input type="text" id="video-hospital-input" name="hospital" value="Healthcare Access Center NJ"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Duration (e.g. 05:20)</label>
            <input type="text" id="video-duration-input" name="duration" value="05:20"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500">
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Views Display (e.g. 12.5K views)</label>
            <input type="text" id="video-views-input" name="views" value="12.5K views"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500">
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Display Order</label>
            <input type="number" id="video-order-input" name="order" value="1" min="1"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500">
          </div>
        </div>

        <!-- Thumbnail / Direct Video Upload -->
        <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800 space-y-3">
          <label class="block font-bold text-slate-200">Custom Thumbnail Image &amp; Direct Video File</label>
          <div>
            <label class="block text-slate-400 mb-1">Thumbnail Image URL</label>
            <div class="flex gap-2">
              <input type="text" id="video-thumbnail-input" name="thumbnail"
                class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-red-500"
                placeholder="Thumbnail image URL">
              <label class="bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold px-3 py-2 rounded-xl cursor-pointer transition-all flex items-center gap-1 text-xs">
                <i class="fa-solid fa-image"></i>
                <span>Upload Image</span>
                <input type="file" class="hidden" accept="image/*" onchange="uploadFieldFile(this, 'video-thumbnail-input', 'video-thumb-preview')">
              </label>
            </div>
          </div>
          <div id="video-thumb-preview" class="relative h-28 rounded-xl overflow-hidden bg-slate-800 border border-slate-700 hidden"></div>

          <div>
            <label class="block text-slate-400 mb-1">Direct Video URL (Optional: MP4/WebM file)</label>
            <div class="flex gap-2">
              <input type="text" id="video-fileurl-input" name="videoUrl"
                class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-red-500"
                placeholder="Direct video file URL (/uploads/videos/...)">
              <label class="bg-red-600 hover:bg-red-500 text-white font-bold px-3 py-2 rounded-xl cursor-pointer transition-all flex items-center gap-1 text-xs">
                <i class="fa-solid fa-video"></i>
                <span>Upload Video</span>
                <input type="file" class="hidden" accept="video/*" onchange="uploadFieldFile(this, 'video-fileurl-input')">
              </label>
            </div>
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Video Summary &amp; Description</label>
          <textarea id="video-summary-input" name="summary" rows="3"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-red-500 leading-relaxed"
            placeholder="Enter key highlights and description of this medical video."></textarea>
        </div>

        <div class="flex items-center gap-2 pt-2">
          <input type="checkbox" id="video-active-input" name="active" checked class="w-4 h-4 rounded text-red-600 bg-slate-800 border-slate-700">
          <label for="video-active-input" class="font-semibold text-slate-300">Set active to display on homepage</label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
          <button type="button" onclick="closeModal('modal-video')" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold">Cancel</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold shadow-lg shadow-red-600/30">Save Video</button>
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
          <span>Write Health News Article</span>
        </h3>
        <button onclick="closeModal('modal-post')" class="text-slate-400 hover:text-white text-lg"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <form id="form-post" onsubmit="handleSavePost(event)" class="space-y-4 text-xs">
        <input type="hidden" id="post-id" name="id">

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Article Title *</label>
          <input type="text" id="post-title-input" name="title" required
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500"
            placeholder="Enter article headline">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Category *</label>
            <input type="text" id="post-category-input" name="category" required list="post-categories-datalist"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500"
              placeholder="Medical Column, FDA Recall, etc.">
            <datalist id="post-categories-datalist">
              <option value="Medical Column">
              <option value="FDA Recall">
              <option value="Health &amp; Wellness">
              <option value="Medicare &amp; ACA">
              <option value="Health Policy &amp; Reports">
              <option value="Hospital News">
              <option value="Health News">
            </datalist>
            <!-- Quick Category Select Pills -->
            <div class="flex flex-wrap gap-1.5 mt-2">
              <button type="button" onclick="selectPostCategory('Medical Column')" class="px-2.5 py-1 rounded-lg text-xs font-extrabold bg-red-600/30 text-red-300 border border-red-500/50 hover:bg-red-600 hover:text-white transition-all cursor-pointer">🩺 Medical Column</button>
              <button type="button" onclick="selectPostCategory('FDA Recall')" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 hover:text-white transition-all cursor-pointer">FDA Recall</button>
              <button type="button" onclick="selectPostCategory('Health & Wellness')" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 hover:text-white transition-all cursor-pointer">Health &amp; Wellness</button>
              <button type="button" onclick="selectPostCategory('Medicare & ACA')" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 hover:text-white transition-all cursor-pointer">Medicare &amp; ACA</button>
              <button type="button" onclick="selectPostCategory('Health Policy & Reports')" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 hover:text-white transition-all cursor-pointer">Policy &amp; Reports</button>
            </div>
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Publication Date</label>
            <input type="date" id="post-date-input" name="date"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
          </div>
          <div>
            <label class="block font-bold text-slate-300 mb-1.5">Author / Department</label>
            <input type="text" id="post-author-input" name="author" value="Editorial Staff"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
          </div>
        </div>

        <!-- Multi-Images Upload Manager & Video -->
        <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-800 space-y-3">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
              <label class="block font-bold text-slate-200 text-xs sm:text-sm">Multiple Images Management</label>
              <p class="text-[11px] text-slate-400 mt-0.5">
                <span class="text-emerald-400 font-bold">1st Image</span> will be used automatically as the Hero Cover &amp; Card Thumbnail. <span class="text-blue-400 font-bold">Subsequent images</span> can be embedded anywhere into the article content.
              </p>
            </div>
            <label class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3.5 py-2 rounded-xl cursor-pointer transition-all flex items-center justify-center gap-1.5 whitespace-nowrap text-xs shadow-md self-start sm:self-auto">
              <i class="fa-solid fa-cloud-arrow-up"></i>
              <span>Batch Upload Images</span>
              <input type="file" class="hidden" accept="image/*" multiple onchange="uploadMultiplePostImages(this)">
            </label>
          </div>

          <!-- Direct URL Add input -->
          <div class="flex gap-2">
            <input type="text" id="post-add-image-url-input"
              class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500"
              placeholder="Paste image URL directly (https://... or /uploads/...) then click [+ Add URL]">
            <button type="button" onclick="addPostImageUrlManual()" class="bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold px-3.5 py-2 rounded-xl border border-slate-700 transition-all flex items-center gap-1 text-xs whitespace-nowrap">
              <i class="fa-solid fa-plus"></i> Add URL
            </button>
          </div>

          <!-- Multi-Image Visual Gallery & Order Manager -->
          <div id="post-images-manager-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 pt-1">
            <!-- Dynamically populated image cards with Thumbnail badge, Reorder arrows, and Delete button -->
          </div>

          <input type="hidden" id="post-cover-input" name="coverImage">

          <!-- Optional Video Attachment -->
          <div class="pt-2 border-t border-slate-800/80">
            <label class="block text-slate-400 mb-1">Attached Video URL (Optional: YouTube or MP4 link)</label>
            <div class="flex gap-2">
              <input type="text" id="post-videourl-input" name="videoUrl"
                class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500"
                placeholder="Video URL (e.g., https://www.youtube.com/watch?v=... or /uploads/videos/...)">
              <label class="bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold px-3 py-2 rounded-xl cursor-pointer transition-all flex items-center gap-1 text-xs whitespace-nowrap">
                <i class="fa-solid fa-video"></i>
                <span>Upload Video</span>
                <input type="file" class="hidden" accept="video/*" onchange="uploadFieldFile(this, 'post-videourl-input')">
              </label>
            </div>
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-300 mb-1.5">Article Excerpt / Summary *</label>
          <input type="text" id="post-excerpt-input" name="excerpt" required
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500"
            placeholder="Short summary displayed on news card lists">
        </div>

        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="block font-bold text-slate-300">Article Main Text Content *</label>
            <div class="flex items-center gap-1">
              <button type="button" id="btn-toggle-post-preview" onclick="togglePostContentPreview()" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition-all flex items-center gap-1">
                <i class="fa-regular fa-eye"></i>
                <span id="preview-toggle-text">Preview</span>
              </button>
            </div>
          </div>

          <!-- Rich Formatting Toolbar -->
          <div class="flex flex-wrap items-center gap-1.5 bg-slate-900/90 border border-slate-700/80 p-2 rounded-t-xl text-xs">
            <button type="button" onclick="insertPostFormat('bold')" title="Bold text (**text**)" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold border border-slate-700 flex items-center gap-1">
              <i class="fa-solid fa-bold"></i> <span>Bold</span>
            </button>
            <button type="button" onclick="insertPostFormat('h2')" title="Major Section Header (## Title)" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-emerald-400 font-bold border border-slate-700 flex items-center gap-1">
              <span>H2 Header</span>
            </button>
            <button type="button" onclick="insertPostFormat('h3')" title="Sub-header (### Title)" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-blue-400 font-bold border border-slate-700 flex items-center gap-1">
              <span>H3 Subheader</span>
            </button>
            <button type="button" onclick="insertPostFormat('large')" title="Large Emphasis (++text++)" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-400 font-bold border border-slate-700 flex items-center gap-1">
              <i class="fa-solid fa-text-height"></i> <span>Large</span>
            </button>
            <button type="button" onclick="insertPostFormat('small')" title="Small Note (--text--)" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 font-medium border border-slate-700 flex items-center gap-1">
              <i class="fa-solid fa-font text-[10px]"></i> <span>Small</span>
            </button>
            <button type="button" onclick="insertPostFormat('mark')" title="Highlight (==text==)" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-yellow-300 border border-slate-700 flex items-center gap-1">
              <i class="fa-solid fa-highlighter"></i> <span>Highlight</span>
            </button>
            <button type="button" onclick="insertPostFormat('list')" title="Bullet List (- item)" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1">
              <i class="fa-solid fa-list-ul"></i> <span>List</span>
            </button>
            <button type="button" onclick="insertPostFormat('quote')" title="Blockquote (> content)" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1">
              <i class="fa-solid fa-quote-left"></i> <span>Quote</span>
            </button>
            <button type="button" onclick="insertPostFormat('box')" title="Special Notice / Callout Box" class="px-2.5 py-1 rounded-lg bg-indigo-900/60 hover:bg-indigo-800 text-indigo-200 font-bold border border-indigo-700/80 flex items-center gap-1">
              <i class="fa-solid fa-box-archive text-indigo-400"></i> <span>Notice Box</span>
            </button>
            <button type="button" onclick="openPhotoPickerModal()" title="Insert Photo Box at Cursor" class="px-2.5 py-1 rounded-lg bg-emerald-900/50 hover:bg-emerald-800 text-emerald-300 font-bold border border-emerald-700/80 flex items-center gap-1">
              <i class="fa-solid fa-camera text-emerald-400"></i> <span>Insert Photo Box</span>
            </button>
          </div>

          <textarea id="post-content-input" name="content" rows="9" required
            oninput="updatePostContentPreview()"
            class="w-full bg-slate-800 border border-t-0 border-slate-700 rounded-b-xl p-3.5 text-sm text-white focus:outline-none focus:border-emerald-500 leading-relaxed font-sans"
            placeholder="Type or paste the full article content here. Use [Insert Photo Box] from the toolbar to embed photos neatly at your cursor position."></textarea>

          <!-- Live Preview Box -->
          <div id="post-content-preview-container" class="hidden mt-3 p-4 bg-slate-900/80 border border-slate-700 rounded-xl text-slate-200 text-sm leading-relaxed max-h-60 overflow-y-auto">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 border-b border-slate-800 pb-1">👁 Real-Time Content Rendering Preview</p>
            <div id="post-content-preview" class="space-y-3 font-sans"></div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
          <div class="flex items-center gap-2">
            <input type="checkbox" id="post-doctorcolumn-input" name="isDoctorColumn" onchange="updateExposureCheckboxLimits(document.getElementById('post-id').value)" class="w-4 h-4 rounded text-blue-500 bg-slate-800 border-slate-700">
            <label for="post-doctorcolumn-input" class="font-bold text-blue-400 text-xs sm:text-sm">📈 Feature in LO MÁS LEÍDO (Trending Top 5)</label>
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" id="post-topstory-input" name="isTopStory" class="w-4 h-4 rounded text-amber-500 bg-slate-800 border-slate-700">
            <label for="post-topstory-input" class="font-bold text-amber-400 text-xs sm:text-sm">🔥 Feature as TOP STORY Headline</label>
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" id="post-liveupdate-input" name="isLiveUpdate" onchange="updateExposureCheckboxLimits(document.getElementById('post-id').value)" class="w-4 h-4 rounded text-blue-500 bg-slate-800 border-slate-700">
            <label for="post-liveupdate-input" class="font-semibold text-slate-300 text-xs sm:text-sm">Feature in REPORTAJES DESTACADOS (Featured Reports)</label>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
          <button type="button" onclick="closeModal('modal-post')" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold">Cancel</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold shadow-lg shadow-emerald-600/30">Publish Article</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Photo Placement Box Picker Modal -->
  <div id="modal-photo-picker" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4 modal-backdrop">
    <div class="bg-slate-900 border border-slate-700 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" onclick="event.stopPropagation()">
      <div class="flex items-center justify-between border-b border-slate-800 pb-3">
        <h3 class="text-base font-bold text-white flex items-center gap-2">
          <i class="fa-solid fa-camera text-emerald-400"></i>
          <span>Insert Photo Box into Content</span>
        </h3>
        <button type="button" onclick="closeModal('modal-photo-picker')" class="text-slate-400 hover:text-white text-lg">✕</button>
      </div>

      <p class="text-xs text-slate-400 leading-relaxed">
        Select an image to place at your current cursor position and enter an optional caption. It will render centered with standard width inside the article body.
      </p>

      <!-- Photos Selector Grid -->
      <div>
        <label class="block font-bold text-slate-300 text-xs mb-2">Select Image (Click to choose)</label>
        <div id="photo-picker-grid" class="grid grid-cols-3 gap-2.5 max-h-48 overflow-y-auto p-1 bg-slate-950/60 rounded-xl border border-slate-800">
          <!-- Dynamically populated thumbnails -->
        </div>
      </div>

      <!-- Direct URL Input (Optional) -->
      <div>
        <label class="block font-semibold text-slate-400 text-xs mb-1">Selected Image URL or Direct URL Input</label>
        <input type="text" id="photo-picker-url-input" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500" placeholder="https://... or /uploads/images/...">
      </div>

      <!-- Caption Input -->
      <div>
        <label class="block font-bold text-slate-300 text-xs mb-1">Photo Caption (Optional)</label>
        <input type="text" id="photo-picker-caption-input" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500" placeholder="e.g., Press release photo / Study infographic">
      </div>

      <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
        <button type="button" onclick="closeModal('modal-photo-picker')" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">Cancel</button>
        <button type="button" onclick="confirmInsertPhotoBox()" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 flex items-center gap-1.5">
          <i class="fa-solid fa-check"></i> <span>Insert Photo Box</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Toast Notification System -->
  <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 max-w-sm w-full bg-slate-800 border border-slate-700 text-white p-4 rounded-2xl shadow-2xl flex items-center gap-3">
    <div id="toast-icon" class="text-xl">✅</div>
    <div class="flex-1 text-xs font-semibold" id="toast-msg">Operation completed successfully.</div>
  </div>

  <script src="/admin/admin.js?v=<?= time() ?>"></script>
</body>
</html>
