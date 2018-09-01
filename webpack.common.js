module.exports = {
  externals: {
    jquery: 'jQuery'
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'babel-loader',
        query: {
          presets: [
            [
              "@babel/preset-env",
              {
                useBuiltIns: "entry"
              }
            ]
          ]
        }
      }
    ]
  }
};
