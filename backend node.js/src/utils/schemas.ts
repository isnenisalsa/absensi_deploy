import { z } from 'zod';

export const loginSchema = z.object({
  body: z.object({
    nrp: z.string().min(1, 'NRP Karyawan tidak boleh kosong').max(100),
    password: z.string().min(6, 'Password minimal 6 karakter')
  })
});

export const checkInSchema = z.object({
  body: z.object({
    shift_id: z.number().int({ message: 'Shift ID minimal berupa angka' }),
    cp_location: z.string().optional(),
    work_location: z.string().optional(),
    lat: z.number().min(-90).max(90, { message: 'Latitude tidak valid' }),
    long: z.number().min(-180).max(180, { message: 'Longitude tidak valid' }),
    photo_url: z.string().url({ message: 'Wajib berformat URL gambar yang sah' }).optional(),
  })
});

export const checkOutSchema = z.object({
  body: z.object({
    lat: z.number().min(-90).max(90, { message: 'Latitude tidak valid' }),
    long: z.number().min(-180).max(180, { message: 'Longitude tidak valid' }),
    photo_url: z.string().url({ message: 'Wajib berformat URL gambar yang sah' }).optional(),
  })
});

export const ftwReportSchema = z.object({
  body: z.object({
    jam_tidur_12_jam: z.string().min(1, 'Data jam tidur wajib diisi'),
    jam_bangun: z.string().min(1, 'Data jam bangun wajib diisi'),
    konsumsi_obat: z.boolean(),
    punya_masalah: z.boolean(),
    gejala_kesehatan: z.boolean(),
    status_ftw: z.string().optional(),
  })
});
