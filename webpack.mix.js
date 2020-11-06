let mix = require('laravel-mix');

// 🎚️ Base config
const config = {
    path: './src/assetbundles/',
}

// Front-end assets
mix.js(config.path + 'src/js/dam.js', config.path + 'dist/dam.js').extract(['@uppy/core','@uppy/xhr-upload','@uppy/dashboard'])
    .sass(config.path + 'src/scss/dam.scss', config.path + 'dist/')
    .copy(config.path + 'src/fonts/*', config.path + 'dist/')
    .options({ processCssUrls: false })
