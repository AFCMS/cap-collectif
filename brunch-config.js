exports.config = {
  overrides: {
    production: {
      plugins: {
        off: ['eslint-brunch'],
      },
      optimize: true,
      sourceMaps: true,
    },
  },
  npm: {
    enabled: true,
    styles: {
      quill: ['dist/quill.base.css', 'dist/quill.snow.css'],
      'react-select': ['dist/react-select.css'],
      'react-toggle': ['style.css'],
    },
  },
  paths: {
    watched: ['app/Resources'],
    public: 'web',
  },
  conventions: {
    assets: /^app\/Resources\/assets/,
    ignored: [/[\\/]_/, 'app/Resources/js-server/registration.js', /-test.js$/],
  },
  files: {
    javascripts: {
      joinTo: {
        'js/vendor.js': [
          'bower_components/jquery/dist/jquery.js',
          'bower_components/Readmore.js/readmore.min.js',
          'bower_components/ckeditor/ckeditor.js',

          // Bootstrap js modules
          'bower_components/bootstrap-sass/assets/javascripts/bootstrap/affix.js',
          'bower_components/bootstrap-sass/assets/javascripts/bootstrap/alert.js',
          'bower_components/bootstrap-sass/assets/javascripts/bootstrap/button.js',
          'bower_components/bootstrap-sass/assets/javascripts/bootstrap/carousel.js',
          'bower_components/bootstrap-sass/assets/javascripts/bootstrap/collapse.js',
          'bower_components/bootstrap-sass/assets/javascripts/bootstrap/modal.js',
          'bower_components/bootstrap-sass/assets/javascripts/bootstrap/tooltip.js',
          'bower_components/bootstrap-sass/assets/javascripts/bootstrap/popover.js',
          'bower_components/bootstrap-sass/assets/javascripts/bootstrap/tab.js',
          'bower_components/bootstrap-sass/assets/javascripts/bootstrap/transition.js',

          // safari polyfills
          'bower_components/es6-promise/promise.js',
          'bower_components/intl/Intl.js',
          'bower_components/intl/locale-data/jsonp/fr.js',
          // end

          /^node_modules/,

          'bower_components/fetch/fetch.js',

          'app/Resources/js/jsapi.js',
          'app/Resources/js/googleCharts.js',

          'app/Resources/js/cookiechoices.js',
          'app/Resources/js/browserUpdate.js',
          'app/Resources/js/modernizr.js',
        ],
        'js/app.js': [
          'app/Resources/js/app.js',
          'app/Resources/js/**/**/*.json',
          'app/Resources/js/**/**/*.js',
        ],
        'js/ckeditor/ckeditor.js': ['bower_components/ckeditor/ckeditor.js'],
        'js/jquery.minicolors.js': [
          'bower_components/jquery-minicolors/jquery.minicolors.js',
        ],
      },
    },
    stylesheets: {
      joinTo: {
        'css/style.css': [
          'bower_components/bootstrap/assets/stylesheets/bootstrap.scss',
          'bower_components/css-spinners/css/spinners.css',
          /^node_modules/,
          'app/Resources/scss/style.scss',
        ],
        'css/jquery.minicolors.css': [
          'bower_components/jquery-minicolors/jquery.minicolors.css',
        ],
        'css/style-admin.css': [
          'app/Resources/scss/style-admin.scss',
          'bower_components/css-spinners/css/spinners.css',
          /^node_modules/,
        ],
      },
    },
  },
  plugins: {
    babel: {
      babelrc: true,
    },
    sass: {
      mode: 'native',
    },
    cleancss: {
      keepSpecialComments: 0,
      removeEmpty: true,
    },
    uglify: {
      mangle: true,
      compress: false,
    },
  },
};
