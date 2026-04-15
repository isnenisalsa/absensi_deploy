import React, { useState } from "react";
import { ModernDataTable, ColumnDef } from "../ModernDataTable";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";

interface Attendance {
  nrp: string;
  attendance_date: string;
  time_wita: string;
  trans_type: string;
  work_location: string;
  cp_location?: string;
  att_latitude?: number;
  att_longitude?: number;
  photo_evidence?: string;
  employee?: { 
    full_name: string;
    position?: { pos_name: string };
    division?: { div_name: string; department?: { dept_name: string } };
  };
}

// ── Config URL backend ──────────────────────────────────────────────────────
const BACKEND_URL = "http://localhost:3000"; // Sesuaikan jika beda port

function getPhotoUrl(filename?: string): string | null {
  if (!filename || filename.trim() === "") return null;
  // Sudah full URL (dummy lama)
  if (filename.startsWith("http")) {
    // Tolak dummy URL lama
    if (filename.includes("dummy") || filename.includes("pama.com")) return null;
    return filename;
  }
  return `${BACKEND_URL}/uploads/attendance/${filename}`;
}

// ── Photo Preview Modal ─────────────────────────────────────────────────────
function PhotoPreviewModal({ url, onClose }: { url: string; onClose: () => void }) {
  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm"
      onClick={onClose}
    >
      <div
        className="relative max-w-lg w-full mx-4"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Close button */}
        <button
          onClick={onClose}
          className="absolute -top-10 right-0 text-white/80 hover:text-white transition-colors"
        >
          <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        {/* Photo */}
        <div className="rounded-2xl overflow-hidden shadow-2xl border border-white/10">
          <img
            src={url}
            alt="Foto Bukti Absensi"
            className="w-full object-contain max-h-[70vh]"
            onError={(e) => {
              (e.target as HTMLImageElement).src =
                "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect width='400' height='300' fill='%23374151'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' fill='%239CA3AF' dy='.3em' font-size='14'%3EGagal memuat foto%3C/text%3E%3C/svg%3E";
            }}
          />
        </div>

        {/* Label */}
        <div className="mt-3 text-center">
          <span className="text-white/60 text-xs font-semibold tracking-widest uppercase">
            📷 Foto Bukti Absensi
          </span>
        </div>
      </div>
    </div>
  );
}

interface HistoryTableProps {
  data: Attendance[];
}

export function HistoryTable({ data }: HistoryTableProps) {
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);

  const columns: ColumnDef<Attendance>[] = [
    {
      header: "FOTO",
      accessorKey: "photo_evidence",
      className: "min-w-[80px] text-center",
      cell: (att) => {
        const url = getPhotoUrl(att.photo_evidence);
        if (!url) {
          return (
            <Badge className="text-[8px] font-black uppercase tracking-widest px-2 py-0.5 border shadow-none bg-slate-100 text-slate-400 border-slate-200">
              No Photo
            </Badge>
          );
        }
        return (
          <button
            onClick={() => setPreviewUrl(url)}
            className="group relative block mx-auto"
            title="Klik untuk lihat foto"
          >
            <img
              src={url}
              alt="Foto bukti"
              className="w-10 h-10 rounded-lg object-cover border-2 border-transparent group-hover:border-blue-400 transition-all duration-200 shadow-sm"
              onError={(e) => {
                (e.target as HTMLImageElement).style.display = "none";
              }}
            />
            {/* Hover zoom overlay */}
            <div className="absolute inset-0 rounded-lg bg-blue-500/0 group-hover:bg-blue-500/20 flex items-center justify-center transition-all duration-200">
              <svg className="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
              </svg>
            </div>
          </button>
        );
      },
    },
    {
      header: "ATTENDANCE DATE",
      accessorKey: "attendance_date",
      className: "min-w-[150px]",
      cell: (att) => (
        <span className="text-[12px] font-black text-slate-400">
          {new Date(att.attendance_date).toISOString().split('T')[0]}
        </span>
      ),
    },
    {
      header: "ATTENDANCE HOUR",
      accessorKey: "time_wita",
      className: "min-w-[150px]",
      cell: (att) => {
        if (!att.time_wita) return "-";
        let timeVal = "-";
        if (att.time_wita.includes('T')) {
          timeVal = att.time_wita.split('T')[1].substring(0, 8);
        } else if (att.time_wita.includes(' ')) {
          timeVal = att.time_wita.split(' ')[1].substring(0, 8);
        } else {
          timeVal = att.time_wita.substring(0, 8);
        }
        return (
          <span className="text-[12px] font-black text-slate-400">
            {timeVal}
          </span>
        );
      },
    },
    {
      header: "NRP",
      accessorKey: "nrp",
      className: "min-w-[120px]",
      cell: (att) => (
        <span className="text-[13px] font-black text-[#1e63d3] hover:underline cursor-pointer tracking-tight">
          {att.nrp}
        </span>
      ),
    },
    {
      header: "NAMA",
      accessorKey: "employee.full_name",
      className: "min-w-[200px]",
      cell: (att) => (
        <span className="text-[13px] font-black text-slate-800 uppercase tracking-tight">
          {att.employee?.full_name || "-"}
        </span>
      ),
    },
    {
      header: "POSISI",
      accessorKey: "employee.position.pos_name",
      className: "min-w-[150px]",
      cell: (att) => (
        <span className="text-[10px] font-black text-slate-400 uppercase tracking-tight">
          {att.employee?.position?.pos_name || "-"}
        </span>
      ),
    },
    {
      header: "DIVISI",
      accessorKey: "employee.division.div_name",
      className: "min-w-[150px]",
      cell: (att) => (
        <span className="text-[10px] font-black text-slate-400 uppercase tracking-tight">
          {att.employee?.division?.div_name || "-"}
        </span>
      ),
    },
    {
      header: "DEPT",
      accessorKey: "employee.division.department.dept_name",
      className: "min-w-[120px]",
      cell: (att) => (
        <span className="text-[10px] font-black text-slate-400 uppercase tracking-tight">
          {att.employee?.division?.department?.dept_name || "-"}
        </span>
      ),
    },
    {
      header: "TRANS",
      accessorKey: "trans_type",
      className: "min-w-[80px] text-center",
      cell: (att) => {
        const isCheckIn = att.trans_type === "Check_in";
        return (
          <Badge className={cn(
            "text-[9px] font-black uppercase tracking-widest px-2.5 py-1 border shadow-none",
            isCheckIn ? "bg-emerald-100 text-emerald-700 border-emerald-200" : "bg-rose-100 text-rose-700 border-rose-200"
          )}>
            {isCheckIn ? "IN" : "OUT"}
          </Badge>
        );
      },
    },
    {
      header: "CP LOCATION",
      accessorKey: "cp_location",
      className: "min-w-[150px]",
      cell: (att) => (
        <span className="text-[10px] font-black text-slate-400 uppercase tracking-tight">
          {att.cp_location || "-"}
        </span>
      ),
    },
    {
      header: "ATT LOCATION",
      accessorKey: "work_location",
      className: "min-w-[180px]",
      cell: (att) => (
        <span className="text-[10px] font-bold text-slate-400 italic font-mono tracking-tighter">
          {att.att_latitude ? `${Number(att.att_latitude).toFixed(4)}, ${Number(att.att_longitude).toFixed(4)}` : att.work_location}
        </span>
      ),
    },
  ];

  return (
    <>
      {/* Photo Preview Modal */}
      {previewUrl && (
        <PhotoPreviewModal url={previewUrl} onClose={() => setPreviewUrl(null)} />
      )}

      <ModernDataTable 
        data={data} 
        columns={columns} 
        searchPlaceholder="Cari Riwayat Absensi..." 
        hideHeader={true}
      />
    </>
  );
}


interface Attendance {
  nrp: string;
  attendance_date: string;
  time_wita: string;
  trans_type: string;
  work_location: string;
  cp_location?: string;
  att_latitude?: number;
  att_longitude?: number;
  employee?: { 
    full_name: string;
    position?: { pos_name: string };
    division?: { div_name: string; department?: { dept_name: string } };
  };
}

interface HistoryTableProps {
  data: Attendance[];
}

export function HistoryTable({ data }: HistoryTableProps) {
  const columns: ColumnDef<Attendance>[] = [
    {
      header: "ATTENDANCE DATE",
      accessorKey: "attendance_date",
      className: "min-w-[150px]",
      cell: (att) => (
        <span className="text-[12px] font-black text-slate-400">
          {new Date(att.attendance_date).toISOString().split('T')[0]}
        </span>
      ),
    },
    {
      header: "ATTENDANCE HOUR",
      accessorKey: "time_wita",
      className: "min-w-[150px]",
      cell: (att) => {
        if (!att.time_wita) return "-";
        // Robust extraction from ISO (T) or standard date-time string (space)
        let timeVal = "-";
        if (att.time_wita.includes('T')) {
          timeVal = att.time_wita.split('T')[1].substring(0, 8);
        } else if (att.time_wita.includes(' ')) {
          timeVal = att.time_wita.split(' ')[1].substring(0, 8);
        } else {
          timeVal = att.time_wita.substring(0, 8);
        }
        return (
          <span className="text-[12px] font-black text-slate-400">
            {timeVal}
          </span>
        );
      },
    },
    {
      header: "NRP",
      accessorKey: "nrp",
      className: "min-w-[120px]",
      cell: (att) => (
        <span className="text-[13px] font-black text-[#1e63d3] hover:underline cursor-pointer tracking-tight">
          {att.nrp}
        </span>
      ),
    },
    {
      header: "NAMA",
      accessorKey: "employee.full_name",
      className: "min-w-[200px]",
      cell: (att) => (
        <span className="text-[13px] font-black text-slate-800 uppercase tracking-tight">
          {att.employee?.full_name || "-"}
        </span>
      ),
    },
    {
      header: "POSISI",
      accessorKey: "employee.position.pos_name",
      className: "min-w-[150px]",
      cell: (att) => (
        <span className="text-[10px] font-black text-slate-400 uppercase tracking-tight">
          {att.employee?.position?.pos_name || "-"}
        </span>
      ),
    },
    {
      header: "DIVISI",
      accessorKey: "employee.division.div_name",
      className: "min-w-[150px]",
      cell: (att) => (
        <span className="text-[10px] font-black text-slate-400 uppercase tracking-tight">
          {att.employee?.division?.div_name || "-"}
        </span>
      ),
    },
    {
      header: "DEPT",
      accessorKey: "employee.division.department.dept_name",
      className: "min-w-[120px]",
      cell: (att) => (
        <span className="text-[10px] font-black text-slate-400 uppercase tracking-tight">
          {att.employee?.division?.department?.dept_name || "-"}
        </span>
      ),
    },
    {
      header: "TRANS",
      accessorKey: "trans_type",
      className: "min-w-[80px] text-center",
      cell: (att) => {
        const isCheckIn = att.trans_type === "Check_in";
        return (
          <Badge className={cn(
            "text-[9px] font-black uppercase tracking-widest px-2.5 py-1 border shadow-none",
            isCheckIn ? "bg-emerald-100 text-emerald-700 border-emerald-200" : "bg-rose-100 text-rose-700 border-rose-200"
          )}>
            {isCheckIn ? "IN" : "OUT"}
          </Badge>
        );
      },
    },
    {
      header: "CP LOCATION",
      accessorKey: "cp_location",
      className: "min-w-[150px]",
      cell: (att) => (
        <span className="text-[10px] font-black text-slate-400 uppercase tracking-tight">
          {att.cp_location || "-"}
        </span>
      ),
    },
    {
      header: "ATT LOCATION",
      accessorKey: "work_location",
      className: "min-w-[180px]",
      cell: (att) => (
        <span className="text-[10px] font-bold text-slate-400 italic font-mono tracking-tighter">
          {att.att_latitude ? `${Number(att.att_latitude).toFixed(4)}, ${Number(att.att_longitude).toFixed(4)}` : att.work_location}
        </span>
      ),
    },
  ];

  return (
    <ModernDataTable 
      data={data} 
      columns={columns} 
      searchPlaceholder="Cari Riwayat Absensi..." 
      hideHeader={true}
    />
  );
}
