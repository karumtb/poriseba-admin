import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'
import autoprefixer from 'autoprefixer'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')

  console.log('MODE:', mode)


  return {
    plugins: [
      vue(),
    ],

    base: env.VITE_BASE_URL || '/',

    css: {
      postcss: {
        plugins: [
          autoprefixer(),
        ],
      },
    },

    resolve: {
      alias: {
        '@': fileURLToPath(
          new URL('./resources/js', import.meta.url)
        ),
      },

      extensions: [
        '.mjs',
        '.js',
        '.ts',
        '.jsx',
        '.tsx',
        '.json',
        '.vue',
        '.scss',
      ],
    },

    build: {
      outDir: 'public/build',
      emptyOutDir: true,
      manifest: 'manifest.json',

      rollupOptions: {
        input: 'resources/js/main.js',
      },
    },
  }
})
