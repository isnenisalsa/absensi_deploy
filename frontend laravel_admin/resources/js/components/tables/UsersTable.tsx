import React from "react";
import { ModernDataTable, ColumnDef } from "../ModernDataTable";
import { Badge } from "../ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "../ui/avatar";
import { Button } from "../ui/button";
import { Edit, ShieldAlert, CheckCircle2, XCircle } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "../ui/tooltip";

interface User {
  nrp: string;
  role: string;
  is_active: boolean;
  employee?: {
    full_name: string;
    position?: { pos_name: string };
  };
}

interface UsersTableProps {
  data: User[];
}

export function UsersTable({ data }: UsersTableProps) {
  const columns: ColumnDef<User>[] = [
    {
      header: "NRP (Username)",
      accessorKey: "nrp",
      className: "font-mono font-bold text-slate-500",
    },
    {
      header: "Profil Pengguna",
      accessorKey: "employee",
      cell: (user) => (
        <div className="flex items-center gap-3">
          <Avatar className="h-9 w-9 border-2 border-white shadow-sm ring-1 ring-slate-100">
            <AvatarImage src={`https://api.dicebear.com/7.x/initials/svg?seed=${user.employee?.full_name || user.nrp}`} />
            <AvatarFallback>{(user.employee?.full_name || user.nrp)[0]}</AvatarFallback>
          </Avatar>
          <div className="flex flex-col">
            <span className="text-[14px] font-bold text-slate-800 leading-tight tracking-tight">{user.employee?.full_name || "Belum ada nama"}</span>
            <span className="text-[11px] font-medium text-slate-400 mt-0.5">{user.employee?.position?.pos_name || "Bukan karyawan"}</span>
          </div>
        </div>
      ),
    },
    {
      header: "Hak Akses (Role)",
      accessorKey: "role",
      cell: (user) => (
        <Badge variant={user.role === 'admin' ? "destructive" : "secondary"} className="text-[10px] font-black uppercase tracking-widest px-2.5">
           {user.role}
        </Badge>
      ),
    },
    {
      header: "Status Login",
      accessorKey: "status",
      className: "text-center",
      cell: (user) => (
        <div className="flex justify-center">
           {user.is_active ? 
              <span className="flex items-center gap-1.5 text-emerald-600 font-bold text-[12px]"><CheckCircle2 className="h-4 w-4"/> Aktif</span> : 
              <span className="flex items-center gap-1.5 text-slate-400 font-bold text-[12px]"><XCircle className="h-4 w-4"/> Suspend</span>
           }
        </div>
      ),
    },
    {
      header: "Aksi",
      accessorKey: "actions",
      className: "text-right",
      cell: (user) => (
        <div className="flex justify-end gap-2">
          <Button variant="outline" size="sm" className={user.is_active ? "text-red-500 border-red-100 hover:bg-red-50" : "text-emerald-500 border-emerald-100 hover:bg-emerald-50"}
                  onClick={() => (window as any).toggleUserStatus(user.nrp, !user.is_active)}>
            {user.is_active ? "Nonaktifkan" : "Aktifkan"}
          </Button>
        </div>
      ),
    },
  ];

  return (
    <ModernDataTable 
      data={data} 
      columns={columns} 
      title="Manajemen Akses Dashboard"
      searchPlaceholder="Cari NRP atau nama..." 
    />
  );
}
