const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');
require('laravel-elixir-svgstore'); // https://www.npmjs.com/package/laravel-elixir-svgstore

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application as well as publishing vendor resources.
 |
 */

/* https://laravel.com/docs/5.3/elixir */
elixir((mix) => {
    mix.copy(
        'node_modules/responsive-bp/build/responsive.min.js',
        'resources/assets/js'
    )
    .copy(
        'node_modules/responsive-bp/build/vendor/jquery-2.1.4.min.js',
        'resources/assets/js'
    );

    mix.sass('app.scss')
       .webpack('app.js');

    mix.version(['css/app.css', 'js/app.js']);

    mix.svgstore();

});
