/* global __dirname */

(function() {
  'use strict';

  var gulp = require('gulp');
  var sass = require('gulp-sass');
  var plumber = require('gulp-plumber');
  var rename = require('gulp-rename');
  var gutil = require('gulp-util');
  var sourcemaps = require('gulp-sourcemaps');
  var postcss = require('gulp-postcss');
  var autoprefixer = require('autoprefixer');
  var cssnano = require('cssnano');
  var gulpStylelint = require('gulp-stylelint');
  var livereload = require('gulp-livereload');
  var webpack = require('webpack');
  var webpackStream = require('webpack-stream');
  var webpackDevConfig = require('./webpack.dev.js');
  var webpackProdConfig = require('./webpack.prod.js');
  var named = require('vinyl-named');
  var eslint = require('gulp-eslint');

  var onError = function(err) {
    // eslint-disable-next-line no-console
    console.log('An error ocurred: ', gutil.colors.magenta(err.message));
    gutil.beep();
    this.emit('end');
  };

  function notifyLiveReload(event) {
    var fileName = require('path').relative(__dirname, event.path);
    livereload.changed(fileName);
  }

  gulp.task('js-dev', function() {
    return gulp.src(['js/src/main.js'])
      .pipe(named())
      .pipe(webpackStream(webpackDevConfig, webpack))
      .pipe(gulp.dest('./js'))
      .pipe(livereload());
  });

  gulp.task('js-prod', ['js-dev'], function() {
    return gulp.src(['js/src/main.js'])
      .pipe(named())
      .pipe(webpackStream(webpackProdConfig, webpack))
      .pipe(gulp.dest('./js'));
  });

  gulp.task('lint-js', function() {
    return gulp.src(['./js/src/**.js'])
      .pipe(plumber({errorHandler: function() { gutil.beep(); }}))
      .pipe(eslint())
      .pipe(eslint.format())
      .pipe(eslint.failAfterError())
    ;
  });

  gulp.task('sass-site-dev', ['lint-sass'], function() {
    var processors = [
      autoprefixer({browsers: ['last 2 versions']}),
    ];
    return gulp.src('./sass/style.scss')
      .pipe(plumber({errorHandler: onError}))
      .pipe(sourcemaps.init())
      .pipe(sass({ outputStyle: 'nested' }))
      .pipe(postcss(processors))
      .pipe(gulp.dest('./'))
      .pipe(sourcemaps.write())
      .pipe(rename('style-dev.css'))
      .pipe(gulp.dest('./'))
      .pipe(livereload());
  });

  gulp.task('sass-site-prod', ['lint-sass'], function() {
    var processors = [
      autoprefixer({browsers: ['last 2 versions']}),
      cssnano(),
    ];
    return gulp.src('./sass/style.scss')
      .pipe(plumber({errorHandler: onError}))
      .pipe(sourcemaps.init())
      .pipe(sass({ outputStyle: 'nested' }))
      .pipe(postcss(processors))
      .pipe(rename('style-prod.css'))
      .pipe(gulp.dest('./'));
  });


  gulp.task('sass-editor', ['lint-sass'], function() {
    var processors = [
      autoprefixer({browsers: ['last 2 versions']}),
    ];
    return gulp.src('./sass/wp-editor-style.scss')
      .pipe(plumber({errorHandler: onError}))
      .pipe(sass({ outputStyle: 'nested' }))
      .pipe(postcss(processors))
      .pipe(gulp.dest('./'));
  });

  gulp.task('sass-admin', ['lint-sass'], function() {
    var processors = [
      autoprefixer({browsers: ['last 2 versions']}),
    ];
    return gulp.src('./sass/wp-admin-style.scss')
      .pipe(plumber({errorHandler: onError}))
      .pipe(sass({ outputStyle: 'nested' }))
      .pipe(postcss(processors))
      .pipe(gulp.dest('./'));
  });

  gulp.task('lint-sass', function() {
    return gulp.src('./sass/**/*.scss')
      .pipe(gulpStylelint({
        reporters: [
          {formatter: 'string', console: true}
        ]
      }));
  });

  gulp.task('watch', ['sass', 'js'], function() {
    livereload.listen();
    gulp.watch('./sass/**/*.scss', ['sass']);
    gulp.watch('./**/*.php', notifyLiveReload);
  });

  gulp.task('sass-site', ['sass-site-dev', 'sass-site-prod']);
  gulp.task('sass', [ 'sass-site', 'sass-editor', 'sass-admin' ]);
  gulp.task('js', [ 'js-dev', 'js-prod' ]);
  gulp.task('default', ['sass', 'js']);
}());
