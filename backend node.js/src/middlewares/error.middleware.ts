import { Request, Response, NextFunction } from 'express';

export const errorHandler = (err: any, req: Request, res: Response, next: NextFunction): void => {
  // Hanya log ke konsol, jangan diekspos ke respon API
  console.error(`[Error] ${err.message || 'Unknown server error'} \n ${err.stack}`);

  res.status(err.status || 500).json({
    error: err.message || 'Terjadi kesalahan sistem internal. Silakan hubungi tim Admin.',
    details: String(err),
    stack: err.stack
  });
};
