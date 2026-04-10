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
  mitra?: { mitra_name: string };
  user?: { is_active: boolean; role: string };
  mitra_id?: number | null;
  location_id?: number | null;
}

interface EmployeesTableProps {
  data: Employee[];
  userRole?: string;
  mitraId?: number | null;
}

export function EmployeesTable({ data, userRole, mitraId }: EmployeesTableProps) {
  const isSuperAdmin = userRole === 'admin' && (mitraId === null || mitraId === undefined);

  const columns: ColumnDef<Employee>[] = [
    {
      header: "NRP",
      accessorKey: "nrp",
      className: "font-bold text-black",
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
            <span className="text-[14px] font-bold text-black leading-tight tracking-tight">{emp.full_name}</span>
            <span className="text-[11px] font-bold text-black mt-0.5">{emp.position?.pos_name || "Jabatan tidak diset"}</span>
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
      header: "Mitra Kerja",
      accessorKey: "mitra",
      cell: (emp) => (
        <div className="flex flex-col">
          <Badge variant="secondary" className="w-fit bg-indigo-50 border-indigo-100 text-indigo-700 text-[10px] font-black uppercase tracking-widest px-2 py-0.5">
            {emp.mitra?.mitra_name || "Internal PAMA"}
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
    ...(isSuperAdmin ? [{
      header: "Aksi",
      accessorKey: "actions",
      className: "text-right",
      cell: (emp: Employee) => (
        <div className="flex justify-end gap-2.5">
          <TooltipProvider>
            <Tooltip>
               <TooltipTrigger asChild>
                 <Button 
                    variant="outline" 
                    size="icon" 
                    className="h-9 w-9 rounded-xl transition-all text-blue-600 border-blue-100 bg-blue-50/50 hover:bg-blue-100/50 hover:border-blue-300 shadow-sm"
                    onClick={() => (window as any).editEmployee(emp)}
                 >
                   <Edit className="h-4 w-4" />
                 </Button>
               </TooltipTrigger>
               <TooltipContent side="top" className="bg-blue-600 text-white border-none text-[10px] font-black uppercase tracking-widest px-3 py-1.5 shadow-lg shadow-black/10">
                 <p>Edit Data Pekerja</p>
               </TooltipContent>
            </Tooltip>
            
            <Tooltip>
               <TooltipTrigger asChild>
                 <Button 
                    variant="outline" 
                    size="icon" 
                    className="h-9 w-9 rounded-xl transition-all text-red-600 border-red-100 bg-red-50/50 hover:bg-red-100/50 hover:border-red-300 shadow-sm"
                    onClick={() => (window as any).confirmDelete(emp.nrp, emp.full_name)}
                 >
                   <Trash2 className="h-4 w-4" />
                 </Button>
               </TooltipTrigger>
               <TooltipContent side="top" className="bg-red-600 text-white border-none text-[10px] font-black uppercase tracking-widest px-3 py-1.5 shadow-lg shadow-black/10">
                 <p>Hapus Permanen</p>
               </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      ),
    }] : []),
  ];

  return (
    <ModernDataTable 
      data={data} 
      columns={columns} 
      searchPlaceholder="Cari NRP atau nama..." 
    />
  );
}
