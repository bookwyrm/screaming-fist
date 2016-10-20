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

  var onError = function(err) {
    // eslint-disable-next-line no-console
    console.log('An error ocurred: ', gutil.colors.magenta(err.message));
    gutil.beep();
    this.emit('end');
  }

  gulp.task('sass-site-dev', function() {
    var processors = [
      autoprefixer({browsers: ['last 2 versions']}),
    ];
    return gulp.src('./sass/style.scss')
    .pipe(plumber({errorHandler: onError}))
    .pipe(sourcemaps.init())
    .pipe(sass({ outputStyle: 'nested' }))
    .pipe(postcss(processors))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest('./'))
    .pipe(rename("style-dev.css"))
    .pipe(gulp.dest('./'))
  });

  gulp.task('sass-site-prod', function() {
    var processors = [
      autoprefixer({browsers: ['last 2 versions']}),
      cssnano(),
    ];
    return gulp.src('./sass/style.scss')
    .pipe(plumber({errorHandler: onError}))
    .pipe(sourcemaps.init())
    .pipe(sass({ outputStyle: 'nested' }))
    .pipe(postcss(processors))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest('./'))
    .pipe(rename("style-prod.css"))
    .pipe(gulp.dest('./'))
  });


  gulp.task('sass-editor', function() {
    return gulp.src('./sass/wp-editor-style.scss')
    .pipe(plumber({errorHandler: onError}))
    .pipe(sass({ outputStyle: 'nested' }))
    .pipe(gulp.dest('./'))
  });

  gulp.task('sass-admin', function() {
    return gulp.src('./sass/wp-admin-style.scss')
    .pipe(plumber({errorHandler: onError}))
    .pipe(sass({ outputStyle: 'nested' }))
    .pipe(gulp.dest('./'))
  });

  gulp.task('sass-site', ['sass-site-dev', 'sass-site-prod']);
  gulp.task('default', ['sass-site']);
}());
