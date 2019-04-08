let mix = require('laravel-mix');

mix
    .js('src/main.js', 'resources/ccc.js')
    .sass('src/main.scss', 'resources/ccc.css');
