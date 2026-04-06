<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Presensi Subcontractor - @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Loading Overlay Styling */
        #globalLoading {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(255, 255, 255, 0.95);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        .loader-spinner {
            width: 48px; height: 48px; border: 5px solid #f3f3f3; border-top: 5px solid #0052cc;
            border-radius: 50%; animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* CSS for Modern State-based Sidebar */
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; display: none; }
        .sidebar-scroll:hover::-webkit-scrollbar-thumb { display: block; }

        /* Sidebar State Classes */
        #mainSidebar.sidebar-collapsed { width: 80px !important; }
        #mainSidebar.sidebar-collapsed .nav-label,
        #mainSidebar.sidebar-collapsed .section-label,
        #mainSidebar.sidebar-collapsed #brandText {
            width: 0; opacity: 0; display: none;
        }
        #mainSidebar.sidebar-collapsed #sidebarSearch { opacity: 0; pointer-events: none; height: 0; padding: 0; margin: 0; overflow: hidden; }
        
        /* Fixed bottom behavior */
        #mainSidebar.sidebar-collapsed #sidebarProfile {
            padding: 12px 6px;
            display: flex;
            justify-content: center;
        }
        #mainSidebar.sidebar-collapsed #sidebarProfile > div {
            border-color: transparent;
            background-color: transparent;
            padding: 0;
            width: 44px;
            justify-content: center;
        }
        #mainSidebar.sidebar-collapsed #sidebarProfile img { width: 36px; height: 36px; }
        #mainSidebar.sidebar-collapsed #btn-logout-text { display: none; }
        #mainSidebar.sidebar-collapsed #btn-logout-container { justify-content: center; }
        
        /* Tooltip behavior */
        .sidebar-item .tooltip { display: none; }
        #mainSidebar.sidebar-collapsed .sidebar-item:hover .tooltip { 
            display: block; opacity: 1; visibility: visible; 
            left: 85px; top: 50%; transform: translateY(-50%);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Custom Tom Select Styling */
        .ts-control { border-radius: 0.75rem !important; padding: 0.625rem 1rem !important; border: 1px solid #e2e8f0 !important; background-color: #f8fafc !important; font-size: 13px !important; font-weight: 700 !important; color: #1e293b !important; }
        .ts-control:focus { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important; border-color: #3b82f6 !important; background-color: #ffffff !important; }
        .ts-dropdown { border-radius: 0.75rem !important; margin-top: 4px !important; border: 1px solid #e2e8f0 !important; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; font-size: 13px !important; z-index: 10001 !important; }

        /* Sidebar Blur Effect */
        #mainSidebar.sidebar-blur {
            filter: blur(8px);
            pointer-events: none;
            transition: filter 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
        }

    </style>
    <script>
        // Global Helper for Sidebar Blur
        function toggleSidebarBlur(shouldBlur) {
            const sidebar = document.getElementById('mainSidebar');
            if (sidebar) {
                if (shouldBlur) sidebar.classList.add('sidebar-blur');
                else sidebar.classList.remove('sidebar-blur');
            }
        }
    </script>
    @stack('styles')
</head>
<body class="bg-[#f8f9fb] min-h-screen flex overflow-hidden">

    <!-- Global Loading Overlay -->
    <div id="globalLoading">
        <div class="relative flex items-center justify-center mb-6">
            <div class="loader-spinner"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <img src="{{ asset('assets/images/logo-pama.png') }}" onerror="this.src='https://ui-avatars.com/api/?name=PAMA&background=0052cc&color=fff&bold=true'" class="w-8 h-8 rounded-lg shadow-sm" alt="Logo"/>
            </div>
        </div>
        <div class="text-[12px] font-black text-blue-600 tracking-[0.2em] uppercase animate-pulse">Memuat Data...</div>
    </div>

    <!-- Original Collapsible Sidebar -->
    <aside id="mainSidebar" 
           class="fixed top-0 left-0 h-screen bg-white border-r border-slate-200 z-40 transition-all duration-300 ease-in-out flex flex-col -translate-x-full md:translate-x-0 md:static overflow-hidden md:w-72">
        
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50/60 h-20 shrink-0">
            <div class="flex items-center space-x-3 overflow-hidden transition-opacity duration-300" id="sidebarHeaderContent">
                <div class="w-9 h-9 bg-gradient-to-br from-[#0052cc] to-blue-500 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 shrink-0">
                    <img src="{{ asset('assets/images/logo-pama.png') }}" class="w-6 h-6 object-contain" alt="Logo"/>
                </div>
                <div class="flex flex-col whitespace-nowrap opacity-100" id="brandText">
                    <span class="font-black text-slate-800 text-[13px] uppercase tracking-wider leading-tight">PAMA SUBCONT</span>
                    <span class="text-[10px] font-bold text-slate-400">Portal Management</span>
                </div>
            </div>
            <!-- Collapse Button (Desktop Only) -->
            <button id="toggleSidebar" class="hidden md:flex p-2 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 shadow-sm transition-all transition-all duration-200 active:scale-90">
                <i id="collapseIcon" class="fa-solid fa-chevron-left text-[12px] opacity-100 transition-transform duration-300"></i>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="px-4 py-3 transition-opacity duration-300 shrink-0" id="sidebarSearch">
            <div class="relative group">
                <i class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-slate-400 group-focus-within:text-blue-500 transition-colors fa-solid fa-magnifying-glass"></i>
                <input id="sidebarSearchInput" type="text" placeholder="Search..." class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-[12px] font-bold placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all">
            </div>
        </div>

        <!-- Navigation List -->
        <nav class="flex-1 px-3 py-2 overflow-y-auto sidebar-scroll min-h-0">
            <div class="flex flex-col gap-6">
                <!-- Overview -->
                <div class="flex flex-col gap-1">
                    <div class="h-6 flex items-center px-4 overflow-hidden section-label transition-opacity duration-300">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Overview</span>
                    </div>
                    <a href="{{ route('dashboard') }}" title="Dashboard" class="sidebar-item relative flex items-center h-12 rounded-xl transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-[#0052cc]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                        <div class="w-14 h-12 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-gauge-high text-[16px]"></i>
                        </div>
                        <span class="text-[13px] font-bold whitespace-nowrap nav-label transition-opacity duration-300">Dashboard</span>
                        <div class="tooltip absolute left-full ml-3 px-2 py-1.5 bg-slate-800 text-white text-[10px] rounded-lg opacity-0 invisible group-hover:opacity-100 whitespace-nowrap z-50 pointer-events-none transition-all">Dashboard</div>
                    </a>
                </div>

                <!-- Operations / Analytics -->
                <div class="flex flex-col gap-1">
                    <div class="h-6 flex items-center px-4 overflow-hidden section-label transition-opacity duration-300">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Analytics</span>
                    </div>
                    <div class="flex flex-col">
                        <!-- Reports Main -->
                        <button onclick="toggleReportMenu()" class="sidebar-item w-full flex items-center h-12 rounded-xl transition-all duration-300 {{ request()->is('report/*') ? 'bg-blue-50 text-[#0052cc]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                            <div class="w-14 h-12 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-chart-pie text-[16px]"></i>
                            </div>
                            <span class="text-[13px] font-bold whitespace-nowrap nav-label flex-1 text-left transition-opacity duration-300">Reports</span>
                            <div class="w-10 flex items-center justify-center nav-label transition-opacity duration-300">
                                <i id="reportChevron" class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300 {{ request()->is('report/*') ? 'rotate-180' : '' }}"></i>
                            </div>
                            <div class="tooltip absolute left-full ml-3 px-2 py-1.5 bg-slate-800 text-white text-[10px] rounded-lg opacity-0 invisible group-hover:opacity-100 whitespace-nowrap z-50 pointer-events-none transition-all">Reports</div>
                        </button>
                        <div id="reportSubMenu" class="transition-all duration-300 ease-in-out {{ request()->is('report/*') ? 'grid-rows-[1fr] opacity-100 mt-1' : 'grid-rows-[0fr] opacity-0' }} grid overflow-hidden">
                            <div class="overflow-hidden flex flex-col gap-0.5 pl-14">
                                <a href="{{ route('report.history') }}" class="flex items-center h-10 rounded-xl transition-all duration-300 {{ request()->routeIs('report.history') ? 'text-[#0052cc] font-black' : 'text-slate-500 hover:text-slate-900' }}">
                                    <i class="fa-solid fa-clock-rotate-left text-[12px] mr-2"></i>
                                    <span class="text-[12px] font-bold whitespace-nowrap">Riwayat Absensi</span>
                                </a>
                                <a href="{{ route('report.ftw') }}" class="flex items-center h-10 rounded-xl transition-all duration-300 {{ request()->routeIs('report.ftw') ? 'text-[#0052cc] font-black' : 'text-slate-500 hover:text-slate-900' }}">
                                    <i class="fa-solid fa-heart-pulse text-[12px] mr-2"></i>
                                    <span class="text-[12px] font-bold whitespace-nowrap">Fit To Work (FTW)</span>
                                </a>
                            </div>
                        </div>

                        <!-- Roster Kerja -->
                        <a href="{{ route('rosters.index') }}" title="Roster Kerja" class="sidebar-item relative flex items-center h-12 rounded-xl transition-all duration-300 {{ request()->routeIs('rosters.*') ? 'bg-blue-50 text-[#0052cc]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                            <div class="w-14 h-12 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-calendar-days text-[16px]"></i>
                            </div>
                            <span class="text-[13px] font-bold whitespace-nowrap nav-label transition-opacity duration-300">Roster Kerja</span>
                            <div class="tooltip absolute left-full ml-3 px-2 py-1.5 bg-slate-800 text-white text-[10px] rounded-lg opacity-0 invisible group-hover:opacity-100 whitespace-nowrap z-50 pointer-events-none transition-all">Roster Kerja</div>
                        </a>
                    </div>
                </div>

                <!-- Management -->
                <div class="flex flex-col gap-1">
                    <div class="h-6 flex items-center px-4 overflow-hidden section-label transition-opacity duration-300">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Management</span>
                    </div>
                    <!-- Master Content -->
                    <div class="flex flex-col">
                        <button onclick="toggleMasterMenu()" class="sidebar-item w-full flex items-center h-12 rounded-xl transition-all duration-300 {{ request()->routeIs('master.*') || request()->is('report/geofence') || request()->is('employees*') ? 'bg-blue-50 text-[#0052cc]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                            <div class="w-14 h-12 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-database text-[16px]"></i>
                            </div>
                            <span class="text-[13px] font-bold whitespace-nowrap nav-label flex-1 text-left transition-opacity duration-300">Master Data</span>
                            <div class="w-10 flex items-center justify-center nav-label transition-opacity duration-300">
                                <i id="masterChevron" class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300 {{ request()->routeIs('master.*') || request()->is('employees*') || request()->is('report/geofence') ? 'rotate-180' : '' }}"></i>
                            </div>
                            <div class="tooltip absolute left-full ml-3 px-2 py-1.5 bg-slate-800 text-white text-[10px] rounded-lg opacity-0 invisible group-hover:opacity-100 whitespace-nowrap z-50 pointer-events-none transition-all">Master Data</div>
                        </button>
                        <div id="masterSubMenu" class="transition-all duration-300 ease-in-out {{ request()->routeIs('master.*') || request()->is('report/geofence') || request()->is('employees*') ? 'grid-rows-[1fr] opacity-100 mt-1' : 'grid-rows-[0fr] opacity-0' }} grid overflow-hidden">
                            <div class="overflow-hidden flex flex-col gap-0.5 pl-14">
                                <a href="{{ url('/employees') }}" class="flex items-center h-10 transition-all {{ request()->is('employees*') ? 'text-[#0052cc] font-black' : 'text-slate-500 hover:text-slate-900' }}">
                                    <i class="fa-solid fa-user-group text-[12px] mr-2"></i>
                                    <span class="text-[12px] font-bold">Karyawan</span>
                                </a>
                                <a href="{{ route('users.index') }}" class="flex items-center h-10 transition-all {{ request()->routeIs('users.index') ? 'text-indigo-600 font-black' : 'text-slate-500 hover:text-slate-900' }}">
                                    <i class="fa-solid fa-shield-halved text-[12px] mr-2"></i>
                                    <span class="text-[12px] font-bold">Akses User</span>
                                </a>
                                <a href="{{ route('master.shifts') }}" class="flex items-center h-10 transition-all {{ request()->routeIs('master.shifts') ? 'text-[#0052cc] font-black' : 'text-slate-500 hover:text-slate-900' }}">
                                    <i class="fa-solid fa-business-time text-[12px] mr-2"></i>
                                    <span class="text-[12px] font-bold">Shift</span>
                                </a>
                                <a href="{{ route('master.departments') }}" class="flex items-center h-10 transition-all {{ request()->routeIs('master.departments') ? 'text-[#0052cc] font-black' : 'text-slate-500 hover:text-slate-900' }}">
                                    <i class="fa-solid fa-sitemap text-[12px] mr-2"></i>
                                    <span class="text-[12px] font-bold">Department</span>
                                </a>
                                <a href="{{ route('master.divisions') }}" class="flex items-center h-10 transition-all {{ request()->routeIs('master.divisions') ? 'text-[#0052cc] font-black' : 'text-slate-500 hover:text-slate-900' }}">
                                    <i class="fa-solid fa-folder-tree text-[12px] mr-2"></i>
                                    <span class="text-[12px] font-bold">Divisi</span>
                                </a>
                                <a href="{{ route('master.districts') }}" class="flex items-center h-10 transition-all {{ request()->routeIs('master.districts') ? 'text-[#0052cc] font-black' : 'text-slate-500 hover:text-slate-900' }}">
                                    <i class="fa-solid fa-map-location text-[12px] mr-2"></i>
                                    <span class="text-[12px] font-bold">Distrik</span>
                                </a>
                                <a href="{{ route('master.positions') }}" class="flex items-center h-10 transition-all {{ request()->routeIs('master.positions') ? 'text-[#0052cc] font-black' : 'text-slate-500 hover:text-slate-900' }}">
                                    <i class="fa-solid fa-id-badge text-[12px] mr-2"></i>
                                    <span class="text-[12px] font-bold">Jabatan</span>
                                </a>
                                <a href="{{ route('report.geofence') }}" class="flex items-center h-10 transition-all {{ request()->routeIs('report.geofence') ? 'text-[#0052cc] font-black' : 'text-slate-500 hover:text-slate-900' }}">
                                    <i class="fa-solid fa-map-location-dot text-[12px] mr-2"></i>
                                    <span class="text-[12px] font-bold">Geofence</span>
                                </a>
                                <a href="{{ route('master.mitra') }}" class="flex items-center h-10 transition-all {{ request()->routeIs('master.mitra') ? 'text-[#0052cc] font-black' : 'text-slate-500 hover:text-slate-900' }}">
                                    <i class="fa-solid fa-handshake text-[12px] mr-2"></i>
                                    <span class="text-[12px] font-bold">Mitra Kerja</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Fixed Bottom Profile -->
        <div class="mt-auto border-t border-slate-200 shrink-0 bg-white">
            <div class="border-b border-slate-200 bg-slate-50/10 p-4 transition-all duration-300" id="sidebarProfile">
                <div class="flex items-center px-1 py-1 rounded-xl bg-white/50 border border-slate-100 hover:bg-white transition-colors duration-200 group/profile overflow-hidden">
                    <div class="relative shrink-0">
                        <img src="{{ asset('assets/images/avatar-placeholder.png') }}" 
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(session('user.employee_data.full_name') ?? 'Admin') }}&rounded=true&background=0052cc&color=fff&bold=true'" 
                             class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-sm" alt="Avatar"/>
                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white shadow-sm" title="Online"></div>
                    </div>
                    <div class="flex-1 min-w-0 ml-3 nav-label transition-opacity duration-300">
                        <p class="text-[13px] font-bold text-slate-800 truncate leading-tight">{{ session('user.employee_data.full_name') ?? 'Administrator' }}</p>
                        <p class="text-[10px] font-bold text-slate-400 truncate">{{ session('user.nrp') ?? 'SYTEM ADMIN' }}</p>
                    </div>
                </div>
            </div>

            <!-- Logout -->
            <div class="p-4">
                <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                        class="sidebar-item relative w-full flex items-center h-12 rounded-xl transition-all duration-300 text-red-500 hover:bg-red-50 hover:text-red-700" id="btn-logout-container">
                    <div class="w-14 h-12 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-right-from-bracket text-[16px]"></i>
                    </div>
                    <span class="text-[13px] font-bold whitespace-nowrap nav-label transition-opacity duration-300" id="btn-logout-text">Logout</span>
                </button>
            </div>
        </div>
    </aside>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- Main Workspace -->
    <main class="flex-1 flex flex-col relative z-0 bg-[#f8f9fb] h-screen overflow-y-auto w-full min-w-0 transition-all duration-300" id="mainContent">
        <!-- Top Mobile Header -->
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-slate-100 h-[60px] px-5 flex justify-between items-center shrink-0 w-full md:hidden">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#0052cc] to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                    <i class="fa-solid fa-bolt text-[14px]"></i>
                </div>
                <span class="font-extrabold text-[#111827] text-[13px] uppercase tracking-wider">PAMA SUBCONT</span>
            </div>
            <button class="p-2 rounded-lg bg-slate-50 text-slate-500 hover:text-blue-600 transition-colors" onclick="toggleSidebarMobile()">
                <i class="fa-solid fa-bars text-[18px]"></i>
            </button>
        </header>

        <!-- Main Payload Screen -->
        <div class="flex-1 w-full max-w-full">
            <div class="px-3 md:px-0">
                @yield('content')
            </div>
        </div>


        <!-- Footer -->
        <footer class="bg-transparent py-5 px-6 md:px-10 flex flex-col sm:flex-row justify-between items-center text-[10px] font-bold text-slate-400 tracking-wider z-20 shrink-0 uppercase w-full mt-auto mt-10">
            <div class="mb-2 sm:mb-0 text-center text-slate-400">© 2026 PAMA SUBCONTRACTOR. ALL RIGHTS SECURED.</div>
            <nav class="flex gap-6">
                <a class="hover:text-blue-600 transition-colors cursor-pointer">Privacy Policy</a>
                <a class="hover:text-blue-600 transition-colors cursor-pointer">Terms of Service</a>
            </nav>
        </footer>
    </main>

    <!-- UI Interaction Scripts -->
    <script>
        // 1. Sidebar Management System (Collapsed state from LocalStorage)
        const sidebar = document.getElementById('mainSidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        const collapseIcon = document.getElementById('collapseIcon');
        const mainContent = document.getElementById('mainContent');
        
        let isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        let isMobileOpen = false;

        function updateSidebarState() {
            if (isSidebarCollapsed) {
                sidebar.classList.add('sidebar-collapsed');
                collapseIcon.classList.add('rotate-180');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                collapseIcon.classList.remove('rotate-180');
            }
            localStorage.setItem('sidebarCollapsed', isSidebarCollapsed);
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                isSidebarCollapsed = !isSidebarCollapsed;
                updateSidebarState();
            });
        }

        // Init State
        updateSidebarState();

        function toggleSidebarMobile() {
            isMobileOpen = !isMobileOpen;
            if (isMobileOpen) {
                sidebar.classList.replace('-translate-x-full', 'translate-x-0');
            } else {
                sidebar.classList.replace('translate-x-0', '-translate-x-full');
            }
        }

        // Menu Accordion Logics
        function toggleReportMenu() {
            const menu = document.getElementById('reportSubMenu');
            const chevron = document.getElementById('reportChevron');
            toggleMenu(menu, chevron);
        }

        function toggleMasterMenu() {
            const menu = document.getElementById('masterSubMenu');
            const chevron = document.getElementById('masterChevron');
            toggleMenu(menu, chevron);
        }

        function toggleMenu(menu, chevron) {
            if (menu.classList.contains('grid-rows-[0fr]')) {
                menu.classList.replace('grid-rows-[0fr]', 'grid-rows-[1fr]');
                menu.classList.replace('opacity-0', 'opacity-100');
                menu.classList.add('mt-1');
                chevron.classList.add('rotate-180');
            } else {
                menu.classList.replace('grid-rows-[1fr]', 'grid-rows-[0fr]');
                menu.classList.replace('opacity-100', 'opacity-0');
                menu.classList.remove('mt-1');
                chevron.classList.remove('rotate-180');
            }
        }

        // Sidebar Search logic
        const sidebarSearchInput = document.getElementById('sidebarSearchInput');
        if (sidebarSearchInput) {
            sidebarSearchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase().trim();
                const sectionContainers = document.querySelectorAll('nav .flex.flex-col.gap-6 > .flex.flex-col.gap-1');
                
                sectionContainers.forEach(section => {
                    let sectionHasMatch = false;
                    const directChildren = section.querySelectorAll(':scope > a, :scope > div.flex.flex-col');
                    
                    directChildren.forEach(child => {
                        if (child.tagName === 'A') {
                            if (child.textContent.toLowerCase().includes(term)) {
                                child.style.display = 'flex';
                                sectionHasMatch = true;
                            } else {
                                child.style.display = 'none';
                            }
                        } else if (child.tagName === 'DIV') {
                            const btn = child.querySelector('button.sidebar-item');
                            const subMenu = child.querySelector('div[id$="SubMenu"]');
                            if (!btn || !subMenu) return;
                            const subLinks = subMenu.querySelectorAll('a');
                            let subHasMatch = false;
                            const btnMatch = btn.textContent.toLowerCase().includes(term);
                            subLinks.forEach(link => {
                                if (btnMatch || link.textContent.toLowerCase().includes(term)) {
                                    link.style.display = 'flex';
                                    subHasMatch = true;
                                } else {
                                    link.style.display = 'none';
                                }
                            });
                            if (btnMatch || subHasMatch) {
                                child.style.display = 'flex';
                                sectionHasMatch = true;
                                if (term !== '' && subHasMatch) {
                                    subMenu.classList.replace('grid-rows-[0fr]', 'grid-rows-[1fr]');
                                    subMenu.classList.replace('opacity-0', 'opacity-100');
                                    subMenu.classList.add('mt-1');
                                    const chev = btn.querySelector('.fa-chevron-down');
                                    if (chev) chev.classList.add('rotate-180');
                                }
                            } else {
                                child.style.display = 'none';
                            }
                        }
                    });
                    const label = section.querySelector('.section-label');
                    if (label) label.style.display = sectionHasMatch ? 'flex' : 'none';
                });
            });
        }

        window.addEventListener('load', () => {
            const loader = document.getElementById('globalLoading');
            loader.style.opacity = '0';
            setTimeout(() => { loader.style.visibility = 'hidden'; }, 500);
        });
    </script>
    @stack('scripts')
</body>
</html>
