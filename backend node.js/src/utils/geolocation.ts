/**
 * Menghitung jarak antara dua titik koordinat bumi (Latitude & Longitude) dalam satuan Meter.
 * Memakai formula Haversine untuk mendapatkan jarak akurat membelah rotasi bumi.
 * 
 * @param lat1 Latitude Pengguna
 * @param lon1 Longitude Pengguna
 * @param lat2 Latitude Pusat (Kantor)
 * @param lon2 Longitude Pusat (Kantor)
 * @returns Jarak dalam bentuk Meter
 */
export const calculateDistance = (lat1: number, lon1: number, lat2: number, lon2: number): number => {
    const R = 6371e3; // Radius bumi dalam meter
    const phi1 = lat1 * Math.PI / 180; // konversi ke radians
    const phi2 = lat2 * Math.PI / 180;
    const deltaPhi = (lat2 - lat1) * Math.PI / 180;
    const deltaLambda = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
        Math.cos(phi1) * Math.cos(phi2) *
        Math.sin(deltaLambda / 2) * Math.sin(deltaLambda / 2);
        
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    const distance = R * c; 
    return distance; // Jarak aktual di bumi
};

/**
 * Interface untuk koordinat GPS
 */
export interface Point {
    lat: number;
    lng: number;
}

/**
 * Mengecek apakah sebuah titik berada di dalam polygon menggunakan Ray Casting Algorithm.
 */
export const isPointInPolygon = (point: Point, polygon: Point[]): boolean => {
    let isInside = false;
    for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
        const xi = polygon[i].lat, yi = polygon[i].lng;
        const xj = polygon[j].lat, yj = polygon[j].lng;

        const intersect = ((yi > point.lng) !== (yj > point.lng))
            && (point.lat < (xj - xi) * (point.lng - yi) / (yj - yi) + xi);
        if (intersect) isInside = !isInside;
    }
    return isInside;
};

/**
 * Memparsing string koordinat format "lat,lng#lat,lng#..." menjadi array of Point.
 */
export const parsePolygonCoords = (coordsStr: string): Point[] => {
    try {
        return coordsStr.split('#').map(p => {
            const parts = p.split(',');
            if (parts.length !== 2) throw new Error("Format koordinat salah");
            return {
                lat: parseFloat(parts[0].trim()),
                lng: parseFloat(parts[1].trim())
            };
        });
    } catch (err) {
        console.error("Gagal parsing polygon_coords:", err);
        return [];
    }
};
