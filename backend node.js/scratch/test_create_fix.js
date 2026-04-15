const axios = require('axios');

const testCreate = async () => {
    try {
        // payload dengan mitra_id kosong (seperti yang dialami user)
        const payload = {
            nrp: 'TEST' + Math.floor(Math.random() * 10000),
            full_name: 'Test Karyawan',
            pos_id: '', 
            div_id: '',
            mitra_id: '', // Ini yang sering jadi 0
            location_id: ''
        };

        console.log('Testing with payload:', payload);
        
        // Catatan: Karena backend butuh token, tes ini mungkin gagal 401
        // Tapi kita bisa melihat apakah errornya 401 atau 500 (Constraint Violation)
        const response = await axios.post('http://localhost:3001/api/employees', payload, {
            headers: { 'Content-Type': 'application/json' }
        });

        console.log('Success:', response.status);
    } catch (error) {
        if (error.response) {
            console.log('Error Status:', error.response.status);
            console.log('Error Body:', error.response.data);
        } else {
            console.log('Error:', error.message);
        }
    }
};

testCreate();
