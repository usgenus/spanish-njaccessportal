/**
 * El Portal de Salud NJ - Admin CMS Client Script
 */

let state = {
  billboards: [],
  videos: [],
  posts: [],
  media: [],
  comments: [],
  categories: { news: [], videos: [], billboards: [] },
  videoFilter: 'Todos',
  postFilter: 'Todos',
  postSearch: '',
  currentPostImages: []
};

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
window.escapeHtml = escapeHtml;

// Initialize CMS on load
document.addEventListener('DOMContentLoaded', () => {
  fetchAllData();
  setupDropzone();
});

// Toast Helper
function showToast(msg, isSuccess = true) {
  const toast = document.getElementById('toast');
  const toastMsg = document.getElementById('toast-msg');
  const toastIcon = document.getElementById('toast-icon');

  toastMsg.textContent = msg;
  toastIcon.textContent = isSuccess ? '✅' : '⚠️';
  toast.className = `fixed bottom-6 right-6 z-50 transform translate-y-0 opacity-100 transition-all duration-300 max-w-sm w-full border text-white p-4 rounded-2xl shadow-2xl flex items-center gap-3 ${
    isSuccess ? 'bg-slate-800 border-emerald-500/50' : 'bg-slate-800 border-red-500/50'
  }`;

  setTimeout(() => {
    toast.className = 'fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 max-w-sm w-full bg-slate-800 border border-slate-700 text-white p-4 rounded-2xl shadow-2xl flex items-center gap-3';
  }, 3500);
}

// Switch Active Tab
function switchTab(tabName) {
  document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
  document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.mobile-tab-btn').forEach(el => el.classList.remove('active'));

  const targetPane = document.getElementById(`tab-${tabName}`);
  const targetBtn = document.getElementById(`nav-${tabName}`);
  const targetMBtn = document.getElementById(`nav-m-${tabName}`);

  if (targetPane) targetPane.classList.remove('hidden');
  if (targetBtn) targetBtn.classList.add('active');
  if (targetMBtn) targetMBtn.classList.add('active');

  if (tabName === 'media') {
    fetchMediaFiles();
  } else if (tabName === 'comments') {
    fetchAdminComments();
  }
}

// Fetch all initial data
async function fetchAllData() {
  try {
    const t = Date.now();
    const [bRes, vRes, pRes] = await Promise.all([
      fetch(`/api/billboards.php?_t=${t}`, { cache: 'no-store' }).then(r => r.json()),
      fetch(`/api/videos.php?_t=${t}`, { cache: 'no-store' }).then(r => r.json()),
      fetch(`/api/posts.php?_t=${t}`, { cache: 'no-store' }).then(r => r.json())
    ]);

    if (bRes.success) {
      state.billboards = bRes.data || [];
      state.categories.billboards = bRes.categories || [];
      renderBillboards();
    }
    if (vRes.success) {
      state.videos = vRes.data || [];
      state.categories.videos = vRes.categories || [];
      renderVideos();
    }
    if (pRes.success) {
      state.posts = pRes.data || [];
      state.categories.news = pRes.categories || [];
      renderPosts();
    }

    updateDashboard();
  } catch (err) {
    console.error('Error fetching data:', err);
    showToast('Error al cargar datos del servidor.', false);
  }
}

// Logout
async function handleLogout() {
  if (!confirm('¿Desea cerrar la sesión de administración?')) return;
  try {
    await fetch('/api/auth.php?action=logout');
    window.location.href = '/admin/login.php';
  } catch (err) {
    window.location.href = '/admin/login.php';
  }
}

// =========================================================
// DASHBOARD
// =========================================================
function updateDashboard() {
  document.getElementById('stat-billboards-count').textContent = state.billboards.length + ' activos';
  document.getElementById('stat-videos-count').textContent = state.videos.length + ' videos';
  document.getElementById('stat-posts-count').textContent = state.posts.length + ' noticias';
  
  // Recent activity list
  const container = document.getElementById('dash-recent-list');
  const recent = [
    ...state.billboards.map(b => ({ type: 'billboard', title: b.title, tag: 'Cartelera', date: b.createdAt || 'Reciente' })),
    ...state.videos.map(v => ({ type: 'video', title: v.title, tag: 'Video', date: v.date || 'Reciente' })),
    ...state.posts.map(p => ({ type: 'post', title: p.title, tag: 'Noticia', date: p.date || 'Reciente' }))
  ].slice(0, 6);

  if (recent.length === 0) {
    container.innerHTML = '<div class="text-center py-6 text-slate-500 text-xs">No hay contenido registrado aún.</div>';
    return;
  }

  container.innerHTML = recent.map(item => `
    <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-900/60 border border-slate-700/60">
      <div class="flex items-center gap-3 min-w-0">
        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold ${
          item.type === 'billboard' ? 'bg-red-500/20 text-red-300' :
          item.type === 'video' ? 'bg-blue-500/20 text-blue-300' : 'bg-emerald-500/20 text-emerald-300'
        }">${item.tag}</span>
        <h4 class="text-xs font-semibold text-white truncate">${escapeHtml(item.title)}</h4>
      </div>
      <span class="text-[11px] text-slate-400 shrink-0 ml-4">${escapeHtml(item.date)}</span>
    </div>
  `).join('');
}

// =========================================================
// BILLBOARDS
// =========================================================
function renderBillboards() {
  const container = document.getElementById('billboards-grid');
  if (!container) return;

  if (state.billboards.length === 0) {
    container.innerHTML = `
      <div class="col-span-full bg-slate-800/40 border border-slate-700 rounded-3xl p-12 text-center text-slate-400">
        <i class="fa-solid fa-panorama text-4xl mb-3 text-slate-500"></i>
        <p class="text-sm font-semibold">No hay carteleras registradas.</p>
        <button onclick="openBillboardModal()" class="mt-4 bg-red-600 hover:bg-red-500 text-white text-xs font-bold px-4 py-2 rounded-xl">
          Agregar Primera Cartelera
        </button>
      </div>
    `;
    return;
  }

  container.innerHTML = state.billboards.map(b => {
    const isVideo = b.mediaType === 'video' || (b.mediaUrl && (b.mediaUrl.endsWith('.mp4') || b.mediaUrl.endsWith('.webm')));
    return `
      <div class="bg-slate-800/80 border ${b.active ? 'border-slate-700/80' : 'border-slate-800 opacity-60'} rounded-3xl overflow-hidden shadow-lg flex flex-col group">
        <div class="relative h-44 bg-slate-950 overflow-hidden">
          ${isVideo ? `
            <video src="${b.mediaUrl}" class="w-full h-full object-cover" muted></video>
            <span class="absolute top-3 right-3 bg-black/70 text-white text-[10px] font-bold px-2 py-1 rounded-lg backdrop-blur-md">
              <i class="fa-solid fa-video mr-1"></i> VIDEO
            </span>
          ` : `
            <img src="${b.mediaUrl || 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800'}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
          `}
          <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
          
          <div class="absolute bottom-3 left-4 right-4 flex items-end justify-between">
            <div>
              <span class="bg-red-600 text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                ${escapeHtml(b.category || 'CAMPAÑA')}
              </span>
              <span class="ml-2 text-xs font-mono text-slate-300 bg-black/60 px-2 py-0.5 rounded-full">
                Orden #${b.order || 1}
              </span>
            </div>
            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full ${b.active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-700 text-slate-400'}">
              ${b.active ? 'Activo' : 'Pausado'}
            </span>
          </div>
        </div>

        <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
          <div>
            <h3 class="font-bold text-white text-base leading-snug line-clamp-2">${escapeHtml(b.title)}</h3>
            <p class="text-xs text-slate-300 mt-2 line-clamp-2 leading-relaxed">${escapeHtml(b.subtitle || '')}</p>
          </div>

          <div class="pt-3 border-t border-slate-700/60 flex items-center justify-between">
            <span class="text-[11px] text-slate-400 truncate max-w-[180px]">
              <i class="fa-solid fa-link mr-1"></i> ${escapeHtml(b.linkUrl || '/')}
            </span>
            <div class="flex items-center gap-2">
              <button onclick="openBillboardModal('${b.id}')" class="bg-slate-700 hover:bg-slate-600 text-slate-200 px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1">
                <i class="fa-solid fa-pen text-[10px]"></i> Editar
              </button>
              <button onclick="deleteBillboard('${b.id}')" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1">
                <i class="fa-solid fa-trash text-[10px]"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function openBillboardModal(id = null) {
  const modal = document.getElementById('modal-billboard');
  const title = document.getElementById('modal-billboard-title');
  const form = document.getElementById('form-billboard');
  form.reset();

  if (id) {
    const b = state.billboards.find(item => item.id === id);
    if (b) {
      title.innerHTML = '<i class="fa-solid fa-pen text-red-500"></i> <span>Editar Cartelera</span>';
      document.getElementById('billboard-id').value = b.id;
      document.getElementById('billboard-title-input').value = b.title || '';
      document.getElementById('billboard-category-input').value = b.category || 'CAMPAÑA ESPECIAL';
      document.getElementById('billboard-order-input').value = b.order || 1;
      document.getElementById('billboard-subtitle-input').value = b.subtitle || '';
      document.getElementById('billboard-media-input').value = b.mediaUrl || '';
      document.getElementById('billboard-linkurl-input').value = b.linkUrl || '/about.html#contacto';
      document.getElementById('billboard-linktext-input').value = b.linkText || 'Más Información →';
      document.getElementById('billboard-active-input').checked = b.active !== false;

      const preview = document.getElementById('billboard-media-preview');
      if (b.mediaUrl) {
        preview.classList.remove('hidden');
        preview.innerHTML = `<img src="${b.mediaUrl}" class="w-full h-full object-cover">`;
      } else {
        preview.classList.add('hidden');
      }
    }
  } else {
    title.innerHTML = '<i class="fa-solid fa-panorama text-red-500"></i> <span>Registrar Banner de Cartelera</span>';
    document.getElementById('billboard-id').value = '';
    document.getElementById('billboard-order-input').value = state.billboards.length + 1;
    document.getElementById('billboard-media-preview').classList.add('hidden');
  }

  modal.classList.remove('hidden');
}

async function handleSaveBillboard(e) {
  e.preventDefault();
  const form = e.target;
  const id = document.getElementById('billboard-id').value;
  const isEdit = Boolean(id);

  const payload = {
    id: id || undefined,
    title: document.getElementById('billboard-title-input').value.trim(),
    category: document.getElementById('billboard-category-input').value.trim(),
    order: parseInt(document.getElementById('billboard-order-input').value, 10) || 1,
    subtitle: document.getElementById('billboard-subtitle-input').value.trim(),
    mediaUrl: document.getElementById('billboard-media-input').value.trim(),
    linkUrl: document.getElementById('billboard-linkurl-input').value.trim(),
    linkText: document.getElementById('billboard-linktext-input').value.trim(),
    active: document.getElementById('billboard-active-input').checked
  };

  try {
    const res = await fetch('/api/billboards.php', {
      method: isEdit ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      showToast(isEdit ? 'Cartelera actualizada.' : 'Cartelera creada con éxito.');
      closeModal('modal-billboard');
      await fetchAllData();
    } else {
      showToast(data.error || 'Error al guardar.', false);
    }
  } catch (err) {
    showToast('Error de conexión con el servidor.', false);
  }
}

async function deleteBillboard(id) {
  if (!confirm('¿Seguro que desea eliminar esta cartelera?')) return;
  try {
    const res = await fetch(`/api/billboards.php?id=${encodeURIComponent(id)}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
      showToast('Cartelera eliminada.');
      await fetchAllData();
    } else {
      showToast(data.error || 'Error al eliminar.', false);
    }
  } catch (err) {
    showToast('Error al eliminar.', false);
  }
}

// =========================================================
// VIDEOS
// =========================================================
function renderVideos() {
  const container = document.getElementById('videos-grid');
  const filterContainer = document.getElementById('video-category-filters');
  if (!container) return;

  // Render Category Filter Chips
  const cats = ['Todos', ...(state.categories.videos || [])];
  const uniqueCats = Array.from(new Set(cats));

  if (filterContainer) {
    filterContainer.innerHTML = uniqueCats.map(c => `
      <button onclick="filterVideosByCategory('${c}')" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap ${
        state.videoFilter === c ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-800 text-slate-400 hover:text-white'
      }">
        ${c}
      </button>
    `).join('');
  }

  // Filter videos
  const filtered = state.videos.filter(v => {
    if (state.videoFilter === 'Todos' || state.videoFilter === '전체') return true;
    return v.category === state.videoFilter;
  });

  if (filtered.length === 0) {
    container.innerHTML = `
      <div class="col-span-full bg-slate-800/40 border border-slate-700 rounded-3xl p-12 text-center text-slate-400">
        <i class="fa-solid fa-play text-4xl mb-3 text-slate-500"></i>
        <p class="text-sm font-semibold">No hay videos en esta categoría.</p>
        <button onclick="openVideoModal()" class="mt-4 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-4 py-2 rounded-xl">
          Agregar Video
        </button>
      </div>
    `;
    return;
  }

  container.innerHTML = filtered.map(v => {
    const thumb = v.thumbnail || (v.youtubeId ? `https://img.youtube.com/vi/${v.youtubeId}/hqdefault.jpg` : 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=800');
    return `
      <div class="bg-slate-800/80 border ${v.active !== false ? 'border-slate-700/80' : 'border-slate-800 opacity-60'} rounded-3xl overflow-hidden shadow-lg flex flex-col group">
        <div class="relative h-44 bg-slate-950 overflow-hidden">
          <img src="${thumb}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
          <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
            <div class="w-12 h-12 rounded-full bg-red-600/90 text-white flex items-center justify-center text-lg shadow-lg group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-play ml-1"></i>
            </div>
          </div>
          <span class="absolute bottom-3 right-3 bg-black/80 text-white text-[10px] font-bold px-2 py-0.5 rounded-md backdrop-blur-md">
            ${escapeHtml(v.duration || '10:00')}
          </span>
          <span class="absolute top-3 left-3 bg-blue-600 text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase">
            ${escapeHtml(v.category || 'Medicina')}
          </span>
        </div>

        <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
          <div>
            <h3 class="font-bold text-white text-sm leading-snug line-clamp-2">${escapeHtml(v.title)}</h3>
            <p class="text-xs text-slate-400 mt-1">${escapeHtml(v.doctor || 'Especialista')}</p>
            ${v.summary ? `<p class="text-xs text-slate-300 mt-2 line-clamp-2 leading-relaxed">${escapeHtml(v.summary)}</p>` : ''}
          </div>

          <div class="pt-3 border-t border-slate-700/60 flex items-center justify-between">
            <span class="text-[11px] text-slate-400">
              <i class="fa-solid fa-eye mr-1"></i> ${escapeHtml(v.views || '1.2K')}
            </span>
            <div class="flex items-center gap-2">
              <button onclick="openVideoModal('${v.id}')" class="bg-slate-700 hover:bg-slate-600 text-slate-200 px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1">
                <i class="fa-solid fa-pen text-[10px]"></i> Editar
              </button>
              <button onclick="deleteVideo('${v.id}')" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1">
                <i class="fa-solid fa-trash text-[10px]"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function filterVideosByCategory(cat) {
  state.videoFilter = cat;
  renderVideos();
}

function openVideoModal(id = null) {
  const modal = document.getElementById('modal-video');
  const title = document.getElementById('modal-video-title');
  const form = document.getElementById('form-video');
  form.reset();

  if (id) {
    const v = state.videos.find(item => item.id === id);
    if (v) {
      title.innerHTML = '<i class="fa-solid fa-pen text-blue-500"></i> <span>Editar Video Médico</span>';
      document.getElementById('video-id').value = v.id;
      document.getElementById('video-title-input').value = v.title || '';
      document.getElementById('video-category-input').value = v.category || 'Cardiovascular';
      document.getElementById('video-youtube-input').value = v.youtubeUrl || v.youtubeId || '';
      document.getElementById('video-doctor-input').value = v.doctor || v.speaker || '';
      document.getElementById('video-duration-input').value = v.duration || '10:00';
      document.getElementById('video-order-input').value = v.order || 1;
      document.getElementById('video-thumbnail-input').value = v.thumbnail || '';
      document.getElementById('video-summary-input').value = v.summary || v.description || '';
      document.getElementById('video-active-input').checked = v.active !== false;

      const preview = document.getElementById('video-thumb-preview');
      if (v.thumbnail) {
        preview.classList.remove('hidden');
        preview.innerHTML = `<img src="${v.thumbnail}" class="w-full h-full object-cover">`;
      } else {
        preview.classList.add('hidden');
      }
    }
  } else {
    title.innerHTML = '<i class="fa-solid fa-video text-blue-500"></i> <span>Registrar Video Médico</span>';
    document.getElementById('video-id').value = '';
    document.getElementById('video-order-input').value = state.videos.length + 1;
    document.getElementById('video-thumb-preview').classList.add('hidden');
  }

  modal.classList.remove('hidden');
}

function autoFetchYtThumb(val) {
  const match = val.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
  const ytId = match ? match[1] : (val.length === 11 ? val : null);
  if (ytId) {
    const thumbUrl = `https://img.youtube.com/vi/${ytId}/hqdefault.jpg`;
    document.getElementById('video-thumbnail-input').value = thumbUrl;
    const preview = document.getElementById('video-thumb-preview');
    preview.classList.remove('hidden');
    preview.innerHTML = `<img src="${thumbUrl}" class="w-full h-full object-cover">`;
  }
}

async function handleSaveVideo(e) {
  e.preventDefault();
  const id = document.getElementById('video-id').value;
  const isEdit = Boolean(id);

  const payload = {
    id: id || undefined,
    title: document.getElementById('video-title-input').value.trim(),
    category: document.getElementById('video-category-input').value.trim(),
    youtubeUrl: document.getElementById('video-youtube-input').value.trim(),
    doctor: document.getElementById('video-doctor-input').value.trim(),
    duration: document.getElementById('video-duration-input').value.trim(),
    order: parseInt(document.getElementById('video-order-input').value, 10) || 1,
    thumbnail: document.getElementById('video-thumbnail-input').value.trim(),
    summary: document.getElementById('video-summary-input').value.trim(),
    active: document.getElementById('video-active-input').checked
  };

  try {
    const res = await fetch('/api/videos.php', {
      method: isEdit ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      showToast(isEdit ? 'Video actualizado.' : 'Video guardado con éxito.');
      closeModal('modal-video');
      await fetchAllData();
    } else {
      showToast(data.error || 'Error al guardar.', false);
    }
  } catch (err) {
    showToast('Error de conexión con el servidor.', false);
  }
}

async function deleteVideo(id) {
  if (!confirm('¿Seguro que desea eliminar este video?')) return;
  try {
    const res = await fetch(`/api/videos.php?id=${encodeURIComponent(id)}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
      showToast('Video eliminado.');
      await fetchAllData();
    } else {
      showToast(data.error || 'Error al eliminar.', false);
    }
  } catch (err) {
    showToast('Error al eliminar.', false);
  }
}

// =========================================================
// BLOG & NEWS POSTS
// =========================================================
function renderPosts() {
  const container = document.getElementById('posts-grid');
  const filterContainer = document.getElementById('post-category-filters');
  if (!container) return;

  const cats = ['Todos', ...(state.categories.news || [])];
  const uniqueCats = Array.from(new Set(cats));

  if (filterContainer) {
    filterContainer.innerHTML = uniqueCats.map(c => `
      <button onclick="filterPostsByCategory('${c}')" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap ${
        state.postFilter === c ? 'bg-emerald-600 text-white shadow-md' : 'bg-slate-800 text-slate-400 hover:text-white'
      }">
        ${c}
      </button>
    `).join('');
  }

  const q = state.postSearch.toLowerCase();
  const filtered = state.posts.filter(p => {
    const matchCat = (state.postFilter === 'Todos' || state.postFilter === '전체') || (p.category === state.postFilter);
    const matchQuery = !q || (p.title && p.title.toLowerCase().includes(q)) || (p.excerpt && p.excerpt.toLowerCase().includes(q));
    return matchCat && matchQuery;
  });

  if (filtered.length === 0) {
    container.innerHTML = `
      <div class="col-span-full bg-slate-800/40 border border-slate-700 rounded-3xl p-12 text-center text-slate-400">
        <i class="fa-solid fa-newspaper text-4xl mb-3 text-slate-500"></i>
        <p class="text-sm font-semibold">No se encontraron noticias que coincidan.</p>
        <button onclick="openPostModal()" class="mt-4 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-4 py-2 rounded-xl">
          Redactar Nueva Noticia
        </button>
      </div>
    `;
    return;
  }

  container.innerHTML = filtered.map(p => {
    const cover = p.coverImage || (!empty(p.images) ? p.images[0] : 'https://images.unsplash.com/photo-1628771065117-74ccb5690668?w=800');
    return `
      <div class="bg-slate-800/80 border ${p.isTopStory ? 'border-red-500/50 ring-1 ring-red-500/30' : 'border-slate-700/80'} rounded-3xl overflow-hidden shadow-lg flex flex-col group">
        <div class="relative h-48 bg-slate-950 overflow-hidden">
          <img src="${cover}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
          <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent"></div>
          
          <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
            <span class="bg-emerald-600 text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase">
              ${escapeHtml(p.category || 'Noticia')}
            </span>
            ${p.isTopStory ? `
              <span class="bg-red-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full uppercase animate-pulse">
                ★ TOP STORY
              </span>
            ` : ''}
          </div>

          <span class="absolute bottom-3 left-4 text-xs font-semibold text-slate-300">
            ${escapeHtml(p.date || '')}
          </span>
        </div>

        <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
          <div>
            <h3 class="font-bold text-white text-base leading-snug line-clamp-2">${escapeHtml(p.title)}</h3>
            <p class="text-xs text-slate-300 mt-2 line-clamp-2 leading-relaxed">${escapeHtml(p.excerpt || '')}</p>
          </div>

          <div class="pt-3 border-t border-slate-700/60 flex items-center justify-between">
            <span class="text-[11px] text-slate-400">
              <i class="fa-solid fa-user-pen mr-1"></i> ${escapeHtml(p.author || 'Redacción')}
            </span>
            <div class="flex items-center gap-2">
              <a href="/noticias.html#${p.slug || p.id}" target="_blank" class="bg-slate-700/60 hover:bg-slate-700 text-slate-300 px-2.5 py-1.5 rounded-xl text-xs font-bold transition-all" title="Ver en el sitio">
                <i class="fa-solid fa-eye"></i>
              </a>
              <button onclick="openPostModal('${p.id}')" class="bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1">
                <i class="fa-solid fa-pen text-[10px]"></i> Editar
              </button>
              <button onclick="deletePost('${p.id}')" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1">
                <i class="fa-solid fa-trash text-[10px]"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function filterPostsByCategory(cat) {
  state.postFilter = cat;
  renderPosts();
}

function handlePostSearch(val) {
  state.postSearch = val;
  renderPosts();
}

function openPostModal(id = null) {
  const modal = document.getElementById('modal-post');
  const title = document.getElementById('modal-post-title');
  const form = document.getElementById('form-post');
  form.reset();
  state.currentPostImages = [];

  if (id) {
    const p = state.posts.find(item => item.id === id);
    if (p) {
      title.innerHTML = '<i class="fa-solid fa-pen text-emerald-400"></i> <span>Editar Noticia</span>';
      document.getElementById('post-id').value = p.id;
      document.getElementById('post-title-input').value = p.title || '';
      document.getElementById('post-category-input').value = p.category || 'Noticias de Salud';
      document.getElementById('post-date-input').value = p.date || new Date().toISOString().split('T')[0];
      document.getElementById('post-author-input').value = p.author || 'Redacción Médica y Salud Pública';
      document.getElementById('post-excerpt-input').value = p.excerpt || '';
      
      const sp = Array.isArray(p.summaryPoints) ? p.summaryPoints.join('\n') : (p.summaryPoints || '');
      document.getElementById('post-summarypoints-input').value = sp;
      document.getElementById('post-content-input').value = p.content || '';
      document.getElementById('post-topstory-input').checked = Boolean(p.isTopStory);
      document.getElementById('post-liveupdate-input').checked = Boolean(p.isLiveUpdate);

      // Populate images
      if (Array.isArray(p.images) && p.images.length > 0) {
        state.currentPostImages = [...p.images];
      } else if (p.coverImage) {
        state.currentPostImages = [p.coverImage];
      }
    }
  } else {
    title.innerHTML = '<i class="fa-solid fa-pen-nib text-emerald-400"></i> <span>Redactar Noticia de Salud</span>';
    document.getElementById('post-id').value = '';
    document.getElementById('post-date-input').value = new Date().toISOString().split('T')[0];
    document.getElementById('post-author-input').value = 'Redacción Médica y Salud Pública';
  }

  renderPostImagesManager();
  modal.classList.remove('hidden');
}

function renderPostImagesManager() {
  const container = document.getElementById('post-images-manager-grid');
  if (!container) return;

  if (state.currentPostImages.length === 0) {
    container.innerHTML = `
      <div class="col-span-full py-4 text-center text-slate-500 text-xs bg-slate-900/40 rounded-xl border border-slate-800">
        No hay imágenes adjuntas. Suba o ingrese la URL de una foto.
      </div>
    `;
    return;
  }

  container.innerHTML = state.currentPostImages.map((img, idx) => `
    <div class="relative group rounded-xl overflow-hidden border ${idx === 0 ? 'border-emerald-500 ring-2 ring-emerald-500/40' : 'border-slate-700'} bg-slate-900 h-24">
      <img src="${img}" class="w-full h-full object-cover">
      ${idx === 0 ? `
        <span class="absolute top-1 left-1 bg-emerald-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow">
          Portada
        </span>
      ` : `
        <button type="button" onclick="setPostCoverImage(${idx})" class="absolute top-1 left-1 bg-black/70 hover:bg-emerald-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow transition-all" title="Establecer como portada">
          Hacer Portada
        </button>
      `}
      <button type="button" onclick="removePostImage(${idx})" class="absolute top-1 right-1 bg-red-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] shadow hover:scale-110 transition-transform">
        ✕
      </button>
    </div>
  `).join('');
}

function addPostImageUrlManual() {
  const input = document.getElementById('post-add-image-url-input');
  const url = input.value.trim();
  if (url) {
    state.currentPostImages.push(url);
    input.value = '';
    renderPostImagesManager();
  }
}

function setPostCoverImage(idx) {
  if (idx > 0 && idx < state.currentPostImages.length) {
    const item = state.currentPostImages.splice(idx, 1)[0];
    state.currentPostImages.unshift(item);
    renderPostImagesManager();
  }
}

function removePostImage(idx) {
  state.currentPostImages.splice(idx, 1);
  renderPostImagesManager();
}

async function uploadMultiplePostImages(input) {
  if (!input.files || input.files.length === 0) return;
  for (let i = 0; i < input.files.length; i++) {
    const file = input.files[i];
    const formData = new FormData();
    formData.append('file', file);
    try {
      const res = await fetch('/api/upload.php', { method: 'POST', body: formData });
      const data = await res.json();
      if (data.success && data.url) {
        state.currentPostImages.push(data.url);
      }
    } catch (e) {
      console.error(e);
    }
  }
  input.value = '';
  renderPostImagesManager();
}

async function handleSavePost(e) {
  e.preventDefault();
  const id = document.getElementById('post-id').value;
  const isEdit = Boolean(id);

  const payload = {
    id: id || undefined,
    title: document.getElementById('post-title-input').value.trim(),
    category: document.getElementById('post-category-input').value.trim(),
    date: document.getElementById('post-date-input').value,
    author: document.getElementById('post-author-input').value.trim(),
    images: state.currentPostImages,
    coverImage: state.currentPostImages[0] || '',
    excerpt: document.getElementById('post-excerpt-input').value.trim(),
    summaryPoints: document.getElementById('post-summarypoints-input').value.trim(),
    content: document.getElementById('post-content-input').value.trim(),
    isTopStory: document.getElementById('post-topstory-input').checked,
    isLiveUpdate: document.getElementById('post-liveupdate-input').checked
  };

  try {
    const res = await fetch('/api/posts.php', {
      method: isEdit ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      showToast(isEdit ? 'Noticia actualizada.' : 'Noticia publicada con éxito.');
      closeModal('modal-post');
      await fetchAllData();
    } else {
      showToast(data.error || 'Error al guardar.', false);
    }
  } catch (err) {
    showToast('Error de conexión con el servidor.', false);
  }
}

async function deletePost(id) {
  if (!confirm('¿Seguro que desea eliminar esta noticia?')) return;
  try {
    const res = await fetch(`/api/posts.php?id=${encodeURIComponent(id)}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
      showToast('Noticia eliminada.');
      await fetchAllData();
    } else {
      showToast(data.error || 'Error al eliminar.', false);
    }
  } catch (err) {
    showToast('Error al eliminar.', false);
  }
}

// =========================================================
// COMMENTS MODERATION
// =========================================================
async function fetchAdminComments() {
  const container = document.getElementById('comments-admin-list');
  if (!container) return;
  container.innerHTML = '<div class="text-center py-8 text-slate-500 text-xs">Cargando comentarios...</div>';

  try {
    const res = await fetch('/api/comments.php?action=all&_t=' + Date.now());
    const data = await res.json();
    if (data.success && Array.isArray(data.comments)) {
      state.comments = data.comments;
      renderAdminComments();
    } else {
      container.innerHTML = '<div class="text-center py-6 text-slate-500 text-xs">No hay comentarios aún.</div>';
    }
  } catch (e) {
    container.innerHTML = '<div class="text-center py-6 text-red-400 text-xs">Error al cargar comentarios.</div>';
  }
}

function renderAdminComments() {
  const container = document.getElementById('comments-admin-list');
  if (!container) return;

  if (state.comments.length === 0) {
    container.innerHTML = '<div class="text-center py-8 text-slate-500 text-xs">No hay comentarios en la base de datos.</div>';
    return;
  }

  container.innerHTML = state.comments.map(c => `
    <div class="bg-slate-900/80 border border-slate-700/80 rounded-2xl p-4 flex items-start justify-between gap-4">
      <div class="space-y-1.5 min-w-0">
        <div class="flex items-center gap-2">
          <span class="font-bold text-red-400 text-xs flex items-center gap-1">
            <i class="fa-solid fa-user-circle"></i> ${escapeHtml(c.nickname)}
          </span>
          <span class="text-slate-500 text-[11px]">· ${escapeHtml(c.createdAt || '')}</span>
          <span class="bg-slate-800 text-slate-300 text-[10px] font-mono px-2 py-0.5 rounded">
            Art: ${escapeHtml(c.postSlug || '')}
          </span>
        </div>
        <p class="text-xs text-slate-200 leading-relaxed font-sans">${escapeHtml(c.content)}</p>
      </div>

      <button onclick="deleteComment('${c.id}')" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 px-3 py-1.5 rounded-xl text-xs font-bold shrink-0 transition-all flex items-center gap-1">
        <i class="fa-solid fa-trash text-[10px]"></i> Eliminar
      </button>
    </div>
  `).join('');
}

async function deleteComment(id) {
  if (!confirm('¿Seguro que desea eliminar este comentario?')) return;
  try {
    const res = await fetch(`/api/comments.php?action=delete&id=${encodeURIComponent(id)}`, { method: 'POST' });
    const data = await res.json();
    if (data.success) {
      showToast('Comentario eliminado.');
      fetchAdminComments();
    } else {
      showToast(data.error || 'Error al eliminar.', false);
    }
  } catch (e) {
    showToast('Error al eliminar.', false);
  }
}

// =========================================================
// MEDIA LIBRARY
// =========================================================
async function fetchMediaFiles() {
  const container = document.getElementById('media-grid');
  if (!container) return;

  try {
    const res = await fetch('/api/media.php?_t=' + Date.now());
    const data = await res.json();
    if (data.success) {
      state.media = data.files || [];
      document.getElementById('stat-media-count').textContent = state.media.length + ' archivos';
      renderMediaGrid();
    }
  } catch (err) {
    console.error('Error fetching media:', err);
  }
}

function renderMediaGrid() {
  const container = document.getElementById('media-grid');
  if (!container) return;

  if (state.media.length === 0) {
    container.innerHTML = `
      <div class="col-span-full py-8 text-center text-slate-500 text-xs">
        No hay archivos subidos todavía. Arrastre y suelte archivos arriba.
      </div>
    `;
    return;
  }

  container.innerHTML = state.media.map(m => `
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl overflow-hidden flex flex-col group">
      <div class="relative h-28 bg-slate-950 overflow-hidden">
        ${m.type === 'video' ? `
          <video src="${m.url}" class="w-full h-full object-cover" muted></video>
          <span class="absolute top-2 right-2 bg-black/80 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">VID</span>
        ` : `
          <img src="${m.url}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        `}
      </div>
      <div class="p-2.5 space-y-1.5 flex-1 flex flex-col justify-between">
        <p class="text-[11px] font-semibold text-white truncate" title="${m.name}">${m.name}</p>
        <div class="flex items-center justify-between gap-1 pt-1">
          <button onclick="copyToClipboard('${m.url}')" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-2 py-1 rounded-lg text-[10px] font-bold transition-all flex items-center gap-1 flex-1 justify-center">
            <i class="fa-solid fa-copy"></i> Copiar URL
          </button>
          <button onclick="deleteMediaFile('${m.url}')" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 p-1 rounded-lg text-[10px] font-bold transition-all">
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>
    </div>
  `).join('');
}

function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(() => {
    showToast('URL copiada al portapapeles: ' + text);
  }).catch(() => {
    showToast('Error al copiar URL.', false);
  });
}

async function deleteMediaFile(url) {
  if (!confirm('¿Seguro que desea eliminar este archivo del servidor?')) return;
  try {
    const res = await fetch('/api/media.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ url })
    });
    const data = await res.json();
    if (data.success) {
      showToast('Archivo eliminado.');
      fetchMediaFiles();
    } else {
      showToast(data.error || 'Error al eliminar.', false);
    }
  } catch (err) {
    showToast('Error de conexión.', false);
  }
}

function setupDropzone() {
  const dropzone = document.getElementById('media-dropzone');
  if (!dropzone) return;

  ['dragenter', 'dragover'].forEach(name => {
    dropzone.addEventListener(name, (e) => {
      e.preventDefault();
      dropzone.classList.add('border-purple-500', 'bg-slate-900/90');
    });
  });

  ['dragleave', 'drop'].forEach(name => {
    dropzone.addEventListener(name, (e) => {
      e.preventDefault();
      dropzone.classList.remove('border-purple-500', 'bg-slate-900/90');
    });
  });

  dropzone.addEventListener('drop', (e) => {
    const files = e.dataTransfer.files;
    if (files && files.length > 0) {
      handleDirectFileUpload(files);
    }
  });
}

async function handleDirectFileUpload(files) {
  if (!files || files.length === 0) return;
  let count = 0;
  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const formData = new FormData();
    formData.append('file', file);
    try {
      const res = await fetch('/api/upload.php', { method: 'POST', body: formData });
      const data = await res.json();
      if (data.success) count++;
    } catch (e) {
      console.error(e);
    }
  }
  showToast(`Se subieron ${count} archivo(s) correctamente.`);
  fetchMediaFiles();
}

async function uploadFieldFile(input, targetInputId, previewId = null) {
  if (!input.files || input.files.length === 0) return;
  const file = input.files[0];
  const formData = new FormData();
  formData.append('file', file);

  try {
    const res = await fetch('/api/upload.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success && data.url) {
      document.getElementById(targetInputId).value = data.url;
      if (previewId) {
        const preview = document.getElementById(previewId);
        preview.classList.remove('hidden');
        if (data.type === 'videos') {
          preview.innerHTML = `<video src="${data.url}" class="w-full h-full object-cover" controls></video>`;
        } else {
          preview.innerHTML = `<img src="${data.url}" class="w-full h-full object-cover">`;
        }
      }
      showToast('Archivo subido con éxito.');
    } else {
      showToast(data.error || 'Error al subir archivo.', false);
    }
  } catch (err) {
    showToast('Error de conexión.', false);
  }
}

// Modal helper
function closeModal(id) {
  document.getElementById(id).classList.add('hidden');
}
window.closeModal = closeModal;
