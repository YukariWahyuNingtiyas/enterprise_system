<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Smart Enterprise Ecosystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--p1:#4f46e5;--p2:#7c3aed;--ink:#1f2937;--muted:#6b7280}
        *{box-sizing:border-box}
        body{margin:0;font-family:'Inter',system-ui,sans-serif;color:var(--ink);min-height:100vh;display:grid;place-items:center;padding:1.5rem;
            background:radial-gradient(700px 500px at 10% 0,rgba(217,70,239,.35),transparent 60%),
                       radial-gradient(800px 600px at 100% 100%,rgba(56,189,248,.28),transparent 60%),
                       linear-gradient(135deg,#1e1b4b,#4338ca 55%,#7c3aed);overflow-x:hidden}
        .blob{position:fixed;border-radius:50%;filter:blur(2px);opacity:.14;background:#fff;pointer-events:none;animation:melayang 12s ease-in-out infinite}
        .b1{width:320px;height:320px;left:-90px;bottom:-90px}.b2{width:200px;height:200px;right:8%;top:6%;animation-delay:-4s}
        @keyframes melayang{50%{transform:translateY(-22px)}}
        .shell{width:100%;max-width:960px;display:grid;grid-template-columns:1.05fr 1fr;border-radius:1.6rem;overflow:hidden;
            background:#fff;box-shadow:0 30px 70px rgba(15,10,60,.45);position:relative;z-index:1;animation:masuk .6s ease both}
        @keyframes masuk{from{opacity:0;transform:translateY(20px) scale(.98)}to{opacity:1;transform:none}}
        .side{position:relative;overflow:hidden;color:#fff;padding:3rem 2.6rem;display:flex;flex-direction:column;justify-content:center;
            background:linear-gradient(160deg,#312e81,#6d28d9 70%,#a21caf)}
        .side::before,.side::after{content:"";position:absolute;border-radius:50%;background:rgba(255,255,255,.1)}
        .side::before{width:260px;height:260px;right:-90px;top:-80px}.side::after{width:180px;height:180px;left:-60px;bottom:-70px}
        .side>*{position:relative;z-index:1}
        .logo{width:64px;height:64px;border-radius:1.1rem;display:grid;place-items:center;font-size:2rem;margin-bottom:1.4rem;
            background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.28);backdrop-filter:blur(4px)}
        .side h1{font-weight:800;font-size:2rem;line-height:1.15;margin:0 0 .8rem;letter-spacing:-.02em}
        .side p{color:rgba(255,255,255,.85);line-height:1.65;margin:0 0 1.6rem}
        .fitur{list-style:none;padding:0;margin:0;display:grid;gap:.7rem}
        .fitur li{display:flex;align-items:center;gap:.75rem;font-size:.92rem;padding:.65rem .9rem;border-radius:.85rem;
            background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.12)}
        .fitur i{font-size:1.1rem;color:#f0abfc}
        .form-side{padding:3rem 2.8rem;display:flex;flex-direction:column;justify-content:center}
        .form-side h2{font-weight:800;font-size:1.75rem;margin:0 0 .3rem;letter-spacing:-.02em}
        .sub{color:var(--muted);margin-bottom:1.8rem}
        .form-label{font-weight:600;font-size:.86rem;margin-bottom:.4rem}
        .input-group-text{background:#f5f6fc;border-color:#dfe3ec;color:#6366f1;padding:0 1rem}
        .form-control{padding:.78rem 1rem;border-color:#dfe3ec;font-size:.95rem}
        .input-group>.form-control:not(:first-child){border-left:0}
        .input-group:focus-within .input-group-text,.input-group:focus-within .form-control,.input-group:focus-within .btn{border-color:#818cf8}
        .input-group:focus-within{box-shadow:0 0 0 .22rem rgba(99,102,241,.18);border-radius:.5rem}
        .form-control:focus{box-shadow:none}
        .btn-eye{border:1px solid #dfe3ec;border-left:0;background:#fff;color:#6b7280}
        .btn-masuk{border:0;border-radius:.8rem;padding:.85rem;font-weight:700;color:#fff;font-size:1rem;width:100%;
            background:linear-gradient(90deg,var(--p1),var(--p2),#c026d3);background-size:200% 100%;transition:background-position .4s,transform .15s,box-shadow .2s;
            box-shadow:0 10px 24px rgba(79,70,229,.4)}
        .btn-masuk:hover{background-position:100% 0;transform:translateY(-1px);box-shadow:0 14px 28px rgba(79,70,229,.5)}
        .btn-masuk:disabled{opacity:.75}
        .alert-err{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:.8rem;padding:.8rem 1rem;font-size:.9rem;margin-bottom:1.2rem;display:flex;gap:.6rem}
        .foot{margin-top:1.6rem;text-align:center;font-size:.8rem;color:#9ca3af}
        @media(max-width:820px){.shell{grid-template-columns:1fr}.side{padding:2rem 1.6rem}.fitur{display:none}.form-side{padding:2rem 1.6rem}}
    </style>
</head>
<body>
<div class="blob b1"></div><div class="blob b2"></div>

<div class="shell">
    <div class="side">
        <div class="logo"><i class="bi bi-diagram-3-fill"></i></div>
        <h1>Smart Enterprise<br>Ecosystem</h1>
        <p>ERP, CRM, dan ESCM dalam satu sistem terpadu dengan dukungan analitik kecerdasan buatan.</p>
        <ul class="fitur">
            <li><i class="bi bi-cash-coin"></i> Finance, Invoice, HR, Payroll</li>
            <li><i class="bi bi-people-fill"></i> Lead, Pipeline, Pesanan</li>
            <li><i class="bi bi-box-seam-fill"></i> Inventory, Vendor, Purchase Order</li>
            <li><i class="bi bi-stars"></i> Lead Scoring, Anomali, Forecasting</li>
        </ul>
    </div>

    <div class="form-side">
        <h2>Selamat datang 👋</h2>
        <div class="sub">Masuk menggunakan akun perusahaan Anda.</div>

        @if (request('timeout'))
            <div class="alert-err" style="background:#fffbeb;border-color:#fde68a;color:#92400e"><i class="bi bi-clock-history"></i><div>Sesi berakhir karena tidak ada aktivitas selama 15 menit. Silakan masuk kembali.</div></div>
        @endif

        @if ($errors->any())
            <div class="alert-err"><i class="bi bi-exclamation-octagon-fill"></i><div>{{ $errors->first() }}</div></div>
        @endif

        <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login') }}" id="formLogin" novalidate>
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                           placeholder="nama@gmail.com" autocomplete="username" autofocus required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Masukkan password" autocomplete="current-password" required>
                    <button type="button" class="btn btn-eye" id="mata" tabindex="-1" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                </div>
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <button type="submit" class="btn-masuk" id="tombol"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
        </form>
        <div class="foot">&copy; {{ date('Y') }} Smart Enterprise Ecosystem</div>
    </div>
</div>

<script>
    const pw = document.getElementById('password'), mata = document.getElementById('mata');
    mata.addEventListener('click', () => {
        const buka = pw.type === 'password';
        pw.type = buka ? 'text' : 'password';
        mata.innerHTML = '<i class="bi bi-eye' + (buka ? '-slash' : '') + '"></i>';
    });
    document.getElementById('formLogin').addEventListener('submit', function (e) {
        const em = document.getElementById('email');
        if (!em.value.trim() || !pw.value) {
            e.preventDefault();
            (em.value.trim() ? pw : em).focus();
            alert('Email dan password wajib diisi.');
            return;
        }
        const b = document.getElementById('tombol');
        b.disabled = true; b.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';
    });
</script>
</body>
</html>