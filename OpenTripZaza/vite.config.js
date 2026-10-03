import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { readFileSync } from 'node:fs'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), {
    name: 'blog-content-for-php',
    generateBundle() {
      // React and the PHP renderer share the same approved blog content.
      this.emitFile({
        type: 'asset',
        fileName: 'blog-content.json',
        source: readFileSync(new URL('./src/content/blog.json', import.meta.url), 'utf8'),
      })
    },
  }],
  build: {
    cssCodeSplit: true,
    sourcemap: false,
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (id.includes('node_modules/react') || id.includes('node_modules/scheduler')) return 'react-vendor'
          if (id.includes('node_modules/i18next')) return 'i18n-vendor'
        },
      },
    },
  },
})
