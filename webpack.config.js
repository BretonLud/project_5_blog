var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .copyFiles({
        from: './assets/styles/images',
        // optional target path, relative to the output dir
        to: 'images/[path][name].[ext]',
        // if versioning is enabled, add the file hash too
        //to: 'images/[path][name].[hash:8].[ext]',
        // only copy files matching this pattern
        //pattern: /\.(png|jpg|jpeg)$/
    })

    .addEntry('app', './assets/app.js')
    .addEntry('blog', './assets/blog.js')

    .cleanupOutputBeforeBuild()

    .enableSassLoader()
    .enableSourceMaps(!Encore.isProduction())
    .enableSingleRuntimeChunk()

    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();