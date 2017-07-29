var gulp = require('gulp');
var concat = require('gulp-concat');
var concat_json = require("gulp-concat-json");
var extend = require('gulp-extend');
var inject = require('gulp-inject');
var gfi = require("gulp-file-insert");
var rename = require("gulp-rename");
var templateCache = require('gulp-angular-templatecache');
var uglify = require('gulp-uglify');
var uglifyJs = require('gulp-uglifyjs');
var stripNgLog = require('gulp-strip-ng-log');
var angularFilesort = require('gulp-angular-filesort');
var ngAnnotate = require('gulp-ng-annotate');
var plumber = require('gulp-plumber');
var concatCss = require('gulp-concat-css');
var sass = require('gulp-sass');
var minifyCss = require('gulp-minify-css');

// configuration
var conf = {
    i18nFilesEn: ['Resources/public/SPA/modules/**/en.json'],
    i18nFilesDe: ['Resources/public/SPA/modules/**/de.json'],
    i18nDistDir: 'Resources/public/SPA/modules/app/i18n',
    i18nJsTemplateFile: 'Resources/public/SPA/modules/app/module.js.template',
    i18nJsTargetFile: 'Resources/public/SPA/modules/app/module.js',
    templateFiles: ['Resources/public/SPA/modules/**/*.html'],
    templateModuleName: 'app.app.templates',
    templateRoot: '/bundles/brickscustomtwentystepsalexa/SPA/modules/',
    templateDistDir: 'Resources/public/SPA/modules/app/templates/',
    templateDistFile: 'templates.js',
    jsDistDir: 'Resources/public/SPA/dist/',
    jsFiles: [
        'Resources/public/SPA/modules/**/*.js'
    ],
    jsFilesWatch: [
        'Resources/public/SPA/modules/**/*.js',
        'Resources/public/SPA/modules/app/module.js.template'
    ],
    sass: [
        'Resources/public/SPA/modules/**/*.scss'
    ],
    sassFilesWatch: [
        'Resources/public/SPA/modules/**/*.scss'
    ],
};

// default task
gulp.task('default', ['app']);

gulp.task('app', ['i18n','template_cache','app-js', 'sass_prod', 'sass_dev']);

gulp.task('prod', ['i18n','template_cache','app-js','app-prod-js', 'sass_prod', 'sass_dev']);

// translation building and injection
gulp.task('i18n-de', function () {

    return gulp.src(conf.i18nFilesDe)
        .pipe(plumber())
        .pipe(extend("de_dist.json"))
        .pipe(gulp.dest(conf.i18nDistDir));
});

gulp.task('i18n-en', function () {

    return gulp.src(conf.i18nFilesEn)
        .pipe(plumber())
        .pipe(extend("en_dist.json"))
        .pipe(gulp.dest(conf.i18nDistDir));
});

gulp.task('i18n', ['i18n-de', 'i18n-en'], function () {

    return gulp.src(conf.i18nJsTemplateFile)
        .pipe(plumber())
        .pipe(gfi({
            "/* de_dist.json */": "Resources/public/SPA/modules/app/i18n/de_dist.json",
            "/* en_dist.json */": "Resources/public/SPA/modules/app/i18n/en_dist.json"
        }))
        .pipe(rename("module.js"))
        .pipe(gulp.dest('Resources/public/SPA/modules/app/'));
});

// compile modules/**/.sass to .css
gulp.task('sass_prod', function(done) {
        gulp.src(conf.sass)
            .pipe(sass({
                errLogToConsole: true
            }))
            .pipe(concatCss("spa-scss.prod",{rebaseUrls:false}))
            .pipe(minifyCss({
                keepSpecialComments: 0
            }))
            .pipe(rename({ extname: '.min.css' }))
            .pipe(gulp.dest('Resources/public/SPA/dist/'))
            .on('end', done);
});

gulp.task('sass_dev', function(done) {
        gulp.src(conf.sass)
            .pipe(sass({
                errLogToConsole: true
            }))
            .pipe(concatCss("spa-scss.dev",{rebaseUrls:false}))
            .pipe(rename({ extname: '.css' }))
            .pipe(gulp.dest('Resources/public/SPA/dist/'))
            .on('end', done);
});

// template caching
gulp.task('template_cache', function () {
    return gulp.src(conf.templateFiles)
        .pipe(plumber())
        .pipe(templateCache(conf.templateDistFile,{
            'root': conf.templateRoot,
            'module': conf.templateModuleName
        }))
        .pipe(gulp.dest(conf.templateDistDir));
});


gulp.task('app-js', function () {
    console.log('building app.js');
    return build('app.js', conf.jsFiles, false);
});

gulp.task('app-prod-js', function () {
    console.log('building app.prod.js');
    return build('app.prod.js', conf.jsFiles, true);
});

// watching
gulp.task('watch', function() {
    gulp.watch(conf.i18nFilesDe.concat([conf.i18nJsTemplateFile]),['i18n']);
    gulp.watch(conf.i18nFilesEn.concat([conf.i18nJsTemplateFile]),['i18n']);
    gulp.watch(conf.templateFiles,['template_cache']);
    gulp.watch(conf.jsFilesWatch,['app-js']);
    gulp.watch(conf.sassFilesWatch,['sass_dev']);
    gulp.watch(conf.sassFilesWatch,['sass_prod']);
});

// helper functions
function build(fileName, files, prod) {
    prod = prod || false;

    if (prod) {
        return gulp.src(files)
            .pipe(plumber())
            .pipe(stripNgLog())
            .pipe(angularFilesort())
            .pipe(concat(fileName))
            .pipe(rename(function (path) {
                if (path.extname === '.js') {
                    path.basename += '.min';
                }
            }))
            .pipe(ngAnnotate({add: true}))
            .pipe(uglify({mangle: false}))
            .pipe(gulp.dest(conf.jsDistDir));
    }

    return gulp.src(files)
        .pipe(plumber())
        .pipe(angularFilesort())
        .pipe(concat(fileName))
        .pipe(gulp.dest(conf.jsDistDir));

}
