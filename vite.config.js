import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'index.html'),
        premium: resolve(__dirname, 'servicio-premium-de-navegacion-y-acceso-a-la-salud.html'),
        tool: resolve(__dirname, 'tool.html'),
        noticias: resolve(__dirname, 'noticias.html'),
        medicare: resolve(__dirname, 'medicare.html'),
        about: resolve(__dirname, 'about.html')
      }
    }
  }
});
