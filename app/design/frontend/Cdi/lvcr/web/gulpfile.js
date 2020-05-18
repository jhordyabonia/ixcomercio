let
  gulp = require('gulp'),
  sass = require('gulp-sass'),
  sourcemaps = require('gulp-sourcemaps'),
  cleanCss = require('gulp-clean-css'),
  rename = require('gulp-rename'),
  postcss = require('gulp-postcss'),
  autoprefixer = require('autoprefixer'),
  notify = require('gulp-notify'),
  browserSync = require('browser-sync').create();

const paths = {
  scss: {
    src: 'src/scss/main.scss',
    watch: 'src/scss/**/*.scss',
    dest: 'css'
  },
  js: {
    src: 'src/js/main.js',
    watch: 'src/js/*.js',
    dest: 'js'
  }
}


// Compile sass into CSS & auto-inject into browsers
function styles () {
  return gulp.src([paths.scss.src])
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss([autoprefixer({
      browsers: [
        'Chrome >= 35',
        'Firefox >= 38',
        'Edge >= 12',
        'Explorer >= 10',
        'iOS >= 8',
        'Safari >= 8',
        'Android 2.3',
        'Android >= 4',
        'Opera >= 12']
    })]))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(cleanCss())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(browserSync.stream())
}


// Move the javascript files into our js folder
function js () {
  return gulp.src([paths.js.src])
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.js.dest))
    .pipe(browserSync.stream())
}


// Static Server + watching scss/html files
function serve () {
  browserSync.init()
  gulp.watch([paths.scss.watch], styles).on('change', browserSync.reload)
  gulp.watch([paths.js.watch], js).on('change', browserSync.reload)
}


const build = gulp.series(styles, gulp.parallel(js, serve))

exports.styles = styles
exports.js = js
exports.serve = serve

exports.default = build
