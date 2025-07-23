const mix = require('laravel-mix');

mix.webpackConfig({
  cache: false,  // ปิด cache
  // หรือ
  // cache: {
  //   type: 'memory' // เก็บ cache ใน memory เท่านั้น
  // }
});

// ตัวอย่าง Laravel Mix config
mix.js('resources/js/app.js', 'public/js')
   .postCss('resources/css/app.css', 'public/css', [
      require('tailwindcss'),
   ])
   .sourceMaps();  // เปิด source maps ใน dev

if (!mix.inProduction()) {
  mix.webpackConfig({
    cache: false
  });
}
