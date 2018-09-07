const webpack = require("webpack");
const HtmlWebpackPlugin = require("html-webpack-plugin");
const ExtractTextPlugin = require("extract-text-webpack-plugin");
const CopyWebpackPlugin = require("copy-webpack-plugin");
const cleanWebpackPlugin = require("clean-webpack-plugin");
const merge = require("webpack-merge");

const os = require("os");
const fs = require("fs");
const path = require("path");

fs.open("./build/env.js", "w", function (err, fd) {
  const buf = 'module.exports = "development";';
  fs.write(fd, buf, 0, buf.length, 0, function (err, written, buffer) { });
});

const webpackBaseConfig = require("./webpack.base.config.js");
const util = require("./util.js");
const package = require("../package.json");

module.exports = merge(webpackBaseConfig, {
  devtool: "#source-map",
  devServer: {
    allowedHosts: [
      'tiny.test',
    ],
    headers: {
      'Access-Control-Allow-Origin': '*'
    },
    staticOptions: {
      setHeaders: function (res, path, stat) {
        res.set('x-timestamp', Date.now());
        res.set('Access-Control-Allow-Origin', '*');
      }
    },
  },
  output: {
    path: path.resolve(__dirname, '../../../../public/assets/dist_dev'),
    publicPath: "http://localhost:8080/assets/dist_dev/",
    filename: "[name].js",
    chunkFilename: "[name].[hash].chunk.js"
  },
  plugins: [
    new webpack.ProvidePlugin({
      Promise: "bluebird"
    }),
    new webpack.NormalModuleReplacementPlugin(/es6-promise$/, "bluebird"),
    new cleanWebpackPlugin(
      ["resources/views/dashboard/dist/dist/*", "public/assets/dist_dev/*"], {
        root: path.resolve(__dirname, "../../../../")
      }
    ),
    new ExtractTextPlugin({
      filename: "[name].css",
      allChunks: true
    }),
    new webpack.optimize.CommonsChunkPlugin({
      name: ["vender-exten", "vender-base"],
      minChunks: Infinity
    }),
    new HtmlWebpackPlugin({
      filename: "../index.blade.php",
      template: "!!ejs-loader!./src/template/index.ejs",
      inject: false
    }),
    new CopyWebpackPlugin([{
      from: "src/styles/fonts",
      to: "fonts"
    },
    {
      from: "src/views/main-components/theme-switch/theme"
    }
    ]),
    function () {
      this.plugin("done", function (statsData) {
        const stats = statsData.toJson();
        if (stats.errors.length) {
          return;
        }
        util.autoBuildApi();
        var html = fs.readFileSync(path.join(__dirname, "../../../../public/assets/index.blade.php"), 'utf8');

          var htmlOutput = html.replace(/\{\{\$cdn\}\}\/assets\/dist_dev\//g, 'http://localhost:8080/assets/dist_dev/')
              .replace(/\{\{\$cdn\}\}\/assets\/dist\//g, 'http://localhost:8080/assets/dist_dev/')
              .replace(/vender-base\.[a-z0-9A-Z]*\.js\?v=/, 'vender-base.js?v=' + stats.hash + '_')
              .replace(/vender-exten\.[a-z0-9A-Z]*\.js\?v=/, 'vender-exten.js?v=' + stats.hash + '_')
              .replace(/main\.[a-z0-9A-Z]*\.js\?v=/, 'main.js?v=' + stats.hash + '_');
        fs.writeFileSync(path.join(__dirname, "../index.blade.php"), htmlOutput);
      });
    }
  ]
});