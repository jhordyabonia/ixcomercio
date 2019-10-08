const
  gulp = require('gulp'),
  plumber = require('gulp-plumber'),
  notify = require('gulp-notify'),
  glob = require('gulp-sass-glob'),
  sass = require('gulp-sass'),
  uglify = require('gulp-uglify'),
  babel = require('gulp-babel'),
  concat = require('gulp-concat'),
  stylish = require('jshint-stylish'),
  imagemin = require('gulp-imagemin'),
  postcss = require('gulp-postcss'),
  rename = require("gulp-rename"),
  autoprefixer = require('autoprefixer');

var thisWatchScssFiles = 'src/scss/**/*.scss';
var thisWatchJsFiles = 'src/js/main.js';

/*SASS TASK*/
gulp.task('patterns', function () {
  return gulp.src(thisWatchScssFiles)
  .pipe(glob())
  .pipe(plumber({
    errorHandler: function (error) {
      notify.onError({
        title: "Gulp",
        subtitle: "Falló!",
        message: "Error: <%= error.message %>",
        sound: "Beep"
      })(error);
      this.emit('end');
    }
  }))
  .pipe(sass({ outputStyle: 'compressed' }))
  .pipe(postcss([autoprefixer()]))

  .pipe(rename({ suffix: '.min' }))
  .pipe(gulp.dest('css'))
});

/*JS TASK*/
gulp.task('uglify', function() {
  return gulp.src('src/js/*.js')
    .pipe(uglify('main.js'))
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('js'))
});

/* SASS LINT TASK */
gulp.task('sass_lint', function lintCssTask() {
  const gulpStylelint = require('gulp-stylelint');
  return gulp
    .src(thisWatchScssFiles)
    .pipe(gulpStylelint({
      reporters: [
        { formatter: 'string', console: true }
      ]
    }));
});

gulp.task('default', gulp.parallel('patterns','uglify','sass_lint'), function() {
  gulp.watch(thisWatchScssFiles, ['patterns']); // Reload on SCSS file changes.
  gulp.watch(thisWatchJsFiles, ['uglify']); // Reload on JS file changes.
});
