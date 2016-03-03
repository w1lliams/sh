var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
  mix.sass('app.scss');
  mix.sass('admin/app.scss', 'public/css/admin.css');
  mix.browserify('admin/app.js', 'public/js/admin_browserify.js');
  mix.scripts([
    '../../../node_modules/bootstrap-sass/assets/javascripts/bootstrap.js',
    '../../../node_modules/bootstrap-multiselect/dist/js/bootstrap-multiselect.js',
    '../../../node_modules/lodash/lodash.js'
  ], 'public/js/admin.js');
  mix.version(['css/admin.css', 'css/app.css', 'js/admin.js']);

  mix.browserSync({
    proxy: 'stophab.y'
  });
});
