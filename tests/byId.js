const http = require('http');

const options = {
  hostname: 'localhost',
  port: 3000,
  path: '/users/list/6649f66c5f59cc80a73b5598',
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
  },
};

const req = http.request(options, (res) => {
  console.log(`STATUS: ${res.statusCode}`);
});
req.end();

