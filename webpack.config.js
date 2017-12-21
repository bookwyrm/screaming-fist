const path = require('path')
const ExtractTextPlugin = require("extract-text-webpack-plugin")

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
  devtool: "source-map", // any "source-map"-like devtool is possible
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
    extractSass
  ]
};
