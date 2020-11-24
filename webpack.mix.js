let mix = require('laravel-mix');

// 🎚️ Base config
const config = {
    path: './src/assetbundles/',
}

// Front-end assets
mix.js(config.path + 'src/js/silo.js', config.path + 'dist/silo.js')
    .sass(config.path + 'src/scss/silo.scss', config.path + 'dist/')
    .copy(config.path + 'src/fonts/*', config.path + 'dist/')
    .options({ processCssUrls: false })

//.extract(['@uppy/core','@uppy/xhr-upload','@uppy/dashboard'])