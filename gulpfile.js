(function() {
  'use strict';

  var gulp = require('gulp');
  var sass = require('gulp-sass');
  var plumber = require('gulp-plumber');
  var rename = require('gulp-rename');
  var gutil = require('gulp-util');
  var sourcemaps = require('gulp-sourcemaps');

  var onError = function(err) {
    // eslint-disable-next-line no-console
    console.log('An error ocurred: ', gutil.colors.magenta(err.message));
    gutil.beep();
    this.emit('end');
  }

  gulp.task('sass', function() {
    return gulp.src('./sass/style.scss')
    .pipe(plumber({errorHandler: onError}))
    .pipe(sourcemaps.init())
    .pipe(sass({ outputStyle: 'nested' }))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest('./'))
    .pipe(rename("style-dev.css"))
    .pipe(gulp.dest('./'))
  });

  gulp.task('default', ['sass']);
}());
