import React from "react";
import { ModernDataTable, ColumnDef } from "../ModernDataTable";
import { Badge } from "../ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "../ui/avatar";
import { Button } from "../ui/button";
import { Edit, Trash2 } from "lucide-react"; // Keep these as they are used in reference tables
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "../ui/tooltip";

interface Shift {
  shift_id: string | number;
  shift_code: string;
  time_in_expected: string;
  time_out_expected: string;
  date_in?: string;
  date_out?: string;
}

interface ShiftsTableProps {
  data: Shift[];
}

// Robust time formatter that never throws
const formatTimeSafe = (timeStr: string) => {
  if (!timeStr) return "00:00";
  
  // If it's a standard HH:mm:ss or HH:mm
  if (timeStr.includes(':')) {
    const parts = timeStr.split(':');
    if (parts.length >= 2) {
      return `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}`;
    }
  }

  // If it's an ISO string
  try {
    const date = new Date(timeStr);
    if (!isNaN(date.getTime())) {
      return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'UTC' });
    }
  } catch (e) {
    // Ignore
  }

  return timeStr.substring(0, 5);
};

export function ShiftsTable({ data = [] }: ShiftsTableProps) {
  const columns: ColumnDef<Shift>[] = [
    {
      header: "Kode Shift",
      accessorKey: "shift_code",
      className: "font-bold text-black",
    },
    {
      header: "Identitas Shift",
      accessorKey: "shift_id", // Using existing key for safety
      cell: (shift) => (
        <div className="flex items-center gap-3">
          <Avatar className="h-9 w-9 border-2 border-white shadow-sm ring-1 ring-slate-100">
            <AvatarImage src={`https://api.dicebear.com/7.x/initials/svg?seed=${shift.shift_code || 'default'}`} />
            <AvatarFallback>{(shift.shift_code || 'S')[0]}</AvatarFallback>
          </Avatar>
          <div className="flex flex-col">
            <span className="text-[14px] font-bold text-black leading-tight tracking-tight uppercase">{shift.shift_code || '---'}</span>
            <span className="text-[11px] font-bold text-black mt-0.5">Shift Kerja</span>
          </div>
        </div>
      ),
    },
    {
      header: "Jam Masuk",
      accessorKey: "time_in_expected",
      className: "text-center",
      cell: (shift) => {
        const time = formatTimeSafe(shift.time_in_expected);
        return (
          <div className="flex justify-center">
            <Badge variant="outline" className="bg-emerald-50 text-emerald-800 font-extrabold text-[12px] px-3.5 py-1.5 rounded-xl border-emerald-100/50 shadow-sm gap-2">
              <i className="fa-solid fa-clock text-emerald-500" /> {time}
            </Badge>
          </div>
        );
      }
    },
    {
      header: "Jam Pulang",
      accessorKey: "time_out_expected",
      className: "text-center",
      cell: (shift) => {
        const time = formatTimeSafe(shift.time_out_expected);
        return (
          <div className="flex justify-center">
            <Badge variant="outline" className="bg-rose-50 text-rose-800 font-extrabold text-[12px] px-3.5 py-1.5 rounded-xl border-rose-100/50 shadow-sm gap-2">
              <i className="fa-solid fa-clock text-rose-500" /> {time}
            </Badge>
          </div>
        );
      }
    },
    {
      header: "Status Shift",
      accessorKey: "date_out",
      className: "text-center",
      cell: (shift) => {
        const isNight = shift.date_out && shift.date_out !== shift.date_in;
        return (
          <div className="flex justify-center">
            {isNight ? (
              <Badge className="bg-slate-900 text-white font-black text-[9px] px-3 py-1.5 rounded-full shadow-md uppercase tracking-wider gap-2 border-none">
                <i className="fa-solid fa-moon text-yellow-400" /> Shift Malam
              </Badge>
            ) : (
              <Badge variant="secondary" className="bg-blue-50 text-blue-700 font-black text-[9px] px-3 py-1.5 rounded-full border-blue-100 shadow-sm uppercase tracking-wider gap-2">
                <i className="fa-solid fa-sun text-orange-400" /> Shift Normal
              </Badge>
            )}
          </div>
        );
      }
    },
    {
      header: "Aksi",
      accessorKey: "shift_id",
      className: "text-right",
      cell: (shift) => {
        const tIn = formatTimeSafe(shift.time_in_expected);
        const tOut = formatTimeSafe(shift.time_out_expected);

        return (
          <div className="flex justify-end gap-2.5">
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button variant="outline" size="icon" className="h-9 w-9 flex items-center justify-center rounded-xl transition-all text-blue-600 border-blue-100 bg-blue-50/50 hover:bg-blue-100/50 hover:border-blue-300 shadow-sm"
                          onClick={() => (window as any).editShift(shift, tIn, tOut)}>
                    <i className="fa-solid fa-pen-to-square text-[14px]"></i>
                  </Button>
                </TooltipTrigger>
                <TooltipContent side="top" className="bg-blue-600 text-white border-none text-[10px] font-bold uppercase tracking-widest px-3 py-1.5 shadow-lg shadow-black/10">
                  <p>Edit Data Shift</p>
                </TooltipContent>
              </Tooltip>

              <Tooltip>
                <TooltipTrigger asChild>
                  <Button variant="outline" size="icon" className="h-9 w-9 flex items-center justify-center rounded-xl transition-all text-red-600 border-red-100 bg-red-50/50 hover:bg-red-100/50 hover:border-red-300 shadow-sm"
                          onClick={() => (window as any).confirmDelete(shift.shift_id, shift.shift_code)}>
                    <i className="fa-solid fa-trash-can text-[14px]"></i>
                  </Button>
                </TooltipTrigger>
                <TooltipContent side="top" className="bg-red-600 text-white border-none text-[10px] font-bold uppercase tracking-widest px-3 py-1.5 shadow-lg shadow-black/10">
                  <p>Hapus Permanen</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>
        );
      }
    }
  ];

  return (
    <ModernDataTable 
      data={data} 
      columns={columns} 
      title="Daftar Shift Terdaftar" 
      searchPlaceholder="Cari kode shift..." 
    />
  );
}
