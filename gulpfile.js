'use strict';
 
var gulp     = require('gulp');
 
var notifier = require('node-notifier');
var sass     = require('gulp-sass');
var prefix   = require('gulp-autoprefixer');
var csscomb  = require('gulp-csscomb');
var jshint   = require('gulp-jshint');
var reload   = require('gulp-livereload');
var shell    = require('gulp-shell');

var path = {
	css: 'src/css',
	scss: 'src/scss/',
	script: 'src/script/'
};
 
var error_notify = function(error) {
    error.message = error.message.replace('\n', '');
    notifier.notify({
        title: error.name + ': ' + error.plugin,
        message: error.message,
        icon: 'http://static.blanks.by/HlYhKOWvuB.png'
    });
}
 
gulp.task('scss', function() {
    gulp.src(path.scss + '*.scss')
        .pipe(sass({
            outputStyle: 'expanded'
        }))
        .on('error', error_notify)
        .pipe(prefix('last 2 version'))
        .pipe(csscomb())
        .pipe(gulp.dest(path.css))
        .pipe(reload());
});
 
gulp.task('script', function() {
    var error_count = 0;
    gulp.src(path.script + '*.js')
        .pipe(jshint())
        .pipe(jshint.reporter(function(errors) {
            console.log('\nSyntax issues:');
            errors.forEach(function(file) {
                var error = file.error;
                console.log(' + ' + file.file.split('/').pop() + ': line ' + error.line + ', col ' + error.character + ', ' + error.reason);
                error_count++;
            });
            console.log('');
            gulp.src(path.script + '*.js').pipe(shell('open -a Terminal'));
        }))
        .pipe(jshint.reporter('fail'))
        .on('error', error_notify);
});
 
gulp.task('watch', function() {
    reload.listen();
    gulp.watch(['*.html', '*.php']).on('change', reload.changed);
 
    gulp.watch(path.scss + '*.scss', ['scss']);
    gulp.watch(path.script + '*.js', ['script']);
});
 
gulp.task('default', [
    'scss',
    'script',
    'watch'
]);
