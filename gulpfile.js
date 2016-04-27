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
  mix.sass('admin/app.scss', 'public/css/admin.css');
  mix.sass('site/app.scss', 'public/css/site.css');

  mix.browserify('admin/app.js', 'public/js/admin_browserify.js');
  mix.scripts([
    '../../../node_modules/bootstrap-sass/assets/javascripts/bootstrap.js',
    '../../../node_modules/bootstrap-multiselect/dist/js/bootstrap-multiselect.js',
    '../../../node_modules/typeahead.js/dist/typeahead.bundle.js',
    '../../../node_modules/lodash/lodash.js'
  ], 'public/js/admin.js');

  mix.browserify('site/app.js', 'public/js/site_browserify.js');
  mix.scripts([
    '../../../node_modules/bootstrap-sass/assets/javascripts/bootstrap.js',
    '../../../node_modules/lodash/lodash.js',
     '../../../node_modules/eventemitter3/index.js'
  ], 'public/js/site.js');

  mix.version([
    'css/admin.css',
    'css/site.css',
    'js/admin.js',
    'js/admin_browserify.js',
    'js/site.js',
    'js/site_browserify.js'
  ]);

  mix.browserSync({
    proxy: 'stophab.y:8080'
  });
});
