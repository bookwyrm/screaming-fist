const merge = require('webpack-merge')
const common = require('./webpack.common.js')
const MiniCssExtractPlugin = require("mini-css-extract-plugin")
const ExtraneousFileCleanupPlugin = require('webpack-extraneous-file-cleanup-plugin');
const autoprefixer = require('autoprefixer')

module.exports = merge(common, {
  mode: 'development',
  devtool: 'inline-source-map',
  plugins: [
    new MiniCssExtractPlugin({filename: "style-dev.css"}),
    new ExtraneousFileCleanupPlugin({
      extensions: [ '.js' ],
      minBytes: 8000
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
        use: [
          MiniCssExtractPlugin.loader,
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
        ]
      }
    ]
  }
});
