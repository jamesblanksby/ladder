'use strict';
 
/* node */
var gulp  = require('gulp');
var path  = require('path');
var color = require('cli-color');

/* gulp */
var sass   = require('gulp-sass');
var rename = require('gulp-rename');
var reload = require('gulp-livereload');
var prefix = require('gulp-autoprefixer');
var jshint = require('gulp-jshint');
var shell  = require('gulp-shell');
var map    = require('gulp-sourcemaps');

/* glob */
var glob = {
    scss: '**/scss/**/*.scss',
    script: '**/script/**/*.js'
};

/* error */
var error = function(file, line, column, reason) {
    console.log(`📄  ${color.blue(`${file}:`)} `);
    console.log(color.white(` + line ${line}, column ${column}`));
    console.log(color.white(` + ${reason}`));
};

/* task:scss */
gulp.task('scss', gulp.parallel(function(done) {
    gulp.src(glob.scss, { base: '.' })
        .pipe(map.init())
        .pipe(sass({ 
            outputStyle: 'compressed' 
        }).on('error', function(err) {
            console.log(`\n⚠️  ${color.yellow('SCSS Error:')}`);
            error(err.relativePath, err.line, err.column, err.messageOriginal);
            gulp.src(glob.scss).pipe(shell('open -a Terminal'));

            this.emit('end');
        }))
        .pipe(prefix({
            browsers: ['last 2 versions'],
            cascade: false
        }))
        .pipe(map.write())
        .pipe(rename(function(path) {
            path.dirname += '/../css';
        }))
        .pipe(gulp.dest(function(file) {
            return file.base;
        }))
        .pipe(reload());

    done();
}));

/* task:script */
gulp.task('script', gulp.parallel(function(done) {
    gulp.src(glob.script)
        .pipe(jshint())
        .pipe(jshint.reporter(function(errors) {
            console.log(`\n⚠️  ${color.yellow('Script Error:')}`);
            errors.map(function(err) {
                error(err.file, err.error.line, err.error.character, err.error.reason);
            });
            gulp.src(glob.script).pipe(shell('open -a Terminal'));
        }))
        .on('error', function(err) {
            this.emit('end');
        })
        .pipe(reload());

    done();
}));

/* task:watch */
gulp.task('watch', gulp.parallel(function(done) {
    reload.listen();

    gulp.watch(['*.html', '*.php']).on('change', reload.changed);
    gulp.watch(glob.scss).on('change', gulp.parallel(['scss']));
    gulp.watch(glob.script).on('change', gulp.parallel(['script']));

    done();
}));

/* task:default */
gulp.task('default', gulp.parallel('watch', 'scss', 'script', function() {
    console.log(`\n👂🏼  ${color.bold(`Listening for changes...`)}`);
}));
