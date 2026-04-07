import React from "react";
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
        const timeVal = att.time_wita.includes(':') ? att.time_wita.substring(0, 8) : new Date(att.time_wita).toLocaleTimeString('en-GB');
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
      searchPlaceholder="Search Attendance Records..." 
      hideHeader={true}
    />
  );
}
