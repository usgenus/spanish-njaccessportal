<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!empty($_SESSION['cms_logged_in']) && $_SESSION['cms_logged_in'] === true) {
    header('Location: /admin/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Healthcare Access Portal CMS Administrator Login</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              red: '#c91818',
              dark: '#1e1e24'
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
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4 font-sans antialiased selection:bg-red-500 selection:text-white">

  <div class="w-full max-w-md">
    <!-- Header Box -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-slate-950/90 border border-slate-700/80 text-white shadow-2xl mb-4 p-3.5 ring-4 ring-red-500/20">
        <span class="text-2xl font-black text-red-600">NJ</span>
      </div>
      <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Healthcare Access Portal</h1>
      <p class="text-sm text-slate-400 mt-1">Integrated Content Management System · Spanish Portal NJAP</p>
    </div>

    <!-- Login Card -->
    <div class="bg-slate-800/90 border border-slate-700/80 backdrop-blur-xl rounded-3xl p-8 shadow-2xl relative overflow-hidden">
      <div class="absolute -right-12 -top-12 w-40 h-40 bg-red-500/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -left-12 -bottom-12 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="mb-6">
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
          Administrator Login
        </h2>
        <p class="text-xs text-slate-400 mt-1">Enter your administrator credentials to manage news, billboards, videos, and media.</p>
      </div>

      <div id="loginError" class="hidden mb-5 p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-semibold flex items-center gap-2">
        <span>⚠️</span>
        <span id="loginErrorText">Incorrect username or password.</span>
      </div>

      <form id="loginForm" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2" for="username">Username</label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">👤</span>
            <input type="text" id="username" name="username" required autocomplete="username"
              class="w-full bg-slate-900/90 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 transition-all"
              placeholder="Enter your username">
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2" for="password">Password</label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">🔒</span>
            <input type="password" id="password" name="password" required autocomplete="current-password"
              class="w-full bg-slate-900/90 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 transition-all"
              placeholder="Enter your password">
          </div>
        </div>

        <button type="submit" id="submitBtn"
          class="w-full mt-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-red-600/30 hover:shadow-red-600/50 transition-all flex items-center justify-center gap-2 text-sm cursor-pointer">
          <span>Access Admin Dashboard</span>
          <span>→</span>
        </button>
      </form>

      <div class="mt-6 pt-5 border-t border-slate-700/60 text-center">
        <a href="/" class="text-xs text-slate-400 hover:text-slate-200 transition-colors flex items-center justify-center gap-1">
          <span>←</span>
          <span>Return to Portal Homepage</span>
        </a>
      </div>
    </div>

    <p class="text-center text-[11px] text-slate-500 mt-6">
      © 2026 Healthcare Access Portal — CMS Engine. All rights reserved.
    </p>
  </div>

  <script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const errBox = document.getElementById('loginError');
      const errText = document.getElementById('loginErrorText');
      const btn = document.getElementById('submitBtn');
      
      errBox.classList.add('hidden');
      btn.disabled = true;
      btn.innerHTML = '<span class="animate-spin text-lg">⚙️</span> <span>Authenticating credentials...</span>';

      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value.trim();

      try {
        const res = await fetch('/api/auth.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, password })
        });
        const data = await res.json();

        if (data.success) {
          btn.innerHTML = '<span>✅ Login successful! Redirecting...</span>';
          setTimeout(() => {
            window.location.href = '/admin/';
          }, 300);
        } else {
          errBox.classList.remove('hidden');
          errText.textContent = data.error || 'Invalid credentials.';
          btn.disabled = false;
          btn.innerHTML = '<span>Access Admin Dashboard</span> <span>→</span>';
        }
      } catch (err) {
        errBox.classList.remove('hidden');
        errText.textContent = 'Server connection error.';
        btn.disabled = false;
        btn.innerHTML = '<span>Access Admin Dashboard</span> <span>→</span>';
      }
    });
  </script>
</body>
</html>
