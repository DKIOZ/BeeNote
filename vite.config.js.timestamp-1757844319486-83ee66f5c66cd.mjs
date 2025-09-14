// vite.config.js
import { defineConfig } from "file:///C:/Users/ludov/Documents/BeeNote/node_modules/vite/dist/node/index.js";
import laravel from "file:///C:/Users/ludov/Documents/BeeNote/node_modules/laravel-vite-plugin/dist/index.js";
import vue from "file:///C:/Users/ludov/Documents/BeeNote/node_modules/@vitejs/plugin-vue/dist/index.mjs";
import { VitePWA } from "file:///C:/Users/ludov/Documents/BeeNote/node_modules/vite-plugin-pwa/dist/index.js";
var vite_config_default = defineConfig({
  plugins: [
    laravel({
      input: "resources/js/app.js",
      refresh: true
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    }),
    VitePWA({
      registerType: "autoUpdate",
      includeAssets: ["favicon.ico", "apple-touch-icon.png"],
      manifest: {
        name: "BeeNote - Gestion de Ruchers",
        short_name: "BeeNote",
        description: "Application de suivi et gestion de ruchers pour apiculteurs",
        theme_color: "#f59e0b",
        background_color: "#ffffff",
        display: "standalone",
        orientation: "portrait",
        scope: "/",
        start_url: "/",
        icons: [
          {
            src: "pwa-192x192.png",
            sizes: "192x192",
            type: "image/png"
          },
          {
            src: "pwa-512x512.png",
            sizes: "512x512",
            type: "image/png"
          }
        ]
      },
      workbox: {
        globPatterns: ["**/*.{js,css,html,ico,png,svg}"]
      }
    })
  ]
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJDOlxcXFxVc2Vyc1xcXFxsdWRvdlxcXFxEb2N1bWVudHNcXFxcQmVlTm90ZVwiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9maWxlbmFtZSA9IFwiQzpcXFxcVXNlcnNcXFxcbHVkb3ZcXFxcRG9jdW1lbnRzXFxcXEJlZU5vdGVcXFxcdml0ZS5jb25maWcuanNcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfaW1wb3J0X21ldGFfdXJsID0gXCJmaWxlOi8vL0M6L1VzZXJzL2x1ZG92L0RvY3VtZW50cy9CZWVOb3RlL3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IHsgZGVmaW5lQ29uZmlnIH0gZnJvbSAndml0ZSc7XG5pbXBvcnQgbGFyYXZlbCBmcm9tICdsYXJhdmVsLXZpdGUtcGx1Z2luJztcbmltcG9ydCB2dWUgZnJvbSAnQHZpdGVqcy9wbHVnaW4tdnVlJztcbmltcG9ydCB7IFZpdGVQV0EgfSBmcm9tICd2aXRlLXBsdWdpbi1wd2EnO1xuXG5leHBvcnQgZGVmYXVsdCBkZWZpbmVDb25maWcoe1xuICAgIHBsdWdpbnM6IFtcbiAgICAgICAgbGFyYXZlbCh7XG4gICAgICAgICAgICBpbnB1dDogJ3Jlc291cmNlcy9qcy9hcHAuanMnLFxuICAgICAgICAgICAgcmVmcmVzaDogdHJ1ZSxcbiAgICAgICAgfSksXG4gICAgICAgIHZ1ZSh7XG4gICAgICAgICAgICB0ZW1wbGF0ZToge1xuICAgICAgICAgICAgICAgIHRyYW5zZm9ybUFzc2V0VXJsczoge1xuICAgICAgICAgICAgICAgICAgICBiYXNlOiBudWxsLFxuICAgICAgICAgICAgICAgICAgICBpbmNsdWRlQWJzb2x1dGU6IGZhbHNlLFxuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICB9LFxuICAgICAgICB9KSxcbiAgICAgICAgVml0ZVBXQSh7XG4gICAgICAgICAgICByZWdpc3RlclR5cGU6ICdhdXRvVXBkYXRlJyxcbiAgICAgICAgICAgIGluY2x1ZGVBc3NldHM6IFsnZmF2aWNvbi5pY28nLCAnYXBwbGUtdG91Y2gtaWNvbi5wbmcnXSxcbiAgICAgICAgICAgIG1hbmlmZXN0OiB7XG4gICAgICAgICAgICAgICAgbmFtZTogJ0JlZU5vdGUgLSBHZXN0aW9uIGRlIFJ1Y2hlcnMnLFxuICAgICAgICAgICAgICAgIHNob3J0X25hbWU6ICdCZWVOb3RlJyxcbiAgICAgICAgICAgICAgICBkZXNjcmlwdGlvbjogJ0FwcGxpY2F0aW9uIGRlIHN1aXZpIGV0IGdlc3Rpb24gZGUgcnVjaGVycyBwb3VyIGFwaWN1bHRldXJzJyxcbiAgICAgICAgICAgICAgICB0aGVtZV9jb2xvcjogJyNmNTllMGInLFxuICAgICAgICAgICAgICAgIGJhY2tncm91bmRfY29sb3I6ICcjZmZmZmZmJyxcbiAgICAgICAgICAgICAgICBkaXNwbGF5OiAnc3RhbmRhbG9uZScsXG4gICAgICAgICAgICAgICAgb3JpZW50YXRpb246ICdwb3J0cmFpdCcsXG4gICAgICAgICAgICAgICAgc2NvcGU6ICcvJyxcbiAgICAgICAgICAgICAgICBzdGFydF91cmw6ICcvJyxcbiAgICAgICAgICAgICAgICBpY29uczogW1xuICAgICAgICAgICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgICAgICAgICBzcmM6ICdwd2EtMTkyeDE5Mi5wbmcnLFxuICAgICAgICAgICAgICAgICAgICAgICAgc2l6ZXM6ICcxOTJ4MTkyJyxcbiAgICAgICAgICAgICAgICAgICAgICAgIHR5cGU6ICdpbWFnZS9wbmcnXG4gICAgICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHNyYzogJ3B3YS01MTJ4NTEyLnBuZycsXG4gICAgICAgICAgICAgICAgICAgICAgICBzaXplczogJzUxMng1MTInLFxuICAgICAgICAgICAgICAgICAgICAgICAgdHlwZTogJ2ltYWdlL3BuZydcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIF1cbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgICB3b3JrYm94OiB7XG4gICAgICAgICAgICAgICAgZ2xvYlBhdHRlcm5zOiBbJyoqLyoue2pzLGNzcyxodG1sLGljbyxwbmcsc3ZnfSddXG4gICAgICAgICAgICB9XG4gICAgICAgIH0pXG4gICAgXSxcbn0pO1xuIl0sCiAgIm1hcHBpbmdzIjogIjtBQUE0UixTQUFTLG9CQUFvQjtBQUN6VCxPQUFPLGFBQWE7QUFDcEIsT0FBTyxTQUFTO0FBQ2hCLFNBQVMsZUFBZTtBQUV4QixJQUFPLHNCQUFRLGFBQWE7QUFBQSxFQUN4QixTQUFTO0FBQUEsSUFDTCxRQUFRO0FBQUEsTUFDSixPQUFPO0FBQUEsTUFDUCxTQUFTO0FBQUEsSUFDYixDQUFDO0FBQUEsSUFDRCxJQUFJO0FBQUEsTUFDQSxVQUFVO0FBQUEsUUFDTixvQkFBb0I7QUFBQSxVQUNoQixNQUFNO0FBQUEsVUFDTixpQkFBaUI7QUFBQSxRQUNyQjtBQUFBLE1BQ0o7QUFBQSxJQUNKLENBQUM7QUFBQSxJQUNELFFBQVE7QUFBQSxNQUNKLGNBQWM7QUFBQSxNQUNkLGVBQWUsQ0FBQyxlQUFlLHNCQUFzQjtBQUFBLE1BQ3JELFVBQVU7QUFBQSxRQUNOLE1BQU07QUFBQSxRQUNOLFlBQVk7QUFBQSxRQUNaLGFBQWE7QUFBQSxRQUNiLGFBQWE7QUFBQSxRQUNiLGtCQUFrQjtBQUFBLFFBQ2xCLFNBQVM7QUFBQSxRQUNULGFBQWE7QUFBQSxRQUNiLE9BQU87QUFBQSxRQUNQLFdBQVc7QUFBQSxRQUNYLE9BQU87QUFBQSxVQUNIO0FBQUEsWUFDSSxLQUFLO0FBQUEsWUFDTCxPQUFPO0FBQUEsWUFDUCxNQUFNO0FBQUEsVUFDVjtBQUFBLFVBQ0E7QUFBQSxZQUNJLEtBQUs7QUFBQSxZQUNMLE9BQU87QUFBQSxZQUNQLE1BQU07QUFBQSxVQUNWO0FBQUEsUUFDSjtBQUFBLE1BQ0o7QUFBQSxNQUNBLFNBQVM7QUFBQSxRQUNMLGNBQWMsQ0FBQyxnQ0FBZ0M7QUFBQSxNQUNuRDtBQUFBLElBQ0osQ0FBQztBQUFBLEVBQ0w7QUFDSixDQUFDOyIsCiAgIm5hbWVzIjogW10KfQo=
