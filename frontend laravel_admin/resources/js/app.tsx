import "./bootstrap";
import React, { Suspense, lazy } from "react";
import { createRoot } from "react-dom/client";

// Lazy Load Tables to reduce initial bundle size
const EmployeesTable = lazy(() => import("./components/tables/EmployeesTable").then(m => ({ default: m.EmployeesTable })));
const UsersTable = lazy(() => import("./components/tables/UsersTable").then(m => ({ default: m.UsersTable })));
const HistoryTable = lazy(() => import("./components/tables/HistoryTable").then(m => ({ default: m.HistoryTable })));
const FtwTable = lazy(() => import("./components/tables/FtwTable").then(m => ({ default: m.FtwTable })));

const components: Record<string, React.ComponentType<any>> = {
  "employees-table": EmployeesTable,
  "users-table": UsersTable,
  "history-table": HistoryTable,
  "ftw-table": FtwTable,
};

document.addEventListener("DOMContentLoaded", () => {
  const mountPoints = document.querySelectorAll("[data-react-component]");
  
  mountPoints.forEach((mount) => {
    const componentName = mount.getAttribute("data-react-component");
    const Component = components[componentName as string];
    
    if (Component) {
      try {
        const props = JSON.parse(mount.getAttribute("data-props") || "{}");
        const root = createRoot(mount);
        
        root.render(
          <Suspense fallback={
            <div className="w-full h-64 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 flex items-center justify-center">
              <div className="flex flex-col items-center gap-3">
                <div className="w-8 h-8 border-2 border-indigo-500/20 border-t-indigo-500 rounded-full animate-spin"></div>
                <span className="text-[10px] text-slate-400 font-black tracking-widest uppercase">Memuat Komponen...</span>
              </div>
            </div>
          }>
            <Component {...props} />
          </Suspense>
        );
      } catch (err) {
        console.error("Failed to hydrate React component:", componentName, err);
      }
    }
  });
});
