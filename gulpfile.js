let gulp = require('gulp');
let cleanCSS = require('gulp-clean-css');
let uglify = require('gulp-uglify');
let rename = require('gulp-rename');

gulp.task('css', () => gulp.src('./assets/css/*.css')
    .pipe(cleanCSS())
    .pipe(rename(path => {
        path.basename += '.min';
    }))
    .pipe(gulp.dest('./assets/css')));
gulp.task('js', () => gulp.src('./assets/js/*.js')
    .pipe(uglify())
    .pipe(rename(path => {
        path.basename += '.min';
    }))
    .pipe(gulp.dest('./assets/js')));
gulp.task('default', ['css', 'js']);