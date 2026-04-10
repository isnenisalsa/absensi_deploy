<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Presensi Subcontractor - Login</title>
<!-- Tailwind CSS CDN -->
    @vite(['resources/css/app.css'])
<!-- Google Fonts: Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<!-- Lucide Icons for UI elements -->
<script src="https://unpkg.com/lucide@latest"></script>
<style data-purpose="typography">
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
<style data-purpose="custom-layout">
    .bg-pattern {
      background-image: radial-gradient(#d1d5db 1.2px, transparent 1.2px);
      background-size: 24px 24px;
    }
    .login-card {
      box-shadow: 0 10px 50px rgba(0, 0, 0, 0.05);
      border-radius: 2.5rem;
    }
    .input-field-container {
      background-color: #fcfdfe;
    }
    .domain-field {
      background-color: #f1f4ff;
    }
    .footer-border {
      border-top: 1px solid #e5e7eb;
    }
  </style>
</head>
<body class="bg-[#f8f9fb] bg-pattern min-h-screen flex flex-col">
<!-- BEGIN: MainContent -->
<main class="flex-grow flex items-center justify-center p-4">
<!-- BEGIN: LoginCard -->
<div class="login-card bg-white w-full max-w-4xl p-12 md:p-20 flex flex-col items-center" data-purpose="login-container">
<!-- Logo Section -->
<!-- 
  [PLACEHOLDER LOGO] 
  Saat Anda sudah memiliki logo, hapus div di bawah ini dan gunakan tag <img> yang saat ini di-comment.
  Ganti atribut 'src' dengan path logo Anda (misal: asset('images/logo.png')).
-->
<div class="mb-8 flex justify-center w-full">
    <div class="h-32 w-auto flex flex-col items-center justify-center p-2 rounded-2xl bg-slate-50/50 border border-slate-100 shadow-sm overflow-hidden">
        <img alt="Company Logo" class="h-24 w-auto object-contain transition-transform duration-500 hover:scale-105" src="{{ asset('assets/images/logo-pama.png') }}" onerror="this.src='https://ui-avatars.com/api/?name=PAMA&background=0052cc&color=fff&size=200&bold=true'" />
    </div>
</div>

<!-- Header Section -->
<div class="text-center mb-10">
<h1 class="text-[#2c3e50] text-3xl font-extrabold tracking-tight uppercase mb-2">Presensi Subcontractor</h1>
<p class="text-gray-400 text-xs font-bold tracking-widest uppercase">Sign In To Continue</p>
</div>
<!-- Form Section -->
<form id="loginForm" action="{{ route('login.post') }}" class="w-full" method="POST">
@csrf

<!-- Menampilkan error jika login gagal -->
@if ($errors->any())
<div class="bg-red-100 text-red-600 p-3 rounded-lg text-xs font-bold mb-4">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
<!-- Username Field -->
<div class="flex flex-col">
<label class="text-[10px] font-extrabold text-slate-500 uppercase mb-2 ml-1 tracking-widest flex items-center gap-2">
    Username / PNRP
</label>
<div id="usernameError" class="text-[9px] font-bold text-red-500 mb-1 hidden">Silahkan isi username dengan benar</div>
<div class="relative flex items-center">
<i data-lucide="user" class="absolute left-4 w-4 h-4 text-slate-400"></i>
<input id="nrpInput" name="nrp" value="{{ old('nrp') }}" class="input-field-container w-full border border-gray-100 rounded-xl py-4 pl-11 pr-5 text-[13px] font-bold text-slate-800 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white outline-none transition-all placeholder:text-gray-400" placeholder="Silahkan Isi Username" type="text"/>
</div>
</div>

<!-- Password Field -->
<div class="flex flex-col">
<label class="text-[10px] font-extrabold text-slate-500 uppercase mb-2 ml-1 tracking-widest flex items-center gap-2">
    Password
</label>
<div id="passwordError" class="text-[9px] font-bold text-red-500 mb-1 hidden">Silahkan isi password dengan benar</div>
<div class="relative flex items-center">
<i data-lucide="lock" class="absolute left-4 w-4 h-4 text-slate-400"></i>
<input id="passwordInput" name="password" class="input-field-container w-full border border-gray-100 rounded-xl py-4 pl-11 pr-12 text-[13px] font-bold text-slate-800 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white outline-none transition-all placeholder:text-gray-400" type="password" placeholder="Silahkan Isi Password"/>
<button id="togglePassword" class="absolute right-4 text-slate-400 hover:text-blue-600 transition-colors focus:outline-none" type="button" aria-label="Toggle password visibility">
<i id="toggleIcon" data-lucide="eye" class="w-4 h-4"></i>
</button>
</div>
</div>

<!-- Domain Field (Disabled/Pre-filled style) -->
<div class="flex flex-col">
<label class="text-[10px] font-extrabold text-slate-500 uppercase mb-2 ml-1 tracking-widest flex items-center gap-2">
    Domain Portal
</label>
<div class="relative flex items-center">
<i data-lucide="briefcase" class="absolute left-4 w-4 h-4 text-slate-400"></i>
<input class="input-field-container w-full border border-gray-100 rounded-xl py-4 pl-11 pr-5 text-[13px] font-bold text-slate-800 bg-slate-100/80 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 cursor-default outline-none transition-all" readonly="" type="text" value="Pamapersada"/>
</div>
</div>

</div>
<!-- Login Button -->
<div class="flex flex-col items-center">
<button class="bg-[#007bff] hover:bg-[#0069d9] text-white font-bold py-4 px-16 rounded-lg flex items-center justify-center gap-2 transition-colors shadow-lg shadow-blue-200 w-full md:w-auto min-w-[280px]" type="submit">
<span class="uppercase tracking-wider text-sm">Log In</span>
<i data-lucide="arrow-right" class="w-4 h-4"></i>
</button>
<a class="mt-6 text-[#5c6ea3] hover:text-blue-700 text-[10px] font-bold tracking-widest uppercase transition-colors" href="#">
            Forgot Password?
          </a>
</div>
</form>
</div>
<!-- END: LoginCard -->
</main>
<!-- END: MainContent -->
<!-- BEGIN: MainFooter -->
<footer class="footer-border bg-white py-8 px-10 flex flex-col md:flex-row justify-between items-center text-[10px] font-bold text-gray-400 tracking-wider">
<div class="mb-4 md:mb-0 uppercase">
      © 2026 PAMA SUBCONTRACTOR PORTAL. ALL RIGHTS RESERVED.
    </div>
<nav class="flex gap-8 uppercase">
<a class="hover:text-gray-600 transition-colors" href="#">Privacy Policy</a>
<a class="hover:text-gray-600 transition-colors" href="#">Terms of Service</a>
<a class="hover:text-gray-600 transition-colors" href="#">Security Audit</a>
</nav>
</footer>
<!-- END: MainFooter -->
<!-- Initialize Icons -->
<script data-purpose="icon-initialization">
    // Refresh the initial icons
    lucide.createIcons();

    // Toggle Password Visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const toggleIcon = document.getElementById('toggleIcon');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            // Check the current type and toggle
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

            // Change icon attribute
            toggleIcon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
            
            // Re-render only this icon holder if possible, or all
            lucide.createIcons();
        });
    }

    // Form Validation
    const loginForm = document.getElementById('loginForm');
    const nrpInput = document.getElementById('nrpInput');
    const usernameError = document.getElementById('usernameError');
    const passwordError = document.getElementById('passwordError');

    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            let isValid = true;

            // Check Username
            if (!nrpInput.value.trim()) {
                usernameError.classList.remove('hidden');
                nrpInput.classList.add('border-red-500');
                isValid = false;
            } else {
                usernameError.classList.add('hidden');
                nrpInput.classList.remove('border-red-500');
            }

            // Check Password
            if (!passwordInput.value.trim()) {
                passwordError.classList.remove('hidden');
                passwordInput.classList.add('border-red-500');
                isValid = false;
            } else {
                passwordError.classList.add('hidden');
                passwordInput.classList.remove('border-red-500');
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }
  </script>
</body></html>
