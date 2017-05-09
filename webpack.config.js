const ExtractTextPlugin = require("extract-text-webpack-plugin");
const path = require('path');

const extractSass = new ExtractTextPlugin({
  filename: '[name].css',
  allChunks: true
});

module.exports = [
  {
    name: 'css',
    entry: {
      cmsbundle: './scss/main.scss',
    },
    output: {
      path: path.resolve(__dirname, 'dist/css'),
      filename: '[name].css',
    },
    module: {
      rules: [{
        test: /\.scss$/,
        use: extractSass.extract({
          use: [{
            loader: 'css-loader?discardComments'
          }, {
            loader: 'sass-loader'
          }],
          fallback: 'style-loader'
        })
      }, {
        test: /\.(jpg|gif|png|svg)$/,
        use: [{
          loader: 'file-loader?emitFile=false&name=../../[path][name].[ext]'
        }]
      }]
    },
    plugins: [
      extractSass
    ]
  }
];
