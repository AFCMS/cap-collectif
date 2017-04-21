const net = require('net');
const fs = require('fs');
var exec = require( "child_process" ).exec;

const socket = 'node_ssr.sock';
const bundlePath = '/var/www/web/js/';

let user = 'capco';
let bundleFileName = 'server-bundle.js';
let currentArg;

function Handler() {
  this.queue = [];
  this.initialized = false;
}

let i = 0;
Handler.prototype.handle = function (connection) {
  const callback = function () {
    connection.setEncoding('utf8');
    i = i + 1;
    let completeData = '';
    console.log('[SSR] Processing request #' + i);
    connection.on('data', (data) => {
      completeData += data;
    });
    const evalCode = function() {
      if (completeData.length === 0) {
        setTimeout(evalCode, 10);
      }
      else {
        try {
          const result = eval(completeData);
          console.log('[SSR] Completed successfully request #'+ i);
          connection.write(result);
          connection.end();
        }
        catch (e) {
          if (e instanceof SyntaxError) {
            console.log('[SSR] Data reveived not full, waiting request #'+ i);
            setTimeout(evalCode, 10);
          }
        }
      }
    }
    evalCode();
  };

  if (this.initialized) {
    callback();
  }
  else {
    this.queue.push(callback);
  }
};

Handler.prototype.initialize = function () {
  console.log(`[SSR] Processing ${this.queue.length} pending requests`);
  let callback = this.queue.pop();
  while (callback) {
    callback();
    callback = this.queue.pop();
  }

  this.initialized = true;
};

const handler = new Handler();

process.argv.forEach((val) => {
  if (val[0] == '-') {
    user = 'capco';
    // currentArg = val.slice(1);
    // return;
  }

  // if (currentArg == 'user') {
  //   console.log('User is '+ val);
  //   user = val;
  // }
});

try {
  fs.mkdirSync(bundlePath);
} catch (e) {
  if (e.code != 'EEXIST') throw e;
}

require(bundlePath + bundleFileName);
console.log(`[SSR] Loaded server bundle: ${bundlePath}${bundleFileName}`);
handler.initialize();

const unixServer = net.createServer((connection) => {
  handler.handle(connection);
});

fs.watchFile(bundlePath + bundleFileName, (curr) => {
  if (curr && curr.blocks && curr.blocks > 0) {
    if (handler.initialized) {
      console.log('[SSR] Restarting the node process, to reload server bundle!');
      unixServer.close();
      process.exit();
      return;
    }

    require(bundlePath + bundleFileName);
    console.log(`[SSR] Loaded server bundle: ${bundlePath}${bundleFileName}`);
    handler.initialize();
  }
});

unixServer.listen(socket, () => {
  const sock = `${process.cwd()}/${socket}`;
  fs.chmodSync(sock, '777');
  console.log(`[SSR] Giving access to socket for "${user}".`)
  exec("chown "+ user +":"+ user +" "+ sock);
  console.log(`[SSR] Listening socket: unix://${sock}`);
});

process.on('SIGINT', () => {
  unixServer.close();
  process.exit();
});
