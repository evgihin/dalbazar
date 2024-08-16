const http = require('http');

const options = {
  hostname: 'localhost',
  port: 3000,
  path: '/users/add',
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
};
const add = JSON.stringify({
name: 'jenya',
about: 'razrabotchik',
avatar: 'www.myphoto.ru'
})

const req = http.request(options, (res) => {
  console.log(`STATUS: ${res.statusCode}`);
});

req.write(add);
req.end();
