const mix = require('laravel-mix');
mix.js('resources/js/app.js', 'public/js')
   .sass('resources/scss/styles.scss', 'public/css')
   .options({ processCssUrls: true })
   .webpackConfig({ watchOptions: { ignored: /node_modules/, poll: 1000 } })
   .disableNotifications()
   .version();
