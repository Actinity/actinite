const mix = require('laravel-mix');

mix.setPublicPath('./dist')

mix.js('resources/js/app.js', 'dist').vue()
    .sass('resources/sass/app.scss', 'dist',{
	    implementation: require('sass')
    })
    .version();
