const merge = require('webpack-merge')
const common = require('./webpack.common.js')
const ExtractTextPlugin = require("extract-text-webpack-plugin")
const ExtraneousFileCleanupPlugin = require('webpack-extraneous-file-cleanup-plugin');
const autoprefixer = require('autoprefixer')

module.exports = merge(common, {
  devtool: 'source-map',
  plugins: [
    new ExtractTextPlugin({filename: "style-dev.css"}),
    new ExtraneousFileCleanupPlugin({
      extensions: [ '.js', '.js.map' ],
      minBytes: 3000
    })
  ],
  module: {
    rules: [
       {
        test: /\.css$/,
        use: [
          'style-loader',
          'css-loader'
        ]
      },
      {
        test: /\.scss$/,
        use: ExtractTextPlugin.extract({
          use: [
            {
              // translates CSS into CommonJS
              loader: "css-loader", options: {
                sourceMap: true
              }
            },
            {
              loader: 'postcss-loader', options: {
                sourceMap: true,
                plugins () {
                  return [
                    autoprefixer({browsers: ['last 2 versions']})
                  ]
                }
              }
            },
            {
              // compiles Sass to CSS
              loader: "sass-loader", options: {
                sourceMap: true
              }
            }
          ],
          fallback: "style-loader"
        })
      }
    ]
  }
});
