const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const WebpackAssetsManifest = require('webpack-assets-manifest');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
    entry: {
        app: ['./ui/sass/app.scss'],
    },
    output: {
        path: path.resolve(__dirname, 'public/build'),
        publicPath: '/build/',
        filename: '[name].[contenthash].js',
        chunkFilename: '[name].[contenthash].js',
        crossOriginLoading: 'anonymous',
    },
    stats: {
        children: false,
        modulesSpace: 0,
    },
    plugins: [
        new CleanWebpackPlugin(),
        new WebpackAssetsManifest({
            publicPath: true,
            output: 'manifest.json',
        }),
        new WebpackAssetsManifest({
            customize(entry, original, manifest, asset) {
                return {
                    key: entry.key,
                    value: asset.info.integrity,
                };
            },
            integrity: true,
            integrityHashes: ['sha512'],
            output: 'integrity.json',
        }),
        new MiniCssExtractPlugin({
            filename: '[name].[contenthash].css',
        }),
    ],
    resolve: {
        extensions: ['.scss'],
        alias: {
            Sass: path.resolve(__dirname, 'ui/sass'),
        },
        fallback: {
            fs: false,
        },
    },
    module: {
        rules: [
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'sass-loader',
                    {
                        loader: 'sass-loader',
                        options: {
                            webpackImporter: false,
                            sassOptions: {
                                includePaths: ['./ui/sass']
                            },
                        },
                    },
                ],
            },
        ],
    },
    optimization: {
        splitChunks: false,
    },
};
