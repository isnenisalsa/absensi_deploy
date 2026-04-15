import React from "react";
import { ModernDataTable, ColumnDef } from "../ModernDataTable";
import { Badge } from "../ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "../ui/avatar";
import { Button } from "../ui/button";
import { Edit, ShieldAlert, CheckCircle2, XCircle, Trash2, ShieldCheck, ShieldX } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "../ui/tooltip";

interface User {
  nrp: string;
  role: string;
  is_active: boolean;
  employee?: {
    full_name: string;
    position?: { pos_name: string };
  };
  mitra?: {
    mitra_name: string;
  };
}

interface UsersTableProps {
  data: User[];
  userRole?: string;
  mitraId?: number | null;
}

export function UsersTable({ data, userRole, mitraId }: UsersTableProps) {
  const canManage = userRole === 'admin' || userRole === 'admin_mitra';

  const columns: ColumnDef<User>[] = [
    {
      header: "NRP (Username)",
      accessorKey: "nrp",
      className: "font-bold text-black",
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
            <span className="text-[14px] font-bold text-black leading-tight tracking-tight">{user.employee?.full_name || "Guest Account"}</span>
            <span className="text-[11px] font-bold text-black mt-0.5">
              {user.role === 'admin' ? "ARIA SYSTEM" : (user.mitra?.mitra_name || "Internal PAMA")}
            </span>
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
              <span className="flex items-center gap-1.5 text-yellow-600 font-bold text-[12px]"><XCircle className="h-4 w-4"/> NONAKTIF</span>
           }
        </div>
      ),
    },
  ];

  if (canManage) {
    columns.push({
      header: "Aksi",
      accessorKey: "actions",
      className: "text-right",
      cell: (user) => (
        <div className="flex justify-end gap-2.5">
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button 
                      variant="outline" 
                      size="icon" 
                      className={`h-9 w-9 flex items-center justify-center rounded-xl transition-all shadow-sm ${user.is_active ? "text-yellow-600 border-yellow-100 bg-yellow-50/50 hover:bg-yellow-100/50 hover:border-yellow-300" : "text-emerald-600 border-emerald-100 bg-emerald-50/50 hover:bg-emerald-100/50 hover:border-emerald-300"}`}
                      onClick={() => (window as any).toggleUserStatus(user.nrp, !user.is_active)}
                  >
                      {user.is_active ? <ShieldX className="h-4 w-4" /> : <ShieldCheck className="h-4 w-4" />}
                  </Button>
                </TooltipTrigger>
                <TooltipContent side="top" className={`${user.is_active ? "bg-yellow-500 text-yellow-950" : "bg-emerald-600 text-white"} border-none text-[10px] font-black uppercase tracking-widest px-3 py-1.5 shadow-lg shadow-black/10`}>
                  <p>{user.is_active ? "Nonaktifkan Akses" : "Aktifkan Akses"}</p>
                </TooltipContent>
              </Tooltip>

              <Tooltip>
                <TooltipTrigger asChild>
                  <Button 
                      variant="outline" 
                      size="icon" 
                      className="h-9 w-9 flex items-center justify-center rounded-xl transition-all text-red-600 border-red-100 bg-red-50/50 hover:bg-red-100/50 hover:border-red-300 shadow-sm"
                      onClick={() => (window as any).deleteUser(user.nrp)}
                  >
                      <Trash2 className="h-4 w-4" />
                  </Button>
                </TooltipTrigger>
                <TooltipContent side="top" className="bg-red-600 text-white border-none text-[10px] font-bold uppercase tracking-widest px-3 py-1.5" style={{ zIndex: 9999 }}>
                  <p>Hapus Permanen</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
        </div>
      ),
    });
  }

  return (
    <ModernDataTable 
      data={data} 
      columns={columns} 
      searchPlaceholder="Cari NRP atau nama..." 
    />
  );
}
