const http = require('http');

const data = JSON.stringify({
  shift_id: 1,
  lat: -3.786,
  long: 115.359,
  location_id: 4,
  cp_location: "SBT",
  work_location: "Office Mulia",
  photo_url: "https://pama.com/dummy.jpg"
});

const req = http.request({
  hostname: 'localhost',
  port: 3000,
  path: '/api/attendance/check-in',
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Content-Length': data.length
    // NO token to see if it even reaches auth or if token is generated
  }
}, res => {
  let body = '';
  res.on('data', chunk => body += chunk);
  res.on('end', () => console.log('STATUS:', res.statusCode, 'BODY:', body));
});
req.on('error', console.error);
req.write(data);
req.end();
