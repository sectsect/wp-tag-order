'use strict';

/**
 * Using gulp 4 syntax
 * https://demisx.github.io/gulp4/2015/01/15/install-gulp4.html
 * npm install gulpjs/gulp.git#4.0 --save-dev
 *
 * Note: probably also make a .gitignore for the node_modules folder
 */

const gulp          = require('gulp');
const cssmin        = require('gulp-cssmin');
const buffer        = require('vinyl-buffer');
const sass          = require('gulp-sass');
const autoprefixer   = require('gulp-autoprefixer');
const uglify        = require("gulp-uglify");
const rename        = require('gulp-rename');
const mmq           = require('gulp-merge-media-queries');
const plumber       = require("gulp-plumber");
const notify        = require('gulp-notify');
const imagemin      = require('gulp-imagemin');
const pngquant      = require('imagemin-pngquant');
const csscomb       = require('gulp-csscomb');
const prettify      = require('gulp-jsbeautifier');
// For icon-font
const svgmin        = require('gulp-svgmin');
const iconfontCss   = require('gulp-iconfont-css');
const iconfont      = require('gulp-iconfont');

/*==================================================
  icon-font  @ http://qiita.com/MAL09/items/1b383fbb62e241ed6e1b
================================================== */
gulp.task('icon-font', (callback) => {
  var svgminData = gulp.src('./fonts/svg/*.svg')
  .pipe(svgmin());                  // minify the svg

  svgminData.pipe(plumber())
  .pipe(iconfontCss({                  // Create a scss file
    fontName  : 'iconfont',
    path      : './assets/sass/_icon-font-template.scss',  // Path to template file
    targetPath: '../sass/_icon-font.scss',      // Path to created scss file
    fontPath  : '../fonts/'              // Path to font file from scss
  }))
  .pipe(iconfont({                  // Create a font
    fontName        : 'iconfont',
    formats         : ['ttf', 'eot', 'woff', 'woff2', 'svg'],
    //  startCodepoint  : 0xF001,
    appendCodepoints: false
  }))
  .pipe(gulp.dest('./fonts'))          // Output the font
  .on('end', function() {
    callback();
  });
});
/*==================================================
  image minify
================================================== */
gulp.task('image-min', () => {
  return gulp.src([
    './images/**/*.+(jpg|jpeg|png|gif|svg)',
    '!./images/sprites/*.+(jpg|jpeg|png|gif|svg)',
    '!./images/svg/*.+(jpg|jpeg|png|gif|svg)'
  ])
  .pipe(imagemin({
    progressive: true,
    svgoPlugins: [{removeViewBox: false}],
    use: [pngquant()]
  }))
  .pipe(gulp.dest("./assets/images"));
});
/*==================================================
  JS format
================================================== */
gulp.task('format-js', () => {
  return gulp.src([
    "./js/**/*.js",
    "!./js/**/*.min.js",
    "!./js/vendor/*.js"
  ])
  .pipe(plumber())
  .pipe(prettify({mode: 'VERIFY_AND_WRITE', indentWithTabs: true, maxPreserveNewlines: 1}))
  .pipe(gulp.dest('./js'));
});
/*==================================================
  JS minify
================================================== */
gulp.task('minify-js', () => {
  return gulp.src([
    "./js/**/*.js",
    "!./js/**/*.min.js",
    "!./js/vendor/*.js"
  ])
  .pipe(plumber())
  .pipe(uglify())
  .pipe(rename({suffix: '.min'}))
  .pipe(gulp.dest('./js'));
});
/*==================================================
  sass
================================================== */
gulp.task('sass', () => {
  return gulp.src("./sass/**/*.scss")
  .pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
  .pipe(sass({outputStyle: 'compressed'}))
  .pipe(csscomb())
  .pipe(autoprefixer({
    cascade: false
  }))
  .pipe(mmq())
  .pipe(gulp.dest("./css"))
  .pipe(cssmin())
  .pipe(gulp.dest("./css"));
});

/*==================================================
  watch
================================================== */
gulp.task('watch', () => {
  gulp.watch("sass/**/*.scss", gulp.parallel('sass'));
});
/*==================================================
  build
================================================== */
gulp.task(
  'build',
  gulp.series(
    gulp.parallel('sass'),
    'watch'
  )
);
/*==================================================
  default
================================================== */
gulp.task('default', gulp.series('build'));
