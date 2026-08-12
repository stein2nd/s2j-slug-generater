import { defineConfig } from 'vite';
import { resolve } from 'path';
import { rmSync } from 'fs';

// FLUSH_DIST環境変数がtrueの場合、distディレクトリを削除
if (process.env.FLUSH_DIST === 'true') {
    rmSync(
        'dist',
        {
            recursive: true,
            force: true
        }
    );
}

// ビルド対象の設定を取得（npmスクリプト名から推測）
const getBuildTarget = () => {
  const npmScript = process.env.npm_lifecycle_event;
  if (npmScript?.includes('admin')) return 'admin';
  if (npmScript?.includes('gutenberg')) return 'gutenberg';
  if (npmScript?.includes('classic')) return 'classic';
  return 'gutenberg'; // デフォルト
};

const buildTarget = getBuildTarget();

// エントリーポイントとライブラリ名、SCSSファイルを設定
const getBuildConfig = (target: string) => {
    switch (target) {
        case 'admin':
            return {
                entry: resolve(import.meta.dirname, 'src/admin/index.tsx'),
                name: 'S2JSlugGeneraterAdmin',
                scss: resolve(import.meta.dirname, 'src/styles/admin.scss')
            };
        case 'gutenberg':
            return {
                entry: resolve(import.meta.dirname, 'src/gutenberg/index.tsx'),
                name: 'S2JSlugGeneraterGutenberg',
                scss: resolve(import.meta.dirname, 'src/styles/gutenberg.scss')
            };
        case 'classic':
            return {
                entry: resolve(import.meta.dirname, 'src/classic/index.ts'),
                name: 'S2JSlugGeneraterClassic',
                scss: resolve(import.meta.dirname, 'src/styles/classic.scss')
            };
        default:
            return {
                entry: resolve(import.meta.dirname, 'src/gutenberg/index.tsx'),
                name: 'S2JSlugGeneraterGutenberg',
                scss: resolve(import.meta.dirname, 'src/styles/gutenberg.scss')
            };
    }
};

const buildConfig = getBuildConfig(buildTarget);

export default defineConfig({
  logLevel: (process.env.VITE_LOG_LEVEL as 'info' | 'warn' | 'error' | 'silent') || 'warn',
  define: {
    'process.env': {},
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'development'),
    'process.env.VITE_LOG_LEVEL': JSON.stringify(process.env.VITE_LOG_LEVEL || 'warn'),
  },
  build: {
    lib: {
      entry: buildConfig.entry,
      formats: ['iife'],
      name: buildConfig.name,
    },
    rollupOptions: {
      external: (id) => {
        // WordPress Gutenberg関連のモジュールを外部化
        if (id.startsWith('@wordpress/')) return true;
        // React は WP 同梱 (wp.element) を使う。jsx-runtime を同梱すると要素型が食い違いクラッシュする
        if (
          id === 'react' ||
          id === 'react-dom' ||
          id === 'react/jsx-runtime' ||
          id === 'react/jsx-dev-runtime'
        ) {
          return true;
        }
        // jQueryを外部化
        if (id === 'jquery') return true;
        return false;
      },
      input: buildConfig.entry,
      output: {
        // WP は react/jsx-runtime グローバルを持たないため、wp.element から互換オブジェクトを用意する
        banner: `window.S2JReactJSXRuntime=window.S2JReactJSXRuntime||{jsx:wp.element.createElement,jsxs:wp.element.createElement,Fragment:wp.element.Fragment};`,
        globals: (id) => {
          // WordPress Gutenbergのグローバル変数名をマッピング
          if (id.startsWith('@wordpress/')) {
            const parts = id.split('/');
            const module = parts[parts.length - 1];
            // 特別なマッピング
            if (id === '@wordpress/data') return 'wp.data';
            if (id === '@wordpress/element') return 'wp.element';
            if (id === '@wordpress/components') return 'wp.components';
            if (id === '@wordpress/i18n') return 'wp.i18n';
            if (id === '@wordpress/editor') return 'wp.editor';
            if (id === '@wordpress/edit-post') return 'wp.editPost';
            if (id === '@wordpress/plugins') return 'wp.plugins';
            if (id === '@wordpress/api-fetch') return 'wp.apiFetch';
            if (id === '@wordpress/core-data') return 'wp.coreData';
            return `wp.${module}`;
          }
          // React関連 → WordPress 同梱 React
          if (id === 'react' || id === 'react-dom') return 'wp.element';
          if (id === 'react/jsx-runtime' || id === 'react/jsx-dev-runtime') {
            return 'S2JReactJSXRuntime';
          }
          // jQuery
          if (id === 'jquery') return 'jQuery';
          return id;
        },
        assetFileNames: (assetInfo) => {
          if (assetInfo.name?.endsWith('.css')) {
            return `css/s2j-slug-generater-${buildTarget}.css`;
          }
          return 'js/[name][extname]';
        },
        chunkFileNames: 'js/[name].js',
        entryFileNames: `js/s2j-slug-generater-${buildTarget}.js`,
      },
      onwarn(warning, warn) {
        // 特定の警告を抑制
        if (warning.code === 'UNUSED_EXTERNAL_IMPORT') return;
        if (warning.code === 'MODULE_LEVEL_DIRECTIVE') return;
        if (warning.message.includes('"use client"')) return;
        if (warning.message.includes('"useTransition"')) return;
        if (warning.message.includes('"startTransition"')) return;
        if (warning.message.includes('"lazy"')) return;
        if (warning.message.includes('"Suspense"')) return;
        if (warning.message.includes('"findDOMNode"')) return;
        if (warning.message.includes('"render"')) return;
        if (warning.message.includes('"hydrate"')) return;
        if (warning.message.includes('"unmountComponentAtNode"')) return;
        
        // その他の警告は表示
        warn(warning);
      },
    },
    outDir: 'dist',
    emptyOutDir: false, // 連続ビルドのためにfalseに設定
    minify: process.env.NODE_ENV === 'production',
    sourcemap: process.env.NODE_ENV !== 'production',
    cssCodeSplit: false,
    reportCompressedSize: false, // 圧縮サイズレポートを無効化
    chunkSizeWarningLimit: 1000 // チャンクサイズ警告の閾値を設定
  },
  css: {
    preprocessorOptions: {
      scss: {
        // additionalData: `@import "${resolve(import.meta.dirname, 'src/styles/variables.scss')}";`
      },
    },
  },
  resolve: {
    alias: {
      '@': resolve(import.meta.dirname, 'src'),
    },
  },
});
