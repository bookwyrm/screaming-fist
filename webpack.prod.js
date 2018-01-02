const merge = require('webpack-merge')
const common = require('./webpack.common.js')
const ExtractTextPlugin = require("extract-text-webpack-plugin")
const UglifyJSPlugin = require('uglifyjs-webpack-plugin')
const autoprefixer = require('autoprefixer')
const cssnano = require('cssnano')

module.exports = merge(common, {
  plugins: [
    new ExtractTextPlugin({filename: "style-prod.css"}),
    new UglifyJSPlugin()
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
                    autoprefixer({browsers: ['last 2 versions']}),
                    cssnano()
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

