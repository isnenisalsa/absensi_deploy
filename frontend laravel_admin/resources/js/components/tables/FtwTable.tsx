import React from "react";
import { ModernDataTable, ColumnDef } from "../ModernDataTable";
import { Badge } from "../ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "../ui/avatar";
import { HeartPulse, Moon, AlertTriangle, CheckCircle2 } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "../ui/tooltip";

interface FtwReport {
  id: number;
  nrp: string;
  ftw_date: string;
  jam_tidur_12_jam: string;
  calculated_status: string;
  unfit_reasons: string;
  employee: { full_name: string };
}

interface FtwTableProps {
  data: FtwReport[];
}

export function FtwTable({ data }: FtwTableProps) {
  const columns: ColumnDef<FtwReport>[] = [
    {
      header: "Identitas Karyawan",
      accessorKey: "employee",
      cell: (rep) => (
        <div className="flex items-center gap-3">
          <Avatar className="h-8 w-8">
            <AvatarImage src={`https://api.dicebear.com/7.x/initials/svg?seed=${rep.employee?.full_name}`} />
            <AvatarFallback>{rep.employee?.full_name?.[0] || '?'}</AvatarFallback>
          </Avatar>
          <div className="flex flex-col">
            <span className="text-[13px] font-extrabold text-slate-800 tracking-tight leading-none">{rep.employee?.full_name}</span>
            <span className="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-wider">{rep.nrp}</span>
          </div>
        </div>
      ),
    },
    {
      header: "Kualitas Istirahat",
      accessorKey: "jam_tidur_12_jam",
      cell: (rep) => (
        <div className="flex flex-col">
          <span className="text-[12px] font-black text-slate-700 flex items-center gap-1.5"><Moon className="h-3.5 w-3.5 text-indigo-500" /> {rep.jam_tidur_12_jam}</span>
          <span className="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-tighter">Durasi Tidur (12 Jam Terakhir)</span>
        </div>
      ),
    },
    {
      header: "Evaluasi Kesehatan",
      accessorKey: "status",
      className: "text-center",
      cell: (rep) => (
        <div className="flex justify-center">
          <Badge 
            variant={rep.calculated_status === 'FIT' ? "success" : "destructive"} 
            className="text-[11px] font-black uppercase tracking-widest px-3 py-1 shadow-sm flex items-center gap-1.5"
          >
            {rep.calculated_status === 'FIT' ? <CheckCircle2 className="h-3 w-3"/> : <AlertTriangle className="h-3 w-3"/>}
            {rep.calculated_status}
          </Badge>
        </div>
      ),
    },
    {
      header: "Detail Temuan (Unfit Reasons)",
      accessorKey: "unfit_reasons",
      cell: (rep) => (
        <div className={rep.calculated_status === 'UNFIT' ? "text-[11px] font-bold text-red-600 bg-red-50 p-2 rounded-lg border border-red-100 italic" : "text-[11px] font-medium text-slate-400"}>
          {rep.unfit_reasons || "-"}
        </div>
      ),
    },
  ];

  return (
    <ModernDataTable 
      data={data} 
      columns={columns} 
      title="Monitoring Fit To Work"
      searchPlaceholder="Cari Karyawan atau NRP..." 
    />
  );
}
