import React from "react";
import { ModernDataTable, ColumnDef } from "../ModernDataTable";
import { Badge } from "../ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "../ui/avatar";
import { Button } from "../ui/button";
import { Eye, Edit, Trash2 } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "../ui/tooltip";

interface Employee {
  nrp: string;
  full_name: string;
  pos_id?: number;
  div_id?: number;
  position?: { pos_name: string };
  division?: { div_name: string };
  user?: { is_active: boolean; role: string };
}

interface EmployeesTableProps {
  data: Employee[];
}

export function EmployeesTable({ data }: EmployeesTableProps) {
  const columns: ColumnDef<Employee>[] = [
    {
      header: "NRP",
      accessorKey: "nrp",
      className: "font-mono font-bold text-slate-500",
    },
    {
      header: "Identitas Karyawan",
      accessorKey: "full_name",
      cell: (emp) => (
        <div className="flex items-center gap-3">
          <Avatar className="h-9 w-9 border-2 border-white shadow-sm ring-1 ring-slate-100">
            <AvatarImage src={`https://api.dicebear.com/7.x/initials/svg?seed=${emp.full_name}`} />
            <AvatarFallback>{emp.full_name[0]}</AvatarFallback>
          </Avatar>
          <div className="flex flex-col">
            <span className="text-[14px] font-bold text-slate-800 leading-tight tracking-tight">{emp.full_name}</span>
            <span className="text-[11px] font-medium text-slate-400 mt-0.5">{emp.position?.pos_name || "Jabatan tidak diset"}</span>
          </div>
        </div>
      ),
    },
    {
      header: "Penempatan",
      accessorKey: "division",
      cell: (emp) => (
        <div className="flex flex-col">
          <Badge variant="outline" className="w-fit bg-slate-50 border-slate-200 text-slate-600 text-[10px] font-bold uppercase tracking-wider">
            {emp.division?.div_name || "Tanpa Divisi"}
          </Badge>
        </div>
      ),
    },
    {
      header: "Status",
      accessorKey: "status",
      className: "text-center",
      cell: (emp) => (
        <div className="flex justify-center">
          <Badge variant={emp.user?.is_active ? "success" : "destructive"} className="text-[10px] font-black uppercase tracking-widest px-2.5">
            {emp.user?.is_active ? "AKTIF" : "NONAKTIF"}
          </Badge>
        </div>
      ),
    },
    {
      header: "Aksi",
      accessorKey: "actions",
      className: "text-right",
      cell: (emp) => (
        <div className="flex justify-end gap-2">
          <TooltipProvider>
            <Tooltip>
               <TooltipTrigger asChild>
                 <Button variant="ghost" size="icon" className="h-8 w-8 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors"
                         onClick={() => (window as any).editEmployee(emp)}>
                   <Edit className="h-4 w-4" />
                 </Button>
               </TooltipTrigger>
               <TooltipContent>Edit Data</TooltipContent>
            </Tooltip>
            
            <Tooltip>
               <TooltipTrigger asChild>
                 <Button variant="ghost" size="icon" className="h-8 w-8 rounded-lg hover:bg-red-50 hover:text-red-600 transition-colors"
                         onClick={() => (window as any).confirmDelete(emp.nrp, emp.full_name)}>
                   <Trash2 className="h-4 w-4" />
                 </Button>
               </TooltipTrigger>
               <TooltipContent>Hapus Permanen</TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      ),
    },
  ];

  return (
    <ModernDataTable 
      data={data} 
      columns={columns} 
      title="Daftar Karyawan Terdaftar"
      searchPlaceholder="Cari NRP atau nama..." 
    />
  );
}
