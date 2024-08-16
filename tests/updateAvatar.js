const http = require('http');

const options = {
  hostname: 'localhost',
  port: 3000,
  path: '/users/me/avatar',
  method: 'post',
  headers: {
    'Content-Type': 'application/json',
  },
};
const update = JSON.stringify({
avatar: 'www.MYPHOTO.ru'
})

const req = http.request(options, (res) => {
  console.log(`STATUS: ${res.statusCode}`);
});

req.write(update);
req.end();
