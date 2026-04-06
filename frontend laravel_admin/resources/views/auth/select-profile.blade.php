<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Presensi Subcontractor - Select Profile</title>
    <!-- Tailwind CSS CDN -->
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .bg-pattern {
            background-image: radial-gradient(#d1d5db 1.2px, transparent 1.2px);
            background-size: 24px 24px;
        }

        .main-card {
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.05);
            border-radius: 1.5rem;
        }

        .info-card {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border-radius: 1rem;
        }
    </style>
</head>

<body class="bg-[#f8f9fb] bg-pattern min-h-screen flex flex-col">

    <!-- Top Navbar -->
    <header
        class="bg-white/80 backdrop-blur-md border-b border-gray-100 py-4 px-8 flex justify-between items-center z-10 w-full">
        <div class="flex items-center gap-3">
            <i  class="fa-solid fa-building w-6 h-6 text-gray-500" ></i>
            <span class="font-extrabold text-[#2c3e50] tracking-wide uppercase text-sm">PAMA SUBCONTRACTOR PORTAL</span>
        </div>
        <div class="flex items-center gap-6 text-gray-400">
            <button class="hover:text-gray-700 transition-colors"><i  class="fa-solid fa-circle-question w-5 h-5"
                    ></i></button>
            <button class="hover:text-gray-700 transition-colors"><i  class="fa-solid fa-bell w-5 h-5" ></i></button>
        </div>
    </header>

    <main class="flex-grow flex flex-col items-center justify-center p-4 w-full">
        <!-- Main Select Profile Card -->
        <div
            class="main-card bg-white w-full max-w-4xl p-10 md:p-14 flex flex-col items-center relative z-10 mb-6 mt-6">

            <!-- Logo area -->
            <div class="mb-4">
                <i  class="fa-solid fa-building w-12 h-12 text-blue-600" ></i>
            </div>

            <h1 class="text-[#2c3e50] text-3xl font-extrabold tracking-tight uppercase mb-2">PRESENSI SUBCONTRACTOR</h1>
            <p class="text-gray-400 text-xs font-bold tracking-widest uppercase mb-12">SELECT PROFILE</p>

            <form action="{{ route('select-profile.post') }}" method="POST" class="w-full">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

                    <!-- USERNAME / PNRP Readonly -->
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold text-gray-400 uppercase mb-2 ml-1">USERNAME / PNRP</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <i  class="fa-solid fa-user w-4 h-4" ></i>
                            </span>
                            <input readonly type="text" value="{{ session('user.nrp') ?? '80123456' }}"
                                class="w-full bg-[#f8f9fb] border border-gray-100 rounded-lg py-3.5 pl-11 pr-4 text-sm text-[#2c3e50] font-bold focus:ring-0 cursor-default outline-none" />
                        </div>
                    </div>

                    <!-- PROFILE SELECT -->
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold text-gray-400 uppercase mb-2 ml-1">PROFILE</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <i  class="fa-solid fa-briefcase w-4 h-4" ></i>
                            </span>
                            <select name="profile" required
                                class="w-full bg-white border border-gray-200 rounded-lg py-3.5 pl-11 pr-10 text-sm text-black invalid:text-gray-400 font-medium focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none appearance-none cursor-pointer transition-colors">
                                <option value="" disabled selected>[SELECT]</option>
                                <option value="Admin" class="text-black">Admin</option>
                            </select>

                        </div>
                        @error('profile')
                            <span class="text-xs text-red-500 mt-1 ml-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- SITE SELECT -->
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold text-gray-400 uppercase mb-2 ml-1">SITE</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-gray-400">
                                <i  class="fa-solid fa-map-pin w-4 h-4" ></i>
                            </span>
                            <select name="site" required
                                class="w-full bg-white border border-gray-200 rounded-lg py-3.5 pl-11 pr-10 text-sm text-black invalid:text-gray-400 font-medium focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none appearance-none cursor-pointer transition-colors">
                                <option value="" disabled selected>[SELECT]</option>
                                <option value="ARIA" class="text-black">ARIA</option>
                            </select>

                        </div>
                        @error('site')
                            <span class="text-xs text-red-500 mt-1 ml-1">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                    <button type="submit"
                        class="bg-[#007bff] hover:bg-[#0069d9] text-white font-bold py-3.5 px-10 rounded-lg flex items-center justify-center gap-2 transition-colors shadow-lg shadow-blue-200 w-full sm:w-auto min-w-[200px]">
                        <span class="uppercase tracking-wider text-sm">Select Profile</span>
                        <i  class="fa-solid fa-arrow-right w-4 h-4" ></i>
                    </button>
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 font-bold py-3.5 px-10 rounded-lg flex items-center justify-center transition-colors w-full sm:w-auto min-w-[200px]">
                        <span class="uppercase tracking-wider text-sm">Cancel</span>
                    </a>
                </div>
            </form>
            <!-- Hidden logout form for Cancel button -->
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>

        <!-- Info Cards Row -->
        <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
            <!-- Security Status -->
            <div class="info-card bg-white p-6 flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
                    <i  class="fa-solid fa-shield w-5 h-5" ></i>
                </div>
                <div>
                    <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">SECURITY STATUS
                    </div>
                    <div class="text-xs font-extrabold text-[#2c3e50]">Secure Connection Active</div>
                </div>
            </div>

            <!-- Last Access -->
            <div class="info-card bg-white p-6 flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-full bg-blue-100/50 text-gray-600 flex items-center justify-center shrink-0">
                    <i  class="fa-solid fa-clock w-5 h-5" ></i>
                </div>
                <div>
                    <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">LAST ACCESS</div>
                    <div class="text-xs font-extrabold text-[#2c3e50]" id="realtimeWitaClock">{{ \Carbon\Carbon::now('Asia/Makassar')->translatedFormat('l, H:i:s') }} WITA</div>
                </div>
            </div>

            <!-- System Support -->
            <div class="info-card bg-white p-6 flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center shrink-0">
                    <i  class="fa-solid fa-headset w-5 h-5" ></i>
                </div>
                <div>
                    <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">SYSTEM SUPPORT</div>
                    <div class="text-xs font-extrabold text-[#2c3e50]">Helpdesk 24/7 Available</div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer
        class="bg-transparent py-6 px-10 flex flex-col md:flex-row justify-between items-center text-[10px] font-bold text-gray-400 tracking-wider z-10 w-full mt-auto">
        <div class="mb-4 md:mb-0 uppercase">
            © 2026 PAMA SUBCONTRACTOR PORTAL. ALL RIGHTS RESERVED.
        </div>
        <nav class="flex gap-8 uppercase">
            <a class="hover:text-gray-600 transition-colors" href="#">Privacy Policy</a>
            <a class="hover:text-gray-600 transition-colors" href="#">Terms of Service</a>
            <a class="hover:text-gray-600 transition-colors" href="#">Security Audit</a>
        </nav>
    </footer>

    <script>
        lucide.createIcons();

        function updateRealtimeClock() {
            // Options for Asia/Makassar (WITA)
            const options = { 
                timeZone: 'Asia/Makassar', 
                weekday: 'long', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit', 
                hour12: false 
            };
            const formatter = new Intl.DateTimeFormat('en-US', options);
            const formatted = formatter.format(new Date());
            // formatted will look like "Thursday, 14:35:25"
            document.getElementById('realtimeWitaClock').textContent = formatted + ' WITA';
        }
        
        setInterval(updateRealtimeClock, 1000);
        updateRealtimeClock(); // init immediately
    </script>
</body>

</html>