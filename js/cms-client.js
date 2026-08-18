/**
 * El Portal de Salud NJ - Dynamic CMS Client Loader
 * Powers Panoramic Billboard Slider, Real-time Community Comments, and Live News Tickers
 */

(function () {
  'use strict';

  // ---------------------------------------------------------
  // 1. BILLBOARD BANNER SLIDER
  // ---------------------------------------------------------
  let billboards = [];
  let currentBillboardIndex = 0;
  let billboardTimer = null;
  let isPaused = false;

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  async function initBillboards() {
    const container = document.getElementById('gallery-billboard-container');
    if (!container) return;

    try {
      const res = await fetch('/api/billboards.php?active_only=1&_t=' + Date.now());
      const data = await res.json();
      if (data.success && Array.isArray(data.data) && data.data.length > 0) {
        billboards = data.data;
        renderBillboard();
      }
    } catch (e) {
      console.log('Billboard client fallback to server rendered state');
    }

    startBillboardTimer();
  }

  function renderBillboard() {
    const container = document.getElementById('gallery-billboard-container');
    if (!container || billboards.length === 0) return;

    const b = billboards[currentBillboardIndex] || billboards[0];
    const isVideo = b.mediaType === 'video' || (b.mediaUrl && (b.mediaUrl.endsWith('.mp4') || b.mediaUrl.endsWith('.webm')));
    const targetLink = b.linkUrl || '/about.html#contacto';
    const total = billboards.length;

    container.innerHTML = `
      <div class="billboard-slider-box" onmouseenter="window.cmsPauseBillboard()" onmouseleave="window.cmsResumeBillboard()">
        <a href="${targetLink}" class="billboard-slide-link" title="${escapeHtml(b.title)}">
          <div class="billboard-media-wrap">
            ${isVideo ? `
              <video src="${b.mediaUrl}" class="billboard-media-img" autoplay muted loop playsinline></video>
            ` : `
              <img id="billboard-active-img" 
                src="${b.mediaUrl || 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=2000&q=85&auto=format'}" 
                alt="${escapeHtml(b.title)}" 
                class="billboard-media-img">
            `}
            <div class="billboard-gradient-bottom"></div>
            <div class="billboard-gradient-side"></div>
          </div>

          <div class="billboard-content-overlay">
            <div class="billboard-inner-container">
              <div class="billboard-text-col">
                <div class="billboard-badges-row">
                  <span class="billboard-badge-cat">
                    ${escapeHtml(b.category || 'CAMPAÑA ESPECIAL')}
                  </span>
                  <span class="billboard-badge-counter">
                    ${currentBillboardIndex + 1} / ${total}
                  </span>
                </div>
                <h3 class="billboard-headline">
                  ${escapeHtml(b.title)}
                </h3>
                <p class="billboard-subtitle">
                  ${escapeHtml(b.subtitle || '')}
                </p>
              </div>

              <div class="billboard-btn-col">
                <span class="billboard-cta-btn">
                  <span>${escapeHtml(b.linkText || 'Más Información')}</span>
                  <span>→</span>
                </span>
              </div>
            </div>
          </div>
        </a>

        ${total > 1 ? `
          <button onclick="event.stopPropagation(); event.preventDefault(); window.cmsPrevBillboard();" 
            class="billboard-nav-arrow billboard-nav-prev" aria-label="Anterior">
            ‹
          </button>

          <button onclick="event.stopPropagation(); event.preventDefault(); window.cmsNextBillboard();" 
            class="billboard-nav-arrow billboard-nav-next" aria-label="Siguiente">
            ›
          </button>

          <div class="billboard-dots-row">
            ${billboards.map((dummy, idx) => `
              <button onclick="event.stopPropagation(); event.preventDefault(); window.cmsGoBillboard(${idx});" 
                class="billboard-dot-pill ${idx === currentBillboardIndex ? 'active' : ''}">
              </button>
            `).join('')}
          </div>
        ` : ''}
      </div>
    `;
  }

  function startBillboardTimer() {
    clearInterval(billboardTimer);
    billboardTimer = setInterval(() => {
      if (!isPaused && billboards.length > 1) {
        currentBillboardIndex = (currentBillboardIndex + 1) % billboards.length;
        renderBillboard();
      }
    }, 6000);
  }

  window.cmsPauseBillboard = function () { isPaused = true; };
  window.cmsResumeBillboard = function () { isPaused = false; };

  window.cmsNextBillboard = function () {
    if (billboards.length <= 1) return;
    currentBillboardIndex = (currentBillboardIndex + 1) % billboards.length;
    renderBillboard();
    startBillboardTimer();
  };

  window.cmsPrevBillboard = function () {
    if (billboards.length <= 1) return;
    currentBillboardIndex = (currentBillboardIndex - 1 + billboards.length) % billboards.length;
    renderBillboard();
    startBillboardTimer();
  };

  window.cmsGoBillboard = function (index) {
    if (index >= 0 && index < billboards.length) {
      currentBillboardIndex = index;
      renderBillboard();
      startBillboardTimer();
    }
  };

  // ---------------------------------------------------------
  // 2. REAL-TIME COMMENTS API INTEGRATION
  // ---------------------------------------------------------
  window.fetchComments = async function (articleId) {
    const listEl = document.getElementById(`commentsList-${articleId}`);
    const countEl = document.getElementById(`commentCount-${articleId}`);

    try {
      const res = await fetch(`/api/comments.php?slug=${encodeURIComponent(articleId)}&_t=${Date.now()}`);
      const data = await res.json();
      if (data.success && Array.isArray(data.comments)) {
        if (countEl) countEl.textContent = data.comments.length;
        if (listEl) renderCommentsHtml(listEl, data.comments);
        return;
      }
    } catch (e) {
      console.log('Error loading comments from API, fallback to localStorage');
    }

    // Fallback to local
    if (window.renderComments) {
      window.renderComments(articleId);
    }
  };

  function renderCommentsHtml(listEl, comments) {
    if (!listEl) return;
    if (comments.length === 0) {
      listEl.innerHTML = `<p style="font-size:0.85rem; color:#64748b; font-style:italic;">No hay comentarios aún. ¡Sé el primero en comentar!</p>`;
      return;
    }

    listEl.innerHTML = comments.map(c => `
      <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:4px; padding:0.85rem 1rem; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.35rem;">
          <span style="font-weight:700; font-size:0.875rem; color:#c91818; display:flex; align-items:center; gap:0.4rem;">
            <span style="width:24px; height:24px; border-radius:50%; background:#fef2f2; color:#c91818; display:inline-flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:800;">
              ${(c.nickname || 'U').charAt(0).toUpperCase()}
            </span>
            ${escapeHtml(c.nickname)}
          </span>
          <span style="font-size:0.75rem; color:#94a3b8;">${escapeHtml(c.createdAt || c.date || 'Reciente')}</span>
        </div>
        <p style="font-size:0.85rem; color:#334155; line-height:1.45; margin-top:0.25rem;">
          ${escapeHtml(c.content || c.text || '')}
        </p>
      </div>
    `).join('');
  }

  window.submitCommentApi = async function (articleId) {
    const nickInput = document.getElementById(`commentNickname-${articleId}`);
    const textInput = document.getElementById(`commentText-${articleId}`);

    if (!nickInput || !textInput) return;
    const nickname = nickInput.value.trim();
    const content = textInput.value.trim();

    if (!nickname || !content) return;

    try {
      const res = await fetch('/api/comments.php?action=add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ slug: articleId, nickname, content })
      });
      const data = await res.json();
      if (data.success) {
        textInput.value = '';
        window.fetchComments(articleId);
        return;
      }
    } catch (e) {}

    // Fallback
    if (window.addComment) {
      window.addComment(articleId);
    }
  };

  // Initialize on page load
  document.addEventListener('DOMContentLoaded', () => {
    initBillboards();
  });

})();
