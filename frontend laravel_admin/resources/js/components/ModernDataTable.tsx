"use client";

import React, { useState, useMemo } from "react";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "./ui/table";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "./ui/dropdown-menu";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { Checkbox } from "./ui/checkbox";
import { cn } from "../lib/utils";
import { Search, Columns } from "lucide-react";

export type ColumnDef<T> = {
  header: string;
  accessorKey: keyof T | string;
  cell?: (item: T) => React.ReactNode;
  className?: string;
};

interface ModernDataTableProps<T> {
  data: T[];
  columns: ColumnDef<T>[];
  searchPlaceholder?: string;
  filterKey?: keyof T;
  title?: string;
  hideHeader?: boolean;
}

// Memoized Table Row for performance
const MemoizedTableRow = React.memo(({ item, columns, visibleColumns }: { item: any, columns: any[], visibleColumns: string[] }) => (
  <TableRow className="hover:bg-slate-50/50 transition-colors group border-slate-50">
    {columns.map((col) => {
      if (!visibleColumns.includes(col.header)) return null;
      return (
        <TableCell key={col.header} className={cn("py-3.5 px-6 text-[13px] text-black font-bold", col.className)}>
          {col.cell ? col.cell(item) : (item[col.accessorKey as string] ?? "-")}
        </TableCell>
      );
    })}
  </TableRow>
));

MemoizedTableRow.displayName = "MemoizedTableRow";

export function ModernDataTable<T extends { id?: string | number; [key: string]: any }>({
  data,
  columns,
  searchPlaceholder = "Search...",
  title,
  hideHeader = false
}: ModernDataTableProps<T>) {
  const [visibleColumns, setVisibleColumns] = useState<string[]>(
    columns.map((c) => c.header)
  );
  const [globalFilter, setGlobalFilter] = useState("");

  const filteredData = useMemo(() => {
    if (!globalFilter) return data;
    const lowerFilter = globalFilter.toLowerCase();
    
    return data.filter((item) => {
      const checkMatch = (obj: any): boolean => {
        return Object.values(obj).some(val => {
          if (val === null || val === undefined) return false;
          if (typeof val === 'object') return checkMatch(val);
          return String(val).toLowerCase().includes(lowerFilter);
        });
      };
      return checkMatch(item);
    });
  }, [data, globalFilter]);

  const toggleColumn = (header: string) => {
    setVisibleColumns((prev) =>
      prev.includes(header)
        ? prev.filter((c) => c !== header)
        : [...prev, header]
    );
  };

  return (
    <div className={cn(
      "w-full animate-in fade-in slide-in-from-bottom-4 duration-500",
      (title || searchPlaceholder) && !hideHeader ? "p-6 border border-slate-200 rounded-2xl bg-white shadow-sm" : ""
    )}>
      {(title || searchPlaceholder) && !hideHeader && (
        <div className="flex flex-wrap gap-4 items-center justify-between mb-4 px-1">
          <div className="flex items-center gap-3">
            {title && <h3 className="text-[15px] font-extrabold text-black tracking-tight leading-none uppercase">{title}</h3>}
            <div className="relative group">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-black group-focus-within:text-blue-500 transition-colors" />
              <Input
                placeholder={searchPlaceholder}
                className="pl-9 w-72 h-10 bg-slate-50 border-slate-200 rounded-xl text-[13px] font-bold text-black focus:bg-white focus:ring-4 focus:ring-blue-100 transition-all placeholder:font-medium"
                value={globalFilter}
                onChange={(e) => setGlobalFilter(e.target.value)}
              />
            </div>
          </div>

          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline" size="sm" className="h-10 px-4 rounded-xl border-slate-200 font-extrabold text-[12px] text-black gap-2 hover:bg-slate-50 transition-all uppercase tracking-wider">
                <Columns className="w-4 h-4" />
                Columns
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-56 p-2 rounded-xl bg-white border-slate-200 shadow-2xl z-[9999]">
              <div className="px-2 py-2 mb-1 border-b border-slate-50">
                <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Toggle Columns</span>
              </div>
              {columns.map((col) => (
                <div
                  key={col.header}
                  className="flex items-center space-x-2 p-2 hover:bg-slate-50 rounded-lg cursor-pointer transition-colors"
                  onClick={() => toggleColumn(col.header)}
                >
                  <Checkbox checked={visibleColumns.includes(col.header)} />
                  <span className="text-[11px] font-bold text-slate-700 uppercase tracking-tight">{col.header}</span>
                </div>
              ))}
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      )}

      <div className="relative w-full overflow-x-auto rounded-xl border border-slate-100 shadow-sm bg-white">
        <Table className="w-full border-collapse">
          <TableHeader className="bg-slate-50/80 border-b border-slate-100 italic-none">
            <TableRow className="hover:bg-transparent border-none">
              {columns.map((col) => {
                if (!visibleColumns.includes(col.header)) return null;
                return (
                  <TableHead 
                    key={col.header} 
                    className={cn(
                      "h-12 px-6 py-4 text-[10px] font-extrabold text-black uppercase tracking-widest whitespace-nowrap italic-none",
                      col.className
                    )}
                  >
                    <div className="flex items-center gap-2">
                       {col.header}
                    </div>
                  </TableHead>
                );
              })}
            </TableRow>
          </TableHeader>
          <TableBody>
            {filteredData.length > 0 ? (
              filteredData.map((item, idx) => (
                <MemoizedTableRow
                  key={item.id ?? idx}
                  item={item}
                  columns={columns}
                  visibleColumns={visibleColumns}
                />
              ))
            ) : (
              <TableRow>
                <TableCell
                  colSpan={columns.length}
                  className="h-48 text-center bg-slate-50/20"
                >
                  <div className="flex flex-col items-center gap-2">
                    <div className="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-2">
                      <Search className="w-6 h-6 text-black" />
                    </div>
                    <span className="text-[11px] font-extrabold text-black uppercase tracking-widest">No results found matching "{globalFilter}"</span>
                    <span className="text-[10px] text-black font-bold uppercase">Try adjusting your search terms</span>
                  </div>
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>
      
      {!hideHeader && (
        <div className="pt-4 flex items-center justify-between px-2">
          <p className="text-[10px] font-extrabold text-black uppercase tracking-widest">
            Total {filteredData.length} records detected
          </p>
          <div className="flex gap-2">
            <Button variant="outline" size="sm" className="h-8 rounded-lg border-slate-200 text-[10px] font-extrabold uppercase text-black hover:bg-slate-50">Prev</Button>
            <Button variant="outline" size="sm" className="h-8 rounded-lg border-slate-200 text-[10px] font-extrabold uppercase text-black hover:bg-slate-50">Next</Button>
          </div>
        </div>
      )}
    </div>
  );
}
