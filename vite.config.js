import { defineConfig } from "vite";
import laravel, { refreshPaths } from "laravel-vite-plugin";
import tailwindcss from "tailwindcss";

export default defineConfig({
    server: {
        host: true,
    },
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/filament/user/theme.css",
            ],
            refresh: [...refreshPaths, "app/Livewire/**"],
        }),
        tailwindcss(),
    ],
});
