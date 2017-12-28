const path = require('path')
const ExtractTextPlugin = require("extract-text-webpack-plugin")
const CleanWebpackPlugin = require('clean-webpack-plugin')
const autoprefixer = require('autoprefixer')

const extractSass = new ExtractTextPlugin({
  filename: "[name].[contenthash].css"
})

module.exports = {
  entry: [
    './src/js/index.js',
    './sass/style.scss'
  ],
  output: {
    filename: 'bundle.js',
    path: path.resolve(__dirname, 'dist')
  },
  devtool: "inline-source-map", // any "source-map"-like devtool is possible
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
        use: extractSass.extract({
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
  },
  plugins: [
    new CleanWebpackPlugin(['dist']),
    extractSass
  ]
};
