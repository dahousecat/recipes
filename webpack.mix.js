const { mix } = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix

    // .combine([
    //     'resources/assets/js/jquery-2.1.4.min.js',
    //     'resources/assets/js/responsive.min.js',
    //     'resources/assets/js/app.js',
    // ], 'public/js/app.js')

    // .js('resources/assets/js/responsive.min.js', 'public/js')
    .js('resources/assets/js/app.js', 'public/js')
    // .js('resources/assets/js/jquery-2.1.4.min.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .version();
