import jwt from 'jsonwebtoken';

const SECRET_KEY = process.env.JWT_SECRET || 'super_secret_absensi_key_123';

export interface TokenPayload {
  nrp: string;
  role: string;
  mitra_id?: number | null;
}

export const generateToken = (payload: TokenPayload): string => {
  return jwt.sign(payload, SECRET_KEY, { expiresIn: '365d' });
};

export const verifyToken = (token: string): TokenPayload | null => {
  try {
    return jwt.verify(token, SECRET_KEY) as TokenPayload;
  } catch (error: any) {
    console.error(`[JWT Debug] Verification failed for token: ${token.substring(0, 15)}... Error: ${error.message}`);
    return null;
  }
};
