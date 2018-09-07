const async = require("async");
const os = require('os');
const fs = require('fs');
const path = require('path');
const http = require('http');
const nodekl = require('nodekl');
const cp = require('child_process');
const execSync = cp.execSync;

var PHP_CONFIG = {};

(function () {
    const config_dir = path.join(path.resolve(__dirname, '../../../..'), 'config')
    const dump = path.join(config_dir, 'dumpjson.php')
    const config = path.join(config_dir, 'app-config.ignore.php')
    var output = execSync(`php ${dump} ${config}`);

    PHP_CONFIG = output ? JSON.parse(output) : {};
})();

function autoBuildApi() {
    var http_host = PHP_CONFIG['ENV_WEB']['devsrv'];
    var dev_token = nodekl.encode(PHP_CONFIG['ENV_DEVELOP_KEY'], PHP_CONFIG['ENV_CRYPT_KEY']);
    dev_token && !(function(){
        console.log('\nbuild api js');
        setTimeout(function (){
            http.get(http_host + "/develop/deploy/buildapimodjs?dev_debug=1&dev_token=" + dev_token, function(res) {
                console.log("Build API js response: " + res.statusCode);
            }).on('error', function(e) {
                console.log("Build API js error: " + e.message);
            });
        }, 1000);

        setTimeout(function (){
            http.get(http_host + "/api/ApiHub/autoBuildComponentMap?dev_debug=1&dev_token=" + dev_token, function(res) {
                console.log("Build autoBuildComponentMap js response: " + res.statusCode);
            }).on('error', function(e) {
                console.log("Build autoBuildComponentMap js error: " + e.message);
            });
        }, 1000);

        
    })();
}

// cursively make dir
function mkdirs(p, mode, f, made) {
    if (typeof mode === 'function' || mode === undefined) {
        f = mode;
        mode = 777 & (~process.umask());
    }
    if (!made)
        made = null;

    var cb = f || function () {};
    if (typeof mode === 'string')
        mode = parseInt(mode, 8);
    p = path.resolve(p);

    fs.mkdir(p, mode, function (er) {
        if (!er) {
            made = made || p;
            return cb(null, made);
        }
        switch (er.code) {
            case 'ENOENT':
                mkdirs(path.dirname(p), mode, function (er, made) {
                    if (er) {
                        cb(er, made);
                    } else {
                        mkdirs(p, mode, cb, made);
                    }
                });
                break;

            // In the case of any other error, just see if there's a dir
            // there already.  If so, then hooray!  If not, then something
            // is borked.
            default:
                fs.stat(p, function (er2, stat) {
                    // if the stat fails, then that's super weird.
                    // let the original error be the failure reason.
                    if (er2 || !stat.isDirectory()) {
                        cb(er, made);
                    } else {
                        cb(null, made)
                    }
                });
                break;
        }
    });
}
// single file copy
function copyFile(file, toDir, cb) {
    async.waterfall([
        function (callback) {
            fs.exists(toDir, function (exists) {
                if (exists) {
                    callback(null, false);
                } else {
                    callback(null, true);
                }
            });
        }, function (need, callback) {
            if (need) {
                mkdirs(path.dirname(toDir), callback);
            } else {
                callback(null, true);
            }
        }, function (p, callback) {
            var reads = fs.createReadStream(file);
            var writes = fs.createWriteStream(path.join(path.dirname(toDir), path.basename(file)));
            reads.pipe(writes);
            //don't forget close the  when  all the data are read
            reads.on("end", function () {
                writes.end();
                callback(null);
            });
            reads.on("error", function (err) {
                console.log("error occur in reads");
                callback(true, err);
            });

        }
    ], cb);

}

// cursively count the  files that need to be copied

function _ccoutTask(from, to, cbw) {
    async.waterfall([
        function (callback) {
            fs.stat(from, callback);
        },
        function (stats, callback) {
            if (stats.isFile()) {
                cbw.addFile(from, to);
                callback(null, []);
            } else if (stats.isDirectory()) {
                fs.readdir(from, callback);
            }
        },
        function (files, callback) {
            if (files.length) {
                for (var i = 0; i < files.length; i++) {
                    _ccoutTask(path.join(from, files[i]), path.join(to, files[i]), cbw.increase());
                }
            }
            callback(null);
        }
    ], cbw);

}
// wrap the callback before counting
function ccoutTask(from, to, cb) {
    var files = [];
    var count = 1;

    function wrapper(err) {
        count--;
        if (err || count <= 0) {
            cb(err, files)
        }
    }
    wrapper.increase = function () {
        count++;
        return wrapper;
    }
    wrapper.addFile = function (file, dir) {
        files.push({
            file : file,
            dir : dir
        });
    }

    _ccoutTask(from, to, wrapper);
}

function copyDir(from, to, cb) {
    if(!cb){
        cb=function(){};
    }
    async.waterfall([
        function (callback) {
            fs.exists(from, function (exists) {
                if (exists) {
                    callback(null, true);
                } else {
                    console.log(from + " not exists");
                    callback(true);
                }
            });
        },
        function (exists, callback) {
            fs.stat(from, callback);
        },
        function (stats, callback) {
            if (stats.isFile()) {
                // one file copy
                copyFile(from, to, function (err) {
                    if (err) {
                        // break the waterfall
                        callback(true);
                    } else {
                        callback(null, []);
                    }
                });
            } else if (stats.isDirectory()) {
                ccoutTask(from, to, callback);
            }
        },
        function (files, callback) {
            // prevent reaching to max file open limit
            async.mapLimit(files, 30, function (f, cb) {
                copyFile(f.file, f.dir, cb);
            }, callback);
        }
    ], cb);
}


module.exports = {
    autoBuildApi: autoBuildApi,
    mkdirs: mkdirs,
    copyFile: copyFile,
    copyDir: copyDir,
    PHP_CONFIG: PHP_CONFIG
};
