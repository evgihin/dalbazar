const http = require('http');

const options = {
  hostname: 'localhost',
  port: 3000,
  path: '/users/me',
  method: 'post',
  headers: {
    'Content-Type': 'application/json',
  },
};
const update = JSON.stringify({
name: 'JENYA',
about: 'razrabotchik',
avatar: 'www.myphoto.ru'
})

const req = http.request(options, (res) => {
  console.log(`STATUS: ${res.statusCode}`);
});

req.write(update);
req.end();
