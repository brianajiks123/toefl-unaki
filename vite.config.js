import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
  plugins: [
    laravel({
      input: [
        "resources/css/app.css",
        "resources/css/admin_lecturer/style.css",
        "resources/css/auth/style.css",
        "resources/css/student/style.css",
        "resources/css/student/style_exams.css",
        "resources/sass/app.scss",
        "resources/js/admin_lecturer/template.js",
        "resources/js/admin_lecturer/batches.js",
        "resources/js/admin_lecturer/users.js",
        "resources/js/admin_lecturer/settings.js",
        "resources/js/admin_lecturer/listen_quest.js",
        "resources/js/admin_lecturer/swe_quest.js",
        "resources/js/admin_lecturer/reading_quest.js",
        "resources/js/admin_lecturer/exams.js",
        "resources/js/admin_lecturer/exam_results.js",
        "resources/js/auth/template.js",
        "resources/js/student/template.js",
        "resources/js/student/template_exams.js",
        "resources/js/student/listening_exam.js",
        "resources/js/student/swe_exam.js",
        "resources/js/student/settings.js",
      ],
      refresh: true,
    }),
  ],
  resolve: {
    alias: {
      "@": "/resources",
      $: "jQuery",
    },
  },
});
