const http = require('http');

async function doLogin() {
  return new Promise((resolve, reject) => {
    const data = JSON.stringify({ nrp: "AR260011", password: "password123" });
    const req = http.request({
      hostname: 'localhost', port: 3000, path: '/api/auth/login', method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(data) }
    }, res => {
      let body = '';
      res.on('data', d => body += d);
      res.on('end', () => resolve(JSON.parse(body)));
    });
    req.write(data); req.end();
  });
}

async function doCheckIn(token) {
  return new Promise((resolve, reject) => {
    const data = JSON.stringify({
      shift_id: 1, lat: -3.786, long: 115.359, location_id: 4, cp_location: "SBT", work_location: "Office Mulia", photo_url: "https://pama.com/dummy.jpg"
    });
    const req = http.request({
      hostname: 'localhost', port: 3000, path: '/api/attendance/check-in', method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(data), 'Authorization': `Bearer ${token}` }
    }, res => {
      let body = '';
      res.on('data', d => body += d);
      res.on('end', () => resolve({ status: res.statusCode, body }));
    });
    req.write(data); req.end();
  });
}

(async () => {
    const loginRes = await doLogin();
    console.log("LOGIN:", loginRes);
    if(loginRes.token) {
        const ciRes = await doCheckIn(loginRes.token);
        console.log("CHECK IN RESULT:", ciRes.status, ciRes.body);
    }
})();
