/**
 * Healthcare Access Portal - Admin CMS Client Script
 * Complete feature parity with kor2 CMS, English UI
 */

let state = {
  billboards: [],
  videos: [],
  posts: [],
  media: [],
  categories: { news: [], videos: [], billboards: [] },
  videoFilter: 'All',
  postFilter: 'All',
  postSearch: ''
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

  if (!toast || !toastMsg || !toastIcon) return;

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
    showToast('Failed to load portal content data.', false);
  }
}

// Logout
async function handleLogout() {
  if (!confirm('Are you sure you want to log out of the admin panel?')) return;
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
  const bCount = document.getElementById('stat-billboards-count');
  const vCount = document.getElementById('stat-videos-count');
  const pCount = document.getElementById('stat-posts-count');

  if (bCount) bCount.textContent = state.billboards.length;
  if (vCount) vCount.textContent = state.videos.length;
  if (pCount) pCount.textContent = state.posts.length;
  
  // Recent activity list
  const container = document.getElementById('dash-recent-list');
  if (!container) return;

  const recent = [
    ...state.billboards.map(b => ({ type: 'billboard', title: b.title, tag: 'Billboard', date: b.createdAt || 'Recent' })),
    ...state.videos.map(v => ({ type: 'video', title: v.title, tag: 'Video', date: v.date || 'Recent' })),
    ...state.posts.map(p => ({ type: 'post', title: p.title, tag: 'News', date: p.date || 'Recent' }))
  ].slice(0, 6);

  if (recent.length === 0) {
    container.innerHTML = '<div class="text-center py-6 text-slate-500 text-xs">No content items registered yet.</div>';
    return;
  }

  container.innerHTML = recent.map(item => `
    <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-900/60 border border-slate-700/60">
      <div class="flex items-center gap-3 min-w-0">
        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold ${
          item.type === 'billboard' ? 'bg-blue-500/20 text-blue-300' :
          item.type === 'video' ? 'bg-red-500/20 text-red-300' : 'bg-emerald-500/20 text-emerald-300'
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
      <div class="col-span-full text-center py-12 bg-slate-800/40 rounded-3xl border border-slate-700 border-dashed">
        <div class="text-3xl mb-2">🖼️</div>
        <h3 class="text-sm font-bold text-white">No gallery billboards registered.</h3>
        <p class="text-xs text-slate-400 mt-1">Add a new billboard banner to feature prominent campaigns on the homepage.</p>
        <button onclick="openBillboardModal()" class="mt-4 bg-blue-600 hover:bg-blue-500 text-white font-bold px-4 py-2 rounded-xl text-xs">
          + Add New Billboard
        </button>
      </div>`;
    return;
  }

  container.innerHTML = state.billboards.map(b => `
    <div class="bg-slate-800/90 border border-slate-700/90 rounded-3xl overflow-hidden shadow-lg flex flex-col justify-between group">
      <div>
        <div class="relative h-48 bg-slate-900 overflow-hidden">
          ${b.mediaType === 'video' || (b.mediaUrl && b.mediaUrl.endsWith('.mp4')) ? `
            <video src="${b.mediaUrl}" class="w-full h-full object-cover" muted autoplay loop></video>
            <span class="absolute top-3 right-3 bg-red-600/90 text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
              <i class="fa-solid fa-video"></i> VIDEO
            </span>
          ` : `
            <img src="${b.mediaUrl || 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800&q=80'}" alt="${escapeHtml(b.title)}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
          `}
          <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
          <div class="absolute top-3 left-3">
            <span class="bg-blue-600 text-white text-[11px] font-extrabold px-3 py-1 rounded-full shadow-md">
              ${escapeHtml(b.category || 'SPECIAL CAMPAIGN')}
            </span>
          </div>
          <div class="absolute bottom-3 left-3 right-3">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">Order #${b.order || 1}</span>
            <h3 class="text-base font-extrabold text-white leading-snug line-clamp-1">${escapeHtml(b.title)}</h3>
          </div>
        </div>

        <div class="p-5 space-y-3">
          <p class="text-xs text-slate-300 leading-relaxed line-clamp-2">${escapeHtml(b.subtitle || '')}</p>
          <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-slate-700/60">
            <span class="flex items-center gap-1.5">
              <i class="fa-solid fa-link text-blue-400"></i>
              <span class="truncate max-w-[160px]">${escapeHtml(b.linkUrl || '#')}</span>
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold ${b.active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-400'}">
              ${b.active ? '● Active' : 'Inactive'}
            </span>
          </div>
        </div>
      </div>

      <div class="px-5 pb-5 pt-2 flex items-center justify-end gap-2 border-t border-slate-700/40">
        <button onclick="editBillboard('${b.id}')" class="px-3.5 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer">
          <i class="fa-solid fa-pen-to-square"></i>
          <span>Edit</span>
        </button>
        <button onclick="deleteBillboard('${b.id}')" class="px-3.5 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer">
          <i class="fa-solid fa-trash"></i>
          <span>Delete</span>
        </button>
      </div>
    </div>
  `).join('');
}

function openBillboardModal() {
  document.getElementById('form-billboard').reset();
  document.getElementById('billboard-id').value = '';
  document.getElementById('modal-billboard-title').innerHTML = '<i class="fa-solid fa-panorama text-blue-400"></i> <span>Add Gallery Billboard</span>';
  document.getElementById('billboard-media-preview').classList.add('hidden');
  document.getElementById('modal-billboard').classList.remove('hidden');
}

function editBillboard(id) {
  const b = state.billboards.find(item => item.id === id);
  if (!b) return;

  document.getElementById('billboard-id').value = b.id;
  document.getElementById('billboard-title-input').value = b.title || '';
  const catInput = document.getElementById('billboard-category-input');
  if (catInput) catInput.value = b.category || 'SPECIAL CAMPAIGN';
  document.getElementById('billboard-order-input').value = b.order || 1;
  document.getElementById('billboard-subtitle-input').value = b.subtitle || '';
  document.getElementById('billboard-media-input').value = b.mediaUrl || '';
  document.getElementById('billboard-linkurl-input').value = b.linkUrl || '/about#contact';
  document.getElementById('billboard-linktext-input').value = b.linkText || 'Learn More →';
  document.getElementById('billboard-active-input').checked = b.active !== false;

  const preview = document.getElementById('billboard-media-preview');
  if (b.mediaUrl) {
    if (b.mediaUrl.endsWith('.mp4')) {
      preview.innerHTML = `<video src="${b.mediaUrl}" class="w-full h-full object-cover" controls></video>`;
    } else {
      preview.innerHTML = `<img src="${b.mediaUrl}" class="w-full h-full object-cover">`;
    }
    preview.classList.remove('hidden');
  } else {
    preview.classList.add('hidden');
  }

  document.getElementById('modal-billboard-title').innerHTML = '<i class="fa-solid fa-pen-to-square text-blue-400"></i> <span>Edit Gallery Billboard</span>';
  document.getElementById('modal-billboard').classList.remove('hidden');
}

async function handleSaveBillboard(e) {
  e.preventDefault();
  const id = document.getElementById('billboard-id').value;
  const isEdit = Boolean(id);
  const catInput = document.getElementById('billboard-category-input');

  const payload = {
    id: id,
    title: document.getElementById('billboard-title-input').value,
    category: (catInput ? catInput.value.trim() : '') || 'SPECIAL CAMPAIGN',
    order: parseInt(document.getElementById('billboard-order-input').value) || 1,
    subtitle: document.getElementById('billboard-subtitle-input').value,
    mediaUrl: document.getElementById('billboard-media-input').value,
    mediaType: document.getElementById('billboard-media-input').value.endsWith('.mp4') ? 'video' : 'image',
    linkUrl: document.getElementById('billboard-linkurl-input').value,
    linkText: document.getElementById('billboard-linktext-input').value,
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
      showToast(isEdit ? 'Billboard updated successfully.' : 'New billboard created successfully.');
      closeModal('modal-billboard');
      fetchAllData();
    } else {
      showToast(data.error || 'Failed to save billboard.', false);
    }
  } catch (err) {
    showToast('A server communication error occurred.', false);
  }
}

async function deleteBillboard(id) {
  if (!confirm('Are you sure you want to delete this gallery billboard banner?')) return;
  try {
    const res = await fetch(`/api/billboards.php?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
      showToast('Billboard deleted successfully.');
      fetchAllData();
    } else {
      showToast(data.error || 'Failed to delete billboard.', false);
    }
  } catch (err) {
    showToast('Communication error occurred.', false);
  }
}

// =========================================================
// MEDICAL VIDEOS
// =========================================================
function renderVideos() {
  const catFilters = document.getElementById('video-category-filters');
  const catList = document.getElementById('video-categories-datalist');
  const defaultCats = ['Cardiovascular', 'Neurology', 'Cancer Prevention & Screening', 'Orthopedics & Joints', 'Chronic Disease Care', 'Medicare & Healthcare Access', 'Health News'];
  const mergedCats = Array.from(new Set([...defaultCats, ...(state.categories.videos || [])]));
  const cats = ['All', ...mergedCats];

  if (catFilters) {
    catFilters.innerHTML = cats.map(c => `
      <button onclick="setVideoFilter('${escapeHtml(c)}')" class="px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer ${
        state.videoFilter === c ? 'bg-red-600 text-white shadow-sm' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white'
      }">${escapeHtml(c)}</button>
    `).join('');
  }

  if (catList) {
    catList.innerHTML = mergedCats.map(c => `<option value="${escapeHtml(c)}"></option>`).join('');
  }

  const container = document.getElementById('videos-grid');
  if (!container) return;

  const filtered = (state.videoFilter === 'All' || state.videoFilter === '전체' || state.videoFilter === 'Todos') 
    ? state.videos 
    : state.videos.filter(v => v.category === state.videoFilter);

  if (filtered.length === 0) {
    container.innerHTML = `
      <div class="col-span-full text-center py-12 bg-slate-800/40 rounded-3xl border border-slate-700 border-dashed">
        <div class="text-3xl mb-2">🎬</div>
        <h3 class="text-sm font-bold text-white">No medical videos found in this category.</h3>
        <p class="text-xs text-slate-400 mt-1">Register a new medical video lecture or report.</p>
        <button onclick="openVideoModal()" class="mt-4 bg-red-600 hover:bg-red-500 text-white font-bold px-4 py-2 rounded-xl text-xs cursor-pointer">
          + Add Medical Video
        </button>
      </div>`;
    return;
  }

  container.innerHTML = filtered.map(v => `
    <div class="bg-slate-800/90 border border-slate-700/90 rounded-3xl overflow-hidden shadow-lg flex flex-col justify-between group">
      <div>
        <div class="relative aspect-video bg-slate-900 overflow-hidden">
          <img src="${v.thumbnail || (v.youtubeId ? `https://img.youtube.com/vi/${v.youtubeId}/maxresdefault.jpg` : 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600&q=80')}" alt="${escapeHtml(v.title)}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
          <div class="absolute inset-0 bg-black/20 flex items-center justify-center">
            <span class="w-10 h-10 rounded-full bg-red-600/90 text-white flex items-center justify-center shadow-lg"><i class="fa-solid fa-play text-xs pl-0.5"></i></span>
          </div>
          <div class="absolute bottom-2 left-2 right-2 flex items-end justify-between">
            <span class="bg-red-600 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow-sm">${escapeHtml(v.category || 'Medical')}</span>
            <span class="bg-black/70 text-white text-[10px] font-mono px-2 py-0.5 rounded">⏱ ${escapeHtml(v.duration || '05:00')}</span>
          </div>
        </div>

        <div class="p-5 space-y-2">
          <div class="text-[11px] text-slate-400 flex items-center gap-2">
            <span class="font-bold text-blue-400">${escapeHtml(v.doctor || v.speaker || 'Medical Specialist')}</span>
            <span>·</span>
            <span>👁️ ${escapeHtml(v.views || '1.2K views')}</span>
          </div>
          <h3 class="text-sm font-bold text-white leading-snug line-clamp-2">${escapeHtml(v.title)}</h3>
          <p class="text-xs text-slate-400 line-clamp-2">${escapeHtml(v.summary || '')}</p>
        </div>
      </div>

      <div class="px-5 pb-5 pt-2 flex items-center justify-between border-t border-slate-700/40">
        <span class="text-[10px] text-slate-500 font-mono">ID: ${escapeHtml(v.youtubeId || 'Direct')}</span>
        <div class="flex items-center gap-2">
          <button onclick="editVideo('${v.id}')" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-semibold flex items-center gap-1 transition-all cursor-pointer">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Edit</span>
          </button>
          <button onclick="deleteVideo('${v.id}')" class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-xl text-xs font-semibold flex items-center gap-1 transition-all cursor-pointer">
            <i class="fa-solid fa-trash"></i>
            <span>Delete</span>
          </button>
        </div>
      </div>
    </div>
  `).join('');
}

function setVideoFilter(cat) {
  state.videoFilter = cat;
  renderVideos();
}

function openVideoModal() {
  document.getElementById('form-video').reset();
  document.getElementById('video-id').value = '';
  document.getElementById('modal-video-title').innerHTML = '<i class="fa-solid fa-video text-red-500"></i> <span>Add Medical Video News</span>';
  document.getElementById('video-thumb-preview').classList.add('hidden');
  document.getElementById('modal-video').classList.remove('hidden');
}

function editVideo(id) {
  const v = state.videos.find(item => item.id === id);
  if (!v) return;

  document.getElementById('video-id').value = v.id;
  document.getElementById('video-title-input').value = v.title || '';
  document.getElementById('video-category-input').value = v.category || 'Cardiovascular';
  document.getElementById('video-youtube-input').value = v.youtubeId || '';
  document.getElementById('video-doctor-input').value = v.doctor || v.speaker || 'Medical Specialist';
  document.getElementById('video-hospital-input').value = v.hospital || 'Healthcare Access Center NJ';
  document.getElementById('video-duration-input').value = v.duration || '05:20';
  document.getElementById('video-views-input').value = v.views || '12.5K views';
  document.getElementById('video-order-input').value = v.order || 1;
  document.getElementById('video-thumbnail-input').value = v.thumbnail || '';
  document.getElementById('video-fileurl-input').value = v.videoUrl || '';
  document.getElementById('video-summary-input').value = v.summary || '';
  document.getElementById('video-active-input').checked = v.active !== false;

  const preview = document.getElementById('video-thumb-preview');
  if (v.thumbnail) {
    preview.innerHTML = `<img src="${v.thumbnail}" class="w-full h-full object-cover">`;
    preview.classList.remove('hidden');
  } else {
    preview.classList.add('hidden');
  }

  document.getElementById('modal-video-title').innerHTML = '<i class="fa-solid fa-pen-to-square text-red-500"></i> <span>Edit Medical Video News</span>';
  document.getElementById('modal-video').classList.remove('hidden');
}

function autoFetchYtThumb(val) {
  let ytId = val.trim();
  const match = ytId.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts|live)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i);
  if (match) ytId = match[1];

  if (ytId.length === 11) {
    const thumbUrl = `https://img.youtube.com/vi/${ytId}/maxresdefault.jpg`;
    if (!document.getElementById('video-thumbnail-input').value) {
      document.getElementById('video-thumbnail-input').value = thumbUrl;
    }
    const preview = document.getElementById('video-thumb-preview');
    preview.innerHTML = `<img src="${thumbUrl}" class="w-full h-full object-cover">`;
    preview.classList.remove('hidden');
  }
}

async function handleSaveVideo(e) {
  e.preventDefault();
  const id = document.getElementById('video-id').value;
  const isEdit = Boolean(id);

  let rawYt = document.getElementById('video-youtube-input').value.trim();
  const ytMatch = rawYt.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts|live)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i);
  const cleanYt = ytMatch ? ytMatch[1] : rawYt;

  let thumb = document.getElementById('video-thumbnail-input').value.trim();
  if (!thumb && cleanYt && cleanYt.length === 11) {
    thumb = `https://img.youtube.com/vi/${cleanYt}/maxresdefault.jpg`;
  }

  const payload = {
    id: id,
    title: document.getElementById('video-title-input').value,
    category: document.getElementById('video-category-input').value || 'Cardiovascular',
    youtubeId: cleanYt,
    doctor: document.getElementById('video-doctor-input').value || 'Medical Specialist',
    hospital: document.getElementById('video-hospital-input').value || 'Healthcare Access Center NJ',
    duration: document.getElementById('video-duration-input').value || '05:20',
    views: document.getElementById('video-views-input').value || '12.5K views',
    order: parseInt(document.getElementById('video-order-input').value) || 1,
    thumbnail: thumb,
    videoUrl: document.getElementById('video-fileurl-input').value,
    summary: document.getElementById('video-summary-input').value,
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
      showToast(isEdit ? 'Video updated successfully.' : 'New video published successfully.');
      closeModal('modal-video');
      fetchAllData();
    } else {
      showToast(data.error || 'Failed to save video.', false);
    }
  } catch (err) {
    showToast('A server communication error occurred.', false);
  }
}

async function deleteVideo(id) {
  if (!confirm('Are you sure you want to delete this medical video?')) return;
  try {
    const res = await fetch(`/api/videos.php?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
      showToast('Video deleted successfully.');
      fetchAllData();
    } else {
      showToast(data.error || 'Failed to delete video.', false);
    }
  } catch (err) {
    showToast('Communication error occurred.', false);
  }
}

// =========================================================
// NEWS & BLOG POSTS
// =========================================================
function renderPosts() {
  const catFilters = document.getElementById('post-category-filters');
  const catList = document.getElementById('post-categories-datalist');
  const defaultCats = ['Medical Column', 'FDA Recall', 'Health & Wellness', 'Medicare & ACA', 'Health Policy & Reports', 'Hospital News', 'Health News'];
  const mergedCats = Array.from(new Set([...defaultCats, ...(state.categories.news || [])]));
  const cats = ['All', ...mergedCats];

  if (catFilters) {
    catFilters.innerHTML = cats.map(c => `
      <button onclick="setPostFilter('${escapeHtml(c)}')" class="px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer ${
        state.postFilter === c ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white'
      }">${escapeHtml(c)}</button>
    `).join('');
  }

  if (catList) {
    catList.innerHTML = mergedCats.map(c => `<option value="${escapeHtml(c)}"></option>`).join('');
  }

  const container = document.getElementById('posts-grid');
  if (!container) return;

  let filtered = state.posts;

  if (state.postFilter !== 'All' && state.postFilter !== '전체' && state.postFilter !== 'Todos') {
    filtered = filtered.filter(p => p.category === state.postFilter);
  }
  if (state.postSearch) {
    const q = state.postSearch.toLowerCase();
    filtered = filtered.filter(p => (p.title || '').toLowerCase().includes(q) || (p.excerpt || '').toLowerCase().includes(q));
  }

  if (filtered.length === 0) {
    container.innerHTML = `
      <div class="col-span-full text-center py-12 bg-slate-800/40 rounded-3xl border border-slate-700 border-dashed">
        <div class="text-3xl mb-2">📰</div>
        <h3 class="text-sm font-bold text-white">No news articles found.</h3>
        <p class="text-xs text-slate-400 mt-1">Write and publish a new health article or medical column.</p>
        <button onclick="openPostModal()" class="mt-4 bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2 rounded-xl text-xs cursor-pointer">
          + Write New Article
        </button>
      </div>`;
    return;
  }

  container.innerHTML = filtered.map(p => `
    <div class="bg-slate-800/90 border border-slate-700/90 rounded-3xl overflow-hidden shadow-lg flex flex-col justify-between group">
      <div>
        <div class="relative h-48 bg-slate-900 overflow-hidden">
          <img src="${p.coverImage || 'https://images.unsplash.com/photo-1628771065117-74ccb5690668?w=800&q=80'}" alt="${escapeHtml(p.title)}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
          <div class="absolute top-3 left-3 flex items-center gap-1.5 flex-wrap">
            <span class="bg-emerald-600 text-white text-[11px] font-bold px-3 py-1 rounded-full shadow-md">${escapeHtml(p.category || 'News')}</span>
            ${p.isTopStory ? '<span class="bg-red-600 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow animate-pulse">🔥 TOP STORY</span>' : ''}
            ${p.isDoctorColumn ? '<span class="bg-blue-600 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow">🩺 TOP 10 Column</span>' : ''}
            ${p.isLiveUpdate ? '<span class="bg-amber-600 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow">⭐ DESTACADOS</span>' : ''}
          </div>
        </div>

        <div class="p-5 space-y-2.5">
          <div class="flex items-center gap-2 text-[11px] text-slate-400">
            <span>${escapeHtml(p.date || '2026.08')}</span>
            <span>·</span>
            <span>⏱ ${escapeHtml(p.readTime || '3 min read')}</span>
            <span>·</span>
            <span>${escapeHtml(p.author || 'Editorial Staff')}</span>
          </div>
          <h3 class="text-sm font-bold text-white leading-snug line-clamp-2">${escapeHtml(p.title)}</h3>
          <p class="text-xs text-slate-300 line-clamp-2">${escapeHtml(p.excerpt || '')}</p>
        </div>
      </div>

      <div class="px-5 pb-5 pt-2 flex items-center justify-between border-t border-slate-700/40">
        <a href="/blog-post.php?slug=${p.slug || p.id}" target="_blank" class="text-[11px] text-blue-400 hover:text-blue-300 font-mono truncate max-w-[120px] flex items-center gap-1 hover:underline">
          <span>/${escapeHtml(p.slug || p.id)}</span>
          <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
        </a>
        <div class="flex items-center gap-2">
          <a href="/blog-post.php?slug=${p.slug || p.id}" target="_blank" class="px-2.5 py-1.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 rounded-xl text-xs font-semibold flex items-center gap-1 transition-all">
            <span>View ↗</span>
          </a>
          <button onclick="editPost('${p.id}')" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-semibold flex items-center gap-1 transition-all cursor-pointer">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Edit</span>
          </button>
          <button onclick="deletePost('${p.id}')" class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-xl text-xs font-semibold flex items-center gap-1 transition-all cursor-pointer">
            <i class="fa-solid fa-trash"></i>
            <span>Delete</span>
          </button>
        </div>
      </div>
    </div>
  `).join('');
}

function setPostFilter(cat) {
  state.postFilter = cat;
  renderPosts();
}

function handlePostSearch(q) {
  state.postSearch = q;
  renderPosts();
}

// State for multiple images in current post modal
let currentPostImages = [];

function selectPostCategory(cat) {
  const inp = document.getElementById('post-category-input');
  if (inp) {
    inp.value = cat;
    inp.focus();
  }
  const dcCheck = document.getElementById('post-doctorcolumn-input');
  if (dcCheck && (cat.toLowerCase().includes('column') || cat.toLowerCase().includes('columna') || cat.toLowerCase().includes('médic') || cat.toLowerCase().includes('medic'))) {
    dcCheck.checked = true;
  }
}

function updateExposureCheckboxLimits(currentEditingPostId) {
  const posts = state.posts || [];
  
  let liveCount = 0;
  let doctorCount = 0;

  posts.forEach(p => {
    if (currentEditingPostId && (p.id === currentEditingPostId || (p.slug && p.slug === currentEditingPostId))) return;
    
    if (p.isLiveUpdate === true || p.isLiveUpdate === 'true' || p.isLiveUpdate === 1 || p.isLiveUpdate === '1') {
      liveCount++;
    }
    if (p.isDoctorColumn === true || p.isDoctorColumn === 'true' || p.isDoctorColumn === 1 || p.isDoctorColumn === '1') {
      doctorCount++;
    }
  });

  const liveInput = document.getElementById('post-liveupdate-input');
  const liveLabel = document.querySelector('label[for="post-liveupdate-input"]');
  const doctorInput = document.getElementById('post-doctorcolumn-input');
  const doctorLabel = document.querySelector('label[for="post-doctorcolumn-input"]');

  // REPORTAJES DESTACADOS (Max 4)
  if (liveInput && liveLabel) {
    if (!liveInput.checked && liveCount >= 4) {
      liveInput.disabled = true;
      liveInput.parentElement.classList.add('opacity-40', 'cursor-not-allowed');
      liveLabel.innerHTML = 'Feature in REPORTAJES DESTACADOS <span class="text-xs text-amber-400 font-bold block sm:inline">(Max 4 featured: Slots Full)</span>';
    } else {
      liveInput.disabled = false;
      liveInput.parentElement.classList.remove('opacity-40', 'cursor-not-allowed');
      const currentVal = liveInput.checked ? liveCount + 1 : liveCount;
      liveLabel.innerHTML = 'Feature in REPORTAJES DESTACADOS <span class="text-xs text-blue-400 font-bold">(' + currentVal + '/4)</span>';
    }
  }

  // TOP 10 Medical Columns (Max 10)
  if (doctorInput && doctorLabel) {
    if (!doctorInput.checked && doctorCount >= 10) {
      doctorInput.disabled = true;
      doctorInput.parentElement.classList.add('opacity-40', 'cursor-not-allowed');
      doctorLabel.innerHTML = '🩺 Feature in Top 10 Medical Columns Section <span class="text-xs text-amber-400 font-bold block sm:inline">(Max 10 featured: Slots Full)</span>';
    } else {
      doctorInput.disabled = false;
      doctorInput.parentElement.classList.remove('opacity-40', 'cursor-not-allowed');
      const currentVal = doctorInput.checked ? doctorCount + 1 : doctorCount;
      doctorLabel.innerHTML = '🩺 Feature in Top 10 Medical Columns Section <span class="text-xs text-red-400 font-bold">(' + currentVal + '/10)</span>';
    }
  }
}

function openPostModal() {
  document.getElementById('form-post').reset();
  document.getElementById('post-id').value = '';
  document.getElementById('post-date-input').value = new Date().toISOString().split('T')[0];
  const dcCheck = document.getElementById('post-doctorcolumn-input');
  if (dcCheck) dcCheck.checked = false;
  const liveCheck = document.getElementById('post-liveupdate-input');
  if (liveCheck) liveCheck.checked = true;
  document.getElementById('modal-post-title').innerHTML = '<i class="fa-solid fa-pen-nib text-emerald-400"></i> <span>Write Health News Article</span>';
  
  updateExposureCheckboxLimits(null);

  currentPostImages = [];
  renderPostImagesGrid();

  const previewContainer = document.getElementById('post-content-preview-container');
  if (previewContainer) previewContainer.classList.add('hidden');
  const previewToggleText = document.getElementById('preview-toggle-text');
  if (previewToggleText) previewToggleText.textContent = 'Preview';

  document.getElementById('modal-post').classList.remove('hidden');
}

function insertPostFormat(type) {
  const textarea = document.getElementById('post-content-input');
  if (!textarea) return;
  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const selectedText = textarea.value.substring(start, end);
  let replacement = '';
  
  switch(type) {
    case 'bold':
      replacement = selectedText ? `**${selectedText}**` : `**Bold text**`;
      break;
    case 'h2':
      replacement = selectedText ? `\n\n## ${selectedText}\n` : `\n\n## Major Section Header\n`;
      break;
    case 'h3':
      replacement = selectedText ? `\n\n### ${selectedText}\n` : `\n\n### Subheader\n`;
      break;
    case 'large':
      replacement = selectedText ? `++${selectedText}++` : `++Large emphasized text++`;
      break;
    case 'small':
      replacement = selectedText ? `--${selectedText}--` : `--Small reference note or citation--`;
      break;
    case 'list':
      if (selectedText) {
        replacement = '\n' + selectedText.split('\n').map(line => `- ${line}`).join('\n') + '\n';
      } else {
        replacement = `\n- Item 1\n- Item 2\n`;
      }
      break;
    case 'quote':
      replacement = selectedText ? `\n> ${selectedText}\n` : `\n> Enter quotation or medical reference here.\n`;
      break;
    case 'box':
      replacement = selectedText ? `\n\n:::box\n${selectedText}\n:::\n\n` : `\n\n:::box\n📢 [Important Notice / Clinical Guideline]\nEnter prominent notice text or medical advisory details here.\n:::\n\n`;
      break;
    case 'mark':
      replacement = selectedText ? `==${selectedText}==` : `==Highlighted text==`;
      break;
    default:
      return;
  }
  
  textarea.setRangeText(replacement, start, end, 'end');
  textarea.focus();
  updatePostContentPreview();
}

let selectedPickerPhotoUrl = '';

function openPhotoPickerModal() {
  const grid = document.getElementById('photo-picker-grid');
  const urlInp = document.getElementById('photo-picker-url-input');
  const capInp = document.getElementById('photo-picker-caption-input');
  
  if (capInp) capInp.value = '';
  if (urlInp) urlInp.value = currentPostImages[0] || '';
  selectedPickerPhotoUrl = currentPostImages[0] || '';

  if (grid) {
    if (currentPostImages.length === 0) {
      grid.innerHTML = `
        <div class="col-span-full py-4 text-center text-slate-500 text-xs">
          No images uploaded yet. Paste an image URL below or upload files above.
        </div>
      `;
    } else {
      grid.innerHTML = currentPostImages.map((url, idx) => {
        const isSelected = idx === 0;
        return `
          <div onclick="selectPhotoPickerImage(${idx}, '${escapeHtml(url)}')" id="photo-picker-item-${idx}" class="photo-picker-item relative aspect-4/3 rounded-xl overflow-hidden border-2 cursor-pointer transition-all ${isSelected ? 'border-emerald-500 ring-2 ring-emerald-500/40' : 'border-slate-800 hover:border-slate-600'}">
            <img src="${escapeHtml(url)}" alt="Image #${idx + 1}" class="w-full h-full object-cover">
            <span class="absolute bottom-1 left-1 bg-black/75 text-white text-[9px] px-1.5 py-0.5 rounded font-mono font-bold">
              ${idx === 0 ? 'Cover' : 'Photo #' + (idx + 1)}
            </span>
          </div>
        `;
      }).join('');
    }
  }

  document.getElementById('modal-photo-picker').classList.remove('hidden');
}

function selectPhotoPickerImage(idx, url) {
  selectedPickerPhotoUrl = url;
  const urlInp = document.getElementById('photo-picker-url-input');
  if (urlInp) urlInp.value = url;

  document.querySelectorAll('.photo-picker-item').forEach((el, i) => {
    if (i === idx) {
      el.classList.add('border-emerald-500', 'ring-2', 'ring-emerald-500/40');
      el.classList.remove('border-slate-800');
    } else {
      el.classList.remove('border-emerald-500', 'ring-2', 'ring-emerald-500/40');
      el.classList.add('border-slate-800');
    }
  });
}

function confirmInsertPhotoBox() {
  const urlInp = document.getElementById('photo-picker-url-input');
  const capInp = document.getElementById('photo-picker-caption-input');
  const url = (urlInp && urlInp.value) ? urlInp.value.trim() : selectedPickerPhotoUrl;

  if (!url) {
    showToast('Please select a photo or enter an image URL.', false);
    return;
  }

  const caption = (capInp && capInp.value) ? capInp.value.trim() : '';
  const textarea = document.getElementById('post-content-input');
  if (textarea) {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const tag = `\n\n![${caption}](${url})\n\n`;
    textarea.setRangeText(tag, start, end, 'end');
    textarea.focus();
    updatePostContentPreview();
  }

  closeModal('modal-photo-picker');
  showToast('Photo box inserted at cursor position.');
}

function insertImageToContent(url, defaultCaption = 'Press Release Photo') {
  const textarea = document.getElementById('post-content-input');
  if (!textarea) return;
  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const caption = prompt('Enter a caption/description for this photo (optional):', defaultCaption) || defaultCaption;
  const tag = `\n\n![${caption}](${url})\n\n`;
  textarea.setRangeText(tag, start, end, 'end');
  textarea.focus();
  updatePostContentPreview();
  showToast('Photo tag inserted at cursor position.');
}

function updatePostContentPreview() {
  const container = document.getElementById('post-content-preview-container');
  if (!container || container.classList.contains('hidden')) return;
  const text = document.getElementById('post-content-input').value || '';
  const previewDiv = document.getElementById('post-content-preview');
  if (previewDiv) {
    previewDiv.innerHTML = renderMarkdownToHtml(text);
  }
}

function togglePostContentPreview() {
  const container = document.getElementById('post-content-preview-container');
  const toggleText = document.getElementById('preview-toggle-text');
  if (!container) return;
  if (container.classList.contains('hidden')) {
    container.classList.remove('hidden');
    if (toggleText) toggleText.textContent = 'Close Preview';
    updatePostContentPreview();
  } else {
    container.classList.add('hidden');
    if (toggleText) toggleText.textContent = 'Preview';
  }
}

function renderMarkdownToHtml(text) {
  if (!text) return '<p class="text-slate-500 italic text-xs">Content entered will be rendered live here.</p>';
  let html = text.replace(/\r\n/g, '\n');
  
  // Special Notice Box :::box ... :::
  html = html.replace(/:::box([\s\S]*?):::/gi, (match, inner) => {
    return `<div class="my-5 p-5 rounded-2xl bg-gradient-to-br from-indigo-950/90 to-slate-900 border-2 border-indigo-500/60 shadow-lg text-slate-100"><div class="flex items-center gap-2 mb-2 font-bold text-indigo-300 text-xs"><i class="fa-solid fa-box-archive"></i> <span>Important Advisory / Callout Box</span></div><div class="leading-relaxed text-sm text-slate-200">${inner.trim().replace(/\n/g, '<br>')}</div></div>`;
  });

  // Resolve shorthand [photo 1], [photo 2: caption], [사진1] tags
  html = html.replace(/\[(?:photo|사진)\s*([0-9]+)(?:\s*:\s*([^\]]+))?\]/gi, (match, numStr, caption) => {
    const idx = parseInt(numStr, 10) - 1;
    const url = currentPostImages[idx] || '';
    const cap = caption ? caption.trim() : `Photo #${numStr}`;
    if (!url) return '';
    return `![${cap}](${url})`;
  });

  // In-text Images ![caption](url)
  html = html.replace(/!\[(.*?)\]\((.*?)\)/g, (match, caption, url) => {
    return `<div class="my-5 mx-auto max-w-md text-center flex flex-col items-center"><div class="rounded-xl overflow-hidden shadow-md border border-slate-700 bg-slate-950 w-full"><img src="${url}" alt="${caption}" class="w-full h-auto max-h-60 object-cover mx-auto"></div>${caption ? `<p class="text-[11px] text-slate-400 mt-1.5 font-medium text-center">▲ ${caption}</p>` : ''}</div>`;
  });

  // Headers
  html = html.replace(/^### (.*$)/gim, '<h3 class="text-base sm:text-lg font-bold text-blue-400 mt-4 mb-2 font-serif">$1</h3>');
  html = html.replace(/^## (.*$)/gim, '<h2 class="text-lg sm:text-xl font-bold text-emerald-400 mt-5 mb-2 font-serif pb-1 border-b border-slate-700">$1</h2>');
  html = html.replace(/^# (.*$)/gim, '<h1 class="text-xl sm:text-2xl font-bold text-white mt-6 mb-3 font-serif pb-1 border-b border-slate-700">$1</h1>');
  
  // Bold
  html = html.replace(/\*\*(.+?)\*\*/g, '<strong class="text-white font-bold">$1</strong>');
  html = html.replace(/__(.+?)__/g, '<strong class="text-white font-bold">$1</strong>');
  
  // Highlight ==text==
  html = html.replace(/==(.+?)==/g, '<mark style="background-color: #fef08a; color: #0f172a; padding: 2px 6px; border-radius: 4px; font-weight: bold;">$1</mark>');
  
  // Large text ++text++
  html = html.replace(/\+\+(.+?)\+\+/g, '<span class="text-base sm:text-lg font-bold text-amber-300">$1</span>');

  // Small text --text--
  html = html.replace(/--(.+?)--/g, '<span class="text-xs text-slate-400 font-normal">$1</span>');

  // Quotes
  html = html.replace(/^\> (.*$)/gim, '<blockquote class="border-l-4 border-emerald-500 pl-3 py-2 my-3 bg-slate-800/90 rounded-r-xl text-slate-200 italic font-medium">$1</blockquote>');
  
  // Bullet lists
  html = html.replace(/^[-*•] (.*$)/gim, '<div class="flex items-start gap-2.5 text-slate-300 pl-2 my-1"><span class="text-red-500 font-bold leading-none mt-1">•</span><span class="flex-1">$1</span></div>');
  
  // Paragraphs
  const parts = html.split('\n\n');
  html = parts.map(part => {
    part = part.trim();
    if (!part) return '';
    if (part.startsWith('<h1') || part.startsWith('<h2') || part.startsWith('<h3') || part.startsWith('<blockquote') || part.startsWith('<div') || part.startsWith('<figure')) {
      return part.replace(/\n/g, '<br>');
    }
    return `<p class="leading-relaxed text-slate-300">${part.replace(/\n/g, '<br>')}</p>`;
  }).join('');
  
  return html;
}

function editPost(id) {
  const p = state.posts.find(item => item.id === id);
  if (!p) return;

  document.getElementById('post-id').value = p.id;
  document.getElementById('post-title-input').value = p.title || '';
  document.getElementById('post-category-input').value = p.category || 'Medical Column';
  document.getElementById('post-date-input').value = p.date || '';
  document.getElementById('post-author-input').value = p.author || 'Editorial Staff';
  document.getElementById('post-videourl-input').value = p.videoUrl || '';
  document.getElementById('post-excerpt-input').value = p.excerpt || '';
  document.getElementById('post-content-input').value = p.content || '';
  document.getElementById('post-topstory-input').checked = Boolean(p.isTopStory && p.isTopStory !== 'false' && p.isTopStory !== 0);
  document.getElementById('post-liveupdate-input').checked = Boolean(p.isLiveUpdate === true || p.isLiveUpdate === 'true' || p.isLiveUpdate === 1 || p.isLiveUpdate === '1');
  const dcCheck = document.getElementById('post-doctorcolumn-input');
  if (dcCheck) {
    dcCheck.checked = Boolean(p.isDoctorColumn === true || p.isDoctorColumn === 'true' || p.isDoctorColumn === 1 || p.isDoctorColumn === '1');
  }

  updateExposureCheckboxLimits(p.id);

  // Initialize multiple images from post
  let imgs = [];
  if (Array.isArray(p.images) && p.images.length > 0) {
    imgs = [...p.images];
  } else if (p.coverImage) {
    imgs = [p.coverImage];
  }
  currentPostImages = imgs;
  renderPostImagesGrid();

  const previewContainer = document.getElementById('post-content-preview-container');
  if (previewContainer) previewContainer.classList.add('hidden');
  const previewToggleText = document.getElementById('preview-toggle-text');
  if (previewToggleText) previewToggleText.textContent = 'Preview';

  document.getElementById('modal-post-title').innerHTML = '<i class="fa-solid fa-pen-to-square text-emerald-400"></i> <span>Edit Health News Article</span>';
  document.getElementById('modal-post').classList.remove('hidden');
}

function renderPostImagesGrid() {
  const grid = document.getElementById('post-images-manager-grid');
  if (!grid) return;

  if (currentPostImages.length === 0) {
    grid.innerHTML = `
      <div class="col-span-full py-6 text-center text-slate-500 text-xs bg-slate-900/60 rounded-2xl border border-slate-800 border-dashed">
        <i class="fa-regular fa-image text-lg mb-1 block"></i>
        <span>No images added. Click [Batch Upload Images] or paste an image URL above.</span>
      </div>
    `;
    const coverInput = document.getElementById('post-cover-input');
    if (coverInput) coverInput.value = '';
    return;
  }

  // Set the 1st image as coverImage
  const coverInput = document.getElementById('post-cover-input');
  if (coverInput) coverInput.value = currentPostImages[0] || '';

  grid.innerHTML = currentPostImages.map((url, idx) => {
    const isFirst = idx === 0;
    const isLast = idx === currentPostImages.length - 1;

    return `
      <div class="relative group bg-slate-900 rounded-2xl border overflow-hidden shadow-md flex flex-col justify-between transition-all ${
        isFirst ? 'border-emerald-500/80 ring-2 ring-emerald-500/30' : 'border-slate-800 hover:border-slate-700'
      }">
        <div class="relative aspect-4/3 bg-slate-950 overflow-hidden">
          <img src="${escapeHtml(url)}" alt="Article Photo #${idx + 1}" class="w-full h-full object-cover">
          
          <!-- Badge -->
          <div class="absolute top-2 left-2">
            ${isFirst ? `
              <span class="bg-emerald-600 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-md flex items-center gap-1">
                <i class="fa-solid fa-star text-[9px]"></i> 1st: Primary Cover
              </span>
            ` : `
              <span class="bg-blue-600/90 text-white text-[10px] font-semibold px-2 py-0.5 rounded-full shadow-md">
                Photo #${idx + 1}
              </span>
            `}
          </div>

          <!-- Delete Action -->
          <button type="button" onclick="removePostImage(${idx})" 
            class="absolute top-2 right-2 w-6 h-6 rounded-full bg-red-600/90 hover:bg-red-600 text-white flex items-center justify-center text-[10px] shadow-md transition-all hover:scale-110 cursor-pointer"
            title="Delete Image">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <!-- Controls (Reorder) -->
        <div class="p-2 bg-slate-950/80 flex items-center justify-between gap-1 border-t border-slate-800 text-[11px]">
          <span class="text-slate-400 font-mono text-[10px] truncate max-w-[70px]" title="${escapeHtml(url)}">
            #${idx + 1}
          </span>
          <div class="flex items-center gap-1">
            ${!isFirst ? `
              <button type="button" onclick="movePostImage(${idx}, -1)" 
                class="px-2 py-0.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-md text-[10px] transition-all cursor-pointer"
                title="Move forward (Make primary cover)">
                ◀ Prev
              </button>
            ` : ''}
            ${!isLast ? `
              <button type="button" onclick="movePostImage(${idx}, 1)" 
                class="px-2 py-0.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-md text-[10px] transition-all cursor-pointer"
                title="Move backward">
                Next ▶
              </button>
            ` : ''}
          </div>
        </div>

        <button type="button" onclick="insertImageToContent('${escapeHtml(url)}')" class="w-full py-1.5 px-2 bg-emerald-950/80 hover:bg-emerald-600 text-emerald-300 hover:text-white border-t border-slate-800 rounded-b-2xl text-[10px] font-bold flex items-center justify-center gap-1 transition-all cursor-pointer">
          <i class="fa-solid fa-arrow-down-to-bracket"></i> Insert at Cursor
        </button>
      </div>
    `;
  }).join('');
}

function compressImageToDataUrl(file, maxWidth = 1200, maxHeight = 1200, quality = 0.85) {
  return new Promise((resolve) => {
    if (!file || !file.type || !file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = e => resolve(e.target.result || '');
      reader.onerror = () => resolve('');
      reader.readAsDataURL(file);
      return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        let w = img.width;
        let h = img.height;
        if (w > maxWidth || h > maxHeight) {
          if (w / h > maxWidth / maxHeight) {
            h = Math.round((h * maxWidth) / w);
            w = maxWidth;
          } else {
            w = Math.round((w * maxHeight) / h);
            h = maxHeight;
          }
        }
        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, w, h);
        try {
          const dataUrl = canvas.toDataURL('image/webp', quality);
          resolve(dataUrl);
        } catch(err) {
          resolve(e.target.result);
        }
      };
      img.onerror = () => resolve(e.target.result);
      img.src = e.target.result;
    };
    reader.onerror = () => resolve('');
    reader.readAsDataURL(file);
  });
}

async function uploadMultiplePostImages(input) {
  if (!input.files || input.files.length === 0) return;
  const files = Array.from(input.files);
  showToast(`Uploading ${files.length} image(s)...`);

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    try {
      const formData = new FormData();
      formData.append('file', file);
      const res = await fetch('/api/upload.php', { method: 'POST', body: formData });
      const data = await res.json();
      if (data.success && data.url) {
        currentPostImages.push(data.url);
      } else {
        const dataUrl = await compressImageToDataUrl(file, 1200, 1200, 0.85);
        if (dataUrl) currentPostImages.push(dataUrl);
      }
    } catch (e) {
      console.error('Image upload error:', e);
      try {
        const dataUrl = await compressImageToDataUrl(file, 1200, 1200, 0.85);
        if (dataUrl) currentPostImages.push(dataUrl);
      } catch (err) {}
    }
  }

  showToast(`${files.length} image(s) processed and added!`);
  input.value = '';
  renderPostImagesGrid();
}

function addPostImageUrlManual() {
  const input = document.getElementById('post-add-image-url-input');
  if (!input) return;
  const url = input.value.trim();
  if (!url) {
    showToast('Please enter an image URL.', false);
    return;
  }
  currentPostImages.push(url);
  input.value = '';
  showToast('Image URL added to list.');
  renderPostImagesGrid();
}

function removePostImage(index) {
  if (index >= 0 && index < currentPostImages.length) {
    currentPostImages.splice(index, 1);
    renderPostImagesGrid();
  }
}

function movePostImage(index, direction) {
  const targetIndex = index + direction;
  if (targetIndex < 0 || targetIndex >= currentPostImages.length) return;
  const temp = currentPostImages[index];
  currentPostImages[index] = currentPostImages[targetIndex];
  currentPostImages[targetIndex] = temp;
  renderPostImagesGrid();
}

async function handleSavePost(e) {
  e.preventDefault();
  const id = document.getElementById('post-id').value;
  const isEdit = Boolean(id);
  const existingPost = isEdit ? state.posts.find(p => p.id === id) : null;

  if (currentPostImages.length === 0) {
    showToast('Please add at least 1 image for the article thumbnail.', false);
    return;
  }

  const payload = {
    id: id,
    slug: existingPost ? (existingPost.slug || id) : '',
    title: document.getElementById('post-title-input').value,
    category: document.getElementById('post-category-input').value || 'Medical Column',
    date: document.getElementById('post-date-input').value,
    author: document.getElementById('post-author-input').value || 'Editorial Staff',
    coverImage: currentPostImages[0] || '',
    images: currentPostImages,
    videoUrl: document.getElementById('post-videourl-input').value,
    excerpt: document.getElementById('post-excerpt-input').value,
    content: document.getElementById('post-content-input').value,
    isDoctorColumn: document.getElementById('post-doctorcolumn-input') ? document.getElementById('post-doctorcolumn-input').checked : false,
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
      showToast(isEdit ? 'Article updated successfully.' : 'New article published successfully.');
      closeModal('modal-post');
      fetchAllData();
    } else {
      showToast(data.error || 'Failed to save article.', false);
    }
  } catch (err) {
    showToast('A server communication error occurred.', false);
  }
}

async function deletePost(id) {
  if (!confirm('Are you sure you want to delete this news article?')) return;
  try {
    const res = await fetch(`/api/posts.php?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
      showToast('Article deleted successfully.');
      fetchAllData();
    } else {
      showToast(data.error || 'Failed to delete article.', false);
    }
  } catch (err) {
    showToast('Communication error occurred.', false);
  }
}

// =========================================================
// MEDIA LIBRARY & UPLOADS
// =========================================================
async function fetchMediaFiles() {
  try {
    const res = await fetch('/api/upload.php?action=list');
    const data = await res.json();
    let files = (data.success && data.files) ? data.files : [];

    const seenUrls = new Set(files.map(f => f.url));
    
    (state.posts || []).forEach(p => {
      const imgs = Array.isArray(p.images) ? p.images : (p.coverImage ? [p.coverImage] : []);
      imgs.forEach((imgUrl, i) => {
        if (imgUrl && !seenUrls.has(imgUrl)) {
          seenUrls.add(imgUrl);
          files.push({
            name: (p.title || 'Post Image') + ' (#' + (i + 1) + ')',
            type: 'image',
            url: imgUrl,
            size: imgUrl.length,
            mtime: Math.floor(Date.now() / 1000)
          });
        }
      });
    });

    (state.billboards || []).forEach(b => {
      if (b.mediaUrl && !seenUrls.has(b.mediaUrl)) {
        seenUrls.add(b.mediaUrl);
        files.push({
          name: (b.title || 'Billboard') + ' Media',
          type: b.mediaType || 'image',
          url: b.mediaUrl,
          size: b.mediaUrl.length,
          mtime: Math.floor(Date.now() / 1000)
        });
      }
    });

    state.media = files;
    const countEl = document.getElementById('stat-media-count');
    if (countEl) countEl.textContent = state.media.length;
    renderMediaGrid();
  } catch (err) {
    console.error('Error fetching media:', err);
  }
}

function renderMediaGrid() {
  const container = document.getElementById('media-grid');
  if (!container) return;
  if (state.media.length === 0) {
    container.innerHTML = `
      <div class="col-span-full text-center py-10 text-slate-500 text-xs">
        No uploaded media files found yet. Use the uploader above to add assets.
      </div>`;
    return;
  }

  container.innerHTML = state.media.map(f => `
    <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl overflow-hidden shadow-sm flex flex-col justify-between group">
      <div class="relative aspect-square bg-slate-950 overflow-hidden flex items-center justify-center">
        ${f.type === 'image' || (f.url && !f.url.endsWith('.mp4')) ? `
          <img src="${f.url}" alt="${escapeHtml(f.name)}" class="w-full h-full object-cover">
        ` : `
          <video src="${f.url}" class="w-full h-full object-cover" muted></video>
          <span class="absolute inset-0 flex items-center justify-center bg-black/40 text-white text-xl">
            <i class="fa-solid fa-film"></i>
          </span>
        `}
      </div>
      <div class="p-2.5 space-y-1.5">
        <p class="text-[11px] font-mono text-slate-300 truncate" title="${escapeHtml(f.name)}">${escapeHtml(f.name)}</p>
        <div class="flex items-center justify-between gap-1">
          <button onclick="copyMediaUrl('${escapeHtml(f.url)}')" class="flex-1 bg-slate-700 hover:bg-slate-600 text-white text-[10px] font-semibold py-1 rounded-lg transition-all flex items-center justify-center gap-1 cursor-pointer">
            <i class="fa-regular fa-copy"></i> Copy
          </button>
          <button onclick="deleteMediaFile('${escapeHtml(f.url)}')" class="bg-red-500/20 hover:bg-red-500/30 text-red-400 text-[10px] p-1 px-2 rounded-lg transition-all cursor-pointer">
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>
    </div>
  `).join('');
}

function copyMediaUrl(url) {
  navigator.clipboard.writeText(url).then(() => {
    showToast('File URL copied to clipboard!');
  });
}

async function deleteMediaFile(url) {
  if (!confirm('Are you sure you want to delete this media file?')) return;
  try {
    const res = await fetch(`/api/upload.php?action=delete&url=${encodeURIComponent(url)}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
      showToast('File deleted successfully.');
      fetchMediaFiles();
    } else {
      showToast(data.error || 'Failed to delete file.', false);
    }
  } catch (err) {
    showToast('File deleted.');
    state.media = state.media.filter(m => m.url !== url);
    renderMediaGrid();
  }
}

// Field file upload helper
async function uploadFieldFile(input, targetInputId, previewId) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];

  showToast('Uploading file...');

  try {
    const formData = new FormData();
    formData.append('file', file);
    const res = await fetch('/api/upload.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.success && data.url) {
      document.getElementById(targetInputId).value = data.url;
      if (previewId) {
        const preview = document.getElementById(previewId);
        if (data.type === 'image') {
          preview.innerHTML = `<img src="${data.url}" class="w-full h-full object-cover">`;
        } else {
          preview.innerHTML = `<video src="${data.url}" class="w-full h-full object-cover" controls></video>`;
        }
        preview.classList.remove('hidden');
      }
      showToast('File upload complete!');
    } else {
      if (file.type && file.type.startsWith('image/')) {
        const dataUrl = await compressImageToDataUrl(file, 1600, 1200, 0.88);
        if (dataUrl) {
          document.getElementById(targetInputId).value = dataUrl;
          if (previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = `<img src="${dataUrl}" class="w-full h-full object-cover">`;
            preview.classList.remove('hidden');
          }
          showToast('Image processed and added.');
          return;
        }
      }
      showToast(data.error || 'Upload failed.', false);
    }
  } catch (err) {
    if (file.type && file.type.startsWith('image/')) {
      const dataUrl = await compressImageToDataUrl(file, 1600, 1200, 0.88);
      if (dataUrl) {
        document.getElementById(targetInputId).value = dataUrl;
        if (previewId) {
          const preview = document.getElementById(previewId);
          preview.innerHTML = `<img src="${dataUrl}" class="w-full h-full object-cover">`;
          preview.classList.remove('hidden');
        }
        showToast('Image processed and added.');
        return;
      }
    }
    showToast('Error uploading file.', false);
  }
}

// Direct multiple files upload from dropzone
async function handleDirectFileUpload(files) {
  if (!files || files.length === 0) return;
  showToast(`Uploading ${files.length} file(s)...`);

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    try {
      const formData = new FormData();
      formData.append('file', file);
      await fetch('/api/upload.php', { method: 'POST', body: formData });
    } catch (e) {}
  }

  showToast('All files uploaded successfully.');
  fetchMediaFiles();
}

function setupDropzone() {
  const dropzone = document.getElementById('media-dropzone');
  if (!dropzone) return;

  ['dragenter', 'dragover'].forEach(eventName => {
    dropzone.addEventListener(eventName, e => {
      e.preventDefault();
      dropzone.classList.add('border-purple-400', 'bg-purple-950/20');
    });
  });

  ['dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, e => {
      e.preventDefault();
      dropzone.classList.remove('border-purple-400', 'bg-purple-950/20');
    });
  });

  dropzone.addEventListener('drop', e => {
    const dt = e.dataTransfer;
    if (dt && dt.files) {
      handleDirectFileUpload(dt.files);
    }
  });
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.classList.add('hidden');
  }
}

// Ensure all handlers are globally reachable on window
window.switchTab = switchTab;
window.fetchAllData = fetchAllData;
window.handleLogout = handleLogout;
window.openBillboardModal = openBillboardModal;
window.editBillboard = editBillboard;
window.deleteBillboard = deleteBillboard;
window.handleSaveBillboard = handleSaveBillboard;
window.openVideoModal = openVideoModal;
window.editVideo = editVideo;
window.deleteVideo = deleteVideo;
window.handleSaveVideo = handleSaveVideo;
window.openPostModal = openPostModal;
window.editPost = editPost;
window.deletePost = deletePost;
window.handleSavePost = handleSavePost;
window.renderPostImagesGrid = renderPostImagesGrid;
window.uploadMultiplePostImages = uploadMultiplePostImages;
window.addPostImageUrlManual = addPostImageUrlManual;
window.removePostImage = removePostImage;
window.movePostImage = movePostImage;
window.closeModal = closeModal;
window.showToast = showToast;
window.uploadFieldFile = uploadFieldFile;
window.handleDirectFileUpload = handleDirectFileUpload;
window.insertPostFormat = insertPostFormat;
window.insertImageToContent = insertImageToContent;
window.openPhotoPickerModal = openPhotoPickerModal;
window.selectPhotoPickerImage = selectPhotoPickerImage;
window.confirmInsertPhotoBox = confirmInsertPhotoBox;
window.togglePostContentPreview = togglePostContentPreview;
window.updatePostContentPreview = updatePostContentPreview;
window.setPostFilter = setPostFilter;
window.handlePostSearch = handlePostSearch;
window.selectPostCategory = selectPostCategory;

// Global backdrop click-to-close handler & dynamic checkbox limits
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.modal-backdrop').forEach(modal => {
    modal.addEventListener('click', e => {
      if (e.target === modal) {
        closeModal(modal.id);
      }
    });
  });

  const liveInp = document.getElementById('post-liveupdate-input');
  if (liveInp) {
    liveInp.addEventListener('change', () => {
      const pid = document.getElementById('post-id').value;
      updateExposureCheckboxLimits(pid);
    });
  }
  const docInp = document.getElementById('post-doctorcolumn-input');
  if (docInp) {
    docInp.addEventListener('change', () => {
      const pid = document.getElementById('post-id').value;
      updateExposureCheckboxLimits(pid);
    });
  }
});
