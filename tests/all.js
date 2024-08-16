const http = require('http');

const options = {
  hostname: 'localhost',
  port: 3000,
  path: '/users/list',
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
  },
};

const req = http.request(options, (res) => {
  console.log(`STATUS: ${res.statusCode}`);
});

req.end();
