var gulp			     = require('gulp');
var cssmin			     = require('gulp-cssmin');
var buffer               = require('vinyl-buffer');
var sass			     = require('gulp-sass');
var autoprefixer	     = require('gulp-autoprefixer');
var mmq                  = require('gulp-merge-media-queries');
var plumber			     = require("gulp-plumber");
var notify			     = require('gulp-notify');
var imagemin		     = require('gulp-imagemin');
var pngquant		     = require('imagemin-pngquant');
var csscomb			     = require('gulp-csscomb');
var prettify		     = require('gulp-jsbeautifier');
var svgmin               = require('gulp-svgmin');
var iconfontCss          = require('gulp-iconfont-css');
var iconfont             = require('gulp-iconfont');

/*==================================================
	icon-font	@ http://qiita.com/MAL09/items/1b383fbb62e241ed6e1b
================================================== */
gulp.task('icon-font', function (callback) {
	var svgminData = gulp.src('./fonts/svg/*.svg')
	.pipe(svgmin());									// minify the svg

	svgminData.pipe(plumber())
	.pipe(iconfontCss({									// Create a scss file
		fontName  : 'iconfont',
		path      : './sass/_icon-font-template.scss',	// Path to template file
		targetPath: '../sass/_icon-font.scss',			// Path to created scss file
		fontPath  : '../fonts/'							// Path to font file from scss
	}))
	.pipe(iconfont({									// Create a font
		fontName        : 'iconfont',
		formats         : ['ttf', 'eot', 'woff', 'woff2', 'svg'],
		appendCodepoints:false
	}))
	.pipe(gulp.dest('./fonts'))							// Output the font
	.on('end', function(){
		callback();
	});
});
/*==================================================
	image minify
================================================== */
gulp.task('image-min', function () {
	gulp.src(['./images/*.+(jpg|jpeg|png|gif|svg)', '!./images/sprites/*.+(jpg|jpeg|png|gif|svg)'])
//	.pipe(imagemin())
	.pipe(imagemin({
		progressive: true,
		svgoPlugins: [{removeViewBox: false}],
		use: [pngquant()]
	}))
	.pipe(gulp.dest("./images"));
});
/*==================================================
	JS format
================================================== */
gulp.task('format-js', function() {
	gulp.src(["./js/**/*.js", "!./js/**/*.min.js"], "!./js/vendor/*.js")
	.pipe(plumber())
	.pipe(prettify({mode: 'VERIFY_AND_WRITE', indentWithTabs: true, maxPreserveNewlines: 1}))
	.pipe(gulp.dest('./js'));
});
/*==================================================
	sass
================================================== */
gulp.task("sass", function() {
	gulp.src("./sass/*scss")
	.pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
	.pipe(sass({outputStyle: 'compressed'}))
	.pipe(csscomb())
	.pipe(autoprefixer({
        browsers: ["last 2 versions", "ie >= 9", "Android >= 4","ios_saf >= 8"],	// https://github.com/ai/browserslist/blob/master/README.md
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
gulp.task("default", ['sass'], function() {
	gulp.watch("sass/*.scss",["sass"]);
});
