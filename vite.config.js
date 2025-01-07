import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            // Define the entry points for your application.
            // These files will be processed by Vite.
            input: [
                'resources/sass/app.scss',
                'resources/js/app.ts',
            ],
            // Refresh the page when these files change.
            // Useful for Blade templates, routes, and config files.
            refresh: [
                'app/Http/**',
                'routes/**',
                'resources/views/**',
                'config/**',
            ],
        }),
        vue({
            // Enable Vue 3 SFC (Single File Component) support.
            // This allows Vite to compile .vue files.
            template: {
                transformAssetUrls: {
                    // The Vue plugin will re-write asset URLs, but it will
                    // only do this for assets that are referenced in a component's
                    // <template> block. By default, we are not rewriting assets
                    // in <style> blocks, but you may configure this to suit your needs.
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            // Define aliases for common directories to simplify imports.
            // This helps in maintaining a cleaner and more readable codebase.
            '@': path.resolve(__dirname, 'resources/js'),
            '@admin': path.resolve(__dirname, 'resources/js/admin'),
            '@shop': path.resolve(__dirname, 'resources/js/shop'),
            '@components': path.resolve(__dirname, 'resources/js/components'),
            '@shared': path.resolve(__dirname, 'resources/js/components/shared'),
            // Example for referencing a specific package or module if needed
            // 'vue': 'vue/dist/vue.esm-bundler.js',
        },
    },
    // Configure the development server.
    server: {
        // Specify the host to listen on. '0.0.0.0' makes it accessible from network.
        host: '0.0.0.0',
        // Specify the port for the Vite development server.
        port: 5173,
        // Enable HMR (Hot Module Replacement) for faster development.
        hmr: {
            // Use a specific host for HMR if running inside a Docker container
            // or if the client needs to connect to a different host than the server.
            host: 'localhost', // Or process.env.VITE_HMR_HOST if dynamic
        },
        // Watch options for file changes.
        watch: {
            usePolling: true, // Recommended for Docker environments
        },
    },
    // Build options for production.
    build: {
        // Output directory for the build artifacts.
        outDir: 'public/build',
        // Empty the output directory before building.
        emptyOutDir: true,
        // Generate sourcemaps for easier debugging in production.
        sourcemap: true,
        rollupOptions: {
            // Configure Rollup specific options if needed.
            // For example, to manually define entry points or externalize dependencies.
        },
    },
});