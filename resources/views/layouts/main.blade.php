<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · Smart Enterprise Ecosystem</title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/sei.css') }}?v=4" rel="stylesheet">
</head>
<body>

@php
    $u = auth()->user();
    $peran = $u->role->nama ?? '';
    $semua = ['admin', 'sales', 'manajer_gudang', 'keuangan', 'pimpinan'];

    // [bagian, label, ikon, nama route, pola route aktif, role yang boleh]
    // Menu satu bagian harus berurutan agar dikelompokkan dengan benar.
    $menu = [
        ['Utama', 'Dashboard', 'bi-grid-1x2-fill', 'dashboard', 'dashboard', $semua],

        ['CRM', 'Leads',       'bi-person-lines-fill', 'lead.index',          'lead.*',          ['admin', 'sales', 'pimpinan']],
        ['CRM', 'Pipeline',    'bi-kanban',            'pipeline',            'pipeline',        ['admin', 'sales', 'pimpinan']],
        ['CRM', 'Pelanggan',   'bi-people-fill',       'customer.index',      'customer.*',      ['admin', 'sales', 'pimpinan']],
        ['CRM', 'Komunikasi',  'bi-chat-dots-fill',    'communication.index', 'communication.*', ['admin', 'sales', 'pimpinan']],
        ['CRM', 'Pesanan',     'bi-bag-check-fill',    'order.index',         'order.*',         ['admin', 'sales', 'pimpinan']],
        ['CRM', 'Laporan CRM', 'bi-bar-chart',         'report.crm',          'report.crm',      ['admin', 'sales', 'pimpinan']],

        ['ERP', 'Finance',  'bi-cash-stack',          'finance.index',  'finance.*',  ['admin', 'keuangan', 'pimpinan']],
        ['ERP', 'Jurnal',   'bi-journal-text',        'journal.index',  'journal.*',  ['admin', 'keuangan', 'pimpinan']],
        ['ERP', 'Invoice',  'bi-receipt',             'invoice.index',  'invoice.*',  ['admin', 'keuangan', 'sales', 'pimpinan']],
        ['ERP', 'HR',       'bi-person-badge-fill',   'employee.index', 'employee.*', ['admin', 'keuangan', 'pimpinan']],
        ['ERP', 'Payroll',  'bi-wallet2',             'payroll.index',  'payroll.*',  ['admin', 'keuangan']],
        ['ERP', 'Laporan',  'bi-bar-chart-line-fill', 'report.index',   'report.index', ['admin', 'keuangan', 'pimpinan']],

        ['ESCM', 'Inventory',           'bi-box-seam-fill',        'inventory.index', 'inventory.*', ['admin', 'manajer_gudang', 'pimpinan']],
        ['ESCM', 'Barang Masuk/Keluar', 'bi-arrow-left-right',     'movement.index',  'movement.*',  ['admin', 'manajer_gudang', 'pimpinan']],
        ['ESCM', 'Vendors',             'bi-truck',                'vendor.index',    'vendor.*',    ['admin', 'manajer_gudang', 'pimpinan']],
        ['ESCM', 'Purchase Order',      'bi-clipboard-check-fill', 'po.index',        'po.*',        ['admin', 'manajer_gudang', 'pimpinan']],
        ['ESCM', 'Pesanan Masuk',       'bi-bag-check-fill',       'order.index',     'order.*',     ['manajer_gudang']],
        ['ESCM', 'Laporan ESCM',        'bi-bar-chart',            'report.escm',     'report.escm', ['admin', 'manajer_gudang', 'pimpinan']],

        ['AI', 'Analitik AI', 'bi-stars', 'ai.index', 'ai.*', $semua],

        ['Sistem', 'Pengguna', 'bi-shield-lock-fill', 'user.index', 'user.*', ['admin']],
    ];

    // Kelompokkan menu yang boleh diakses peran ini
    $grup = [];
    foreach ($menu as [$bagian, $label, $ikon, $rute, $cocok, $boleh]) {
        if (in_array($peran, $boleh, true) && Route::has($rute)) {
            $grup[$bagian][] = ['label' => $label, 'ikon' => $ikon, 'rute' => $rute, 'cocok' => $cocok];
        }
    }
@endphp

<div class="sb-backdrop" id="sbBackdrop"></div>

<div class="app">

    <!-- ===================== SIDEBAR ===================== -->
    <aside class="sidebar" id="sidebar">

        <div class="brand">
            <i class="bi bi-diagram-3-fill"></i>
            <div><b>Smart Enterprise</b><small>Ecosystem</small></div>
        </div>

        <nav>
            @foreach($grup as $nama => $items)
                @php $aktifGrup = collect($items)->contains(fn ($i) => request()->routeIs($i['cocok'])); @endphp

                @if($nama === 'Utama')
                    @foreach($items as $i)
                        <a href="{{ route($i['rute']) }}" class="{{ request()->routeIs($i['cocok']) ? 'active' : '' }}">
                            <i class="bi {{ $i['ikon'] }}"></i> {{ $i['label'] }}
                        </a>
                    @endforeach
                @else
                    <div class="menu-group {{ $aktifGrup ? 'has-active' : '' }}" data-group="{{ $nama }}" data-active="{{ $aktifGrup ? 1 : 0 }}">
                        <button type="button" class="menu-title" aria-expanded="true">
                            <span>{{ $nama }}</span><i class="bi bi-chevron-down chev"></i>
                        </button>
                        <div class="menu-items">
                            @foreach($items as $i)
                                <a href="{{ route($i['rute']) }}" class="{{ request()->routeIs($i['cocok']) ? 'active' : '' }}">
                                    <i class="bi {{ $i['ikon'] }}"></i> {{ $i['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>
    </aside>

    {{-- Dijalankan langsung setelah sidebar agar posisi scroll & menu terbuka/tertutup
         sudah benar sebelum halaman tampil (tidak "loncat" ke atas saat pindah halaman) --}}
    <script>
    (function () {
        var sb = document.getElementById('sidebar');
        if (!sb) return;
        var st = {};
        try { st = JSON.parse(localStorage.getItem('sei.menu') || '{}'); } catch (e) {}

        sb.querySelectorAll('.menu-group').forEach(function (g) {
            var n = g.dataset.group, aktif = g.dataset.active === '1';
            var buka = aktif ? true : (n in st ? !!st[n] : false);
            g.classList.toggle('collapsed', !buka);
            g.querySelector('.menu-title').setAttribute('aria-expanded', buka ? 'true' : 'false');
        });

        try {
            var y = sessionStorage.getItem('sei.sb');
            if (y !== null) sb.scrollTop = parseInt(y, 10) || 0;
        } catch (e) {}

        var a = sb.querySelector('a.active');
        if (a) {
            var r = a.getBoundingClientRect(), s = sb.getBoundingClientRect();
            if (r.top < s.top + 70 || r.bottom > s.bottom - 30) a.scrollIntoView({ block: 'center' });
        }

        sb.addEventListener('scroll', function () {
            try { sessionStorage.setItem('sei.sb', sb.scrollTop); } catch (e) {}
        }, { passive: true });

        sb.addEventListener('click', function (e) {
            var t = e.target.closest('.menu-title');
            if (!t) return;
            var g = t.parentElement, tutup = !g.classList.contains('collapsed');
            g.classList.toggle('collapsed', tutup);
            t.setAttribute('aria-expanded', tutup ? 'false' : 'true');
            st[g.dataset.group] = !tutup;
            try { localStorage.setItem('sei.menu', JSON.stringify(st)); } catch (e2) {}
        });

        window.addEventListener('beforeunload', function () {
            try { sessionStorage.setItem('sei.sb', sb.scrollTop); } catch (e) {}
        });
    })();
    </script>

    <!-- ===================== KONTEN ===================== -->
    <div class="main">

        <div class="topbar no-print">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none" type="button" id="sbToggle" aria-label="Buka menu">
                    <i class="bi bi-list"></i>
                </button>
                <span class="text-muted d-none d-lg-inline">{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end lh-sm d-none d-sm-block">
                    <div class="fw-semibold">{{ $u->name }}</div>
                    <small class="text-muted">{{ $u->role->label ?? '-' }}</small>
                </div>
                <div class="avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</div>

                {{-- Keluar selalu meminta konfirmasi --}}
                <form method="POST" action="{{ route('logout') }}"
                      data-confirm="Anda yakin ingin keluar dari aplikasi?"
                      data-confirm-title="Keluar dari aplikasi?"
                      data-confirm-ok="Ya, keluar"
                      data-confirm-icon="warning">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm" type="submit">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </button>
                </form>
            </div>
        </div>

        <main class="content">
            <div class="page-head">
                <div>
                    <h4>@yield('title')</h4>
                    <p>@yield('subtitle')</p>
                </div>
                <div class="d-flex gap-2 flex-wrap no-print">@yield('actions')</div>
            </div>

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('scripts')

<script>
/* =====================================================================
   Skrip global: notifikasi, validasi formulir, konfirmasi, penjaga
   perubahan belum disimpan, dan perapian tabel
   ===================================================================== */
const toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3500, timerProgressBar: true });

@if(session('success'))
    toast.fire({ icon: 'success', title: @json(session('success')) });
@endif
@if(session('error'))
    Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')), confirmButtonColor: '#4f46e5' });
@endif
@if($errors->any())
    toast.fire({ icon: 'warning', title: 'Periksa kembali isian formulir' });
@endif

/* ---------- menu mobile ---------- */
(function () {
    var sb = document.getElementById('sidebar'), bd = document.getElementById('sbBackdrop'), tg = document.getElementById('sbToggle');
    function tutup() { sb.classList.remove('show'); bd.classList.remove('show'); }
    if (tg) tg.addEventListener('click', function () { sb.classList.toggle('show'); bd.classList.toggle('show'); });
    if (bd) bd.addEventListener('click', tutup);
})();

/* ---------- 1. error dari server ditampilkan di bawah kolom ---------- */
(function () {
    var errs = @json($errors->messages());
    Object.entries(errs).forEach(function (kv) {
        var name = kv[0].replace(/\.(\d+)\.(.+)/g, '[$1][$2]');
        var el = document.querySelector('[name="' + name + '"]');
        if (el && !el.classList.contains('is-invalid')) {
            el.classList.add('is-invalid');
            var d = document.createElement('div');
            d.className = 'invalid-feedback';
            d.textContent = kv[1][0];
            el.insertAdjacentElement('afterend', d);
        }
    });
})();

/* ---------- 2. validasi langsung (per kolom) dengan pesan Indonesia ---------- */
function pesanError(el) {
    var v = el.validity;
    if (v.valueMissing)   return el.type === 'checkbox' ? 'Centang kolom ini untuk melanjutkan.'
                              : (el.tagName === 'SELECT' ? 'Pilih salah satu opsi.' : 'Kolom ini wajib diisi.');
    if (v.typeMismatch)   return el.type === 'email' ? 'Format email tidak valid, contoh: nama@gmail.com.' : 'Format isian tidak valid.';
    if (v.patternMismatch) return el.title || 'Format isian tidak sesuai.';
    if (v.tooShort)       return 'Minimal ' + el.minLength + ' karakter.';
    if (v.tooLong)        return 'Maksimal ' + el.maxLength + ' karakter.';
    if (v.rangeUnderflow) return 'Nilai minimal ' + el.min + '.';
    if (v.rangeOverflow)  return 'Nilai maksimal ' + el.max + '.';
    if (v.stepMismatch)   return 'Nilai tidak sesuai kelipatan yang diizinkan.';
    if (v.badInput)       return 'Masukkan angka yang valid.';
    return el.validationMessage;
}
function umpan(el) {
    var n = el.nextElementSibling;
    while (n && !n.classList.contains('invalid-feedback')) {
        if (n.matches('input,select,textarea')) { n = null; break; }
        n = n.nextElementSibling;
    }
    return n;
}
function tandai(el) {
    if (!el.willValidate || el.type === 'hidden' || el.disabled) return true;
    var ok = el.checkValidity(), fb = umpan(el);
    var filter = el.form && (el.form.method || '').toLowerCase() === 'get';   // formulir pencarian/filter tidak diberi tanda hijau
    el.classList.toggle('is-invalid', !ok);
    el.classList.toggle('is-valid', ok && !filter && el.value !== '' && el.type !== 'checkbox' && el.type !== 'radio');
    if (!ok) {
        if (!fb) { fb = document.createElement('div'); fb.className = 'invalid-feedback'; el.insertAdjacentElement('afterend', fb); }
        fb.textContent = pesanError(el);
    }
    return ok;
}
document.addEventListener('focusout', function (e) {
    var el = e.target;
    if (!el.matches || !el.matches('.content input, .content select, .content textarea')) return;
    if (/^(text|email|tel|search|url)$/.test(el.type) || el.tagName === 'TEXTAREA') el.value = el.value.trim();
    el.dataset.touched = '1';
    tandai(el);
}, true);
document.addEventListener('input', function (e) {
    var el = e.target;
    if (el.matches && el.matches('.content input, .content select, .content textarea') && (el.dataset.touched || el.classList.contains('is-invalid'))) tandai(el);
}, true);
document.addEventListener('change', function (e) {
    var el = e.target;
    if (el.matches && el.matches('.content select, .content input[type=checkbox], .content input[type=date]')) { el.dataset.touched = '1'; tandai(el); }
}, true);

/* ---------- 3. perapian isian: bintang wajib, telepon, angka, email ---------- */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.content [required]').forEach(function (el) {
        if (!el.id) return;
        var l = document.querySelector('label[for="' + CSS.escape(el.id) + '"]');
        if (l && !l.querySelector('.text-danger')) l.insertAdjacentHTML('beforeend', ' <span class="text-danger">*</span>');
    });
    document.querySelectorAll('.content input[name*="telepon"], .content input[name*="phone"]').forEach(function (el) {
        if (!el.pattern) { el.pattern = '[0-9+\\-\\s]{8,20}'; el.title = 'Telepon 8–20 karakter: angka, +, - atau spasi.'; }
        el.maxLength = 20; el.setAttribute('inputmode', 'tel');
        el.addEventListener('input', function () { el.value = el.value.replace(/[^0-9+\-\s]/g, ''); });
    });
    document.querySelectorAll('.content input[type=email]').forEach(function (el) {
        el.addEventListener('blur', function () { el.value = el.value.trim().toLowerCase(); });
    });
    document.querySelectorAll('.content input[type=number]').forEach(function (el) {
        el.addEventListener('keydown', function (e) {
            var minus = el.min === '' ? false : parseFloat(el.min) >= 0;
            if (e.key === 'e' || e.key === 'E' || e.key === '+' || (e.key === '-' && minus)) e.preventDefault();
        });
        el.addEventListener('wheel', function () { el.blur(); }, { passive: true });
    });

    /* tabel: bungkus agar bisa digeser di layar kecil, jaga kode/tanggal/nominal tidak terpotong */
    document.querySelectorAll('table.table').forEach(function (t) {
        if (!t.closest('.table-responsive')) {
            var w = document.createElement('div'); w.className = 'table-responsive';
            t.parentNode.insertBefore(w, t); w.appendChild(t);
        }
        t.querySelectorAll('tbody td').forEach(function (td) {
            var s = td.textContent.trim();
            if (/^[A-Z]{2,6}-[A-Z0-9-]+$/.test(s) || /^\d{1,2}\/\d{1,2}\/\d{2,4}/.test(s) || /^[+-]?Rp\s?[\d.,]+$/.test(s) || /^[\d.,]+$/.test(s)) td.classList.add('nw');
        });
        t.querySelectorAll('tbody tr').forEach(function (tr) {
            var td = tr.lastElementChild;
            if (td && td.tagName === 'TD' && td.querySelector('.btn')) td.classList.add('aksi');
        });
    });
});

/* ---------- 4. kunci tombol saat kirim + konfirmasi ---------- */
function kunci(f) {
    f.querySelectorAll('button[type=submit], button:not([type])').forEach(function (b) {
        b.disabled = true;
        b.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';
    });
}
function konfirmasiUntuk(f) {
    if (f.dataset.noConfirm !== undefined) return null;
    var m = f.querySelector('input[name=_method]');
    var method = ((m ? m.value : f.method) || '').toUpperCase();
    var act = (f.getAttribute('action') || '').toLowerCase();
    var pesan = f.dataset.confirm, judul = f.dataset.confirmTitle || 'Konfirmasi',
        ikon = f.dataset.confirmIcon || 'question', ok = f.dataset.confirmOk || 'Ya, lanjutkan', warna = '#4f46e5';

    if (method === 'DELETE') {
        pesan = pesan || 'Data yang dihapus tidak dapat dikembalikan. Lanjutkan menghapus?';
        judul = f.dataset.confirmTitle || 'Hapus data?'; ikon = 'warning'; ok = f.dataset.confirmOk || 'Ya, hapus'; warna = '#e11d48';
    } else if (!pesan && /\/batal\b/.test(act)) {
        pesan = 'Pembatalan tidak dapat diulang. Lanjutkan membatalkan?'; judul = 'Batalkan?'; ikon = 'warning'; ok = 'Ya, batalkan'; warna = '#e11d48';
    } else if (!pesan && /\/(konfirmasi|proses|kirim|terima|convert)\b/.test(act)) {
        pesan = 'Lanjutkan proses ini? Perubahan dapat memengaruhi beberapa modul sekaligus.';
    }
    if (!pesan) return null;
    if (ikon === 'warning') warna = '#e11d48';
    return { pesan: pesan, judul: judul, ikon: ikon, ok: ok, warna: warna };
}

var dirty = false;
document.addEventListener('submit', function (e) {
    var f = e.target;
    if (!(f instanceof HTMLFormElement)) return;

    // a) validasi: tampilkan semua kolom bermasalah, fokus ke yang pertama
    if (!f.checkValidity()) {
        e.preventDefault(); e.stopPropagation();
        f.classList.add('was-validated');
        var pertama = null;
        f.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!tandai(el) && !pertama) pertama = el;
        });
        if (pertama) { pertama.scrollIntoView({ block: 'center', behavior: 'smooth' }); pertama.focus({ preventScroll: true }); }
        toast.fire({ icon: 'warning', title: 'Lengkapi isian yang wajib diisi' });
        return;
    }

    // b) konfirmasi (hapus, keluar, proses, dll.)
    var c = konfirmasiUntuk(f);
    if (c && !f.dataset.ok) {
        e.preventDefault();
        var teks = c.pesan + (dirty ? '\n\nPerubahan yang belum disimpan akan hilang.' : '');
        Swal.fire({
            title: c.judul, text: teks, icon: c.ikon, showCancelButton: true,
            confirmButtonText: c.ok, cancelButtonText: 'Batal', confirmButtonColor: c.warna, reverseButtons: true, focusCancel: true
        }).then(function (r) {
            if (r.isConfirmed) { f.dataset.ok = 1; dirty = false; kunci(f); f.submit(); }
        });
        return;
    }

    dirty = false;
    kunci(f);
}, true);

/* ---------- 5. penjaga perubahan yang belum disimpan ---------- */
function formDijaga(f) {
    if (!f || !f.closest('.content')) return false;
    var m = f.querySelector('input[name=_method]');
    var method = ((m ? m.value : f.method) || '').toUpperCase();
    if (method === 'GET' || method === 'DELETE') return false;
    return f.querySelectorAll('input:not([type=hidden]):not([type=checkbox]):not([type=radio]), textarea').length > 0;
}
['input', 'change'].forEach(function (ev) {
    document.addEventListener(ev, function (e) { if (formDijaga(e.target.form)) dirty = true; }, true);
});
window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
document.addEventListener('click', function (e) {
    var a = e.target.closest ? e.target.closest('a[href]') : null;
    if (!a || !dirty) return;
    var h = a.getAttribute('href') || '';
    if (!h || h.charAt(0) === '#' || /^javascript/i.test(h) || a.target === '_blank' || a.hasAttribute('data-bs-toggle') || e.ctrlKey || e.metaKey) return;
    e.preventDefault();
    Swal.fire({
        title: 'Tinggalkan halaman?', text: 'Perubahan yang belum disimpan akan hilang.', icon: 'warning',
        showCancelButton: true, confirmButtonText: 'Ya, tinggalkan', cancelButtonText: 'Tetap di sini',
        confirmButtonColor: '#e11d48', reverseButtons: true, focusCancel: true
    }).then(function (r) { if (r.isConfirmed) { dirty = false; location.href = a.href; } });
}, true);

/* ---------- 6. muat ulang saat kembali lewat tombol Back browser ---------- */
window.addEventListener('pageshow', function (e) { if (e.persisted) location.reload(); });

/* ---------- 7. keluar otomatis saat tidak ada aktivitas ---------- */
(function () {
    var BATAS = 15 * 60 * 1000;      // 15 menit tanpa aktivitas -> keluar otomatis
    var PERINGATAN = 60 * 1000;      // peringatan 1 menit sebelum keluar
    var PING = 4 * 60 * 1000;        // jaga sesi server tetap hidup selama pengguna aktif
    var terakhir = Date.now(), pingTerakhir = Date.now(), dialog = false, pewaktu = null;
    var token = (document.querySelector('input[name="_token"]') || {}).value || '';

    function aktif() {
        if (dialog) return;                         // saat dialog tampil, hanya tombol yang dihitung
        terakhir = Date.now();
        if (terakhir - pingTerakhir > PING) {
            pingTerakhir = terakhir;
            fetch(@json(url('/keep-alive')), { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' }).catch(function () {});
        }
    }
    ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (ev) {
        document.addEventListener(ev, aktif, { passive: true, capture: true });
    });

    function keluar() {
        dirty = false;
        fetch(@json(route('logout')), { method: 'POST', headers: { 'X-CSRF-TOKEN': token }, credentials: 'same-origin' })
            .catch(function () {})
            .then(function () { location.href = @json(url('/login')) + '?timeout=1'; });
    }

    function tanya() {
        dialog = true;
        var sisa = Math.round(PERINGATAN / 1000), t;
        Swal.fire({
            icon: 'warning', title: 'Sesi hampir berakhir',
            html: 'Tidak ada aktivitas. Anda akan keluar otomatis dalam <b id="sisaDetik">' + sisa + '</b> detik.',
            showCancelButton: true, confirmButtonText: 'Tetap masuk', cancelButtonText: 'Keluar sekarang',
            confirmButtonColor: '#4f46e5', allowOutsideClick: false, allowEscapeKey: false,
            didOpen: function () {
                t = setInterval(function () {
                    sisa--; var el = document.getElementById('sisaDetik'); if (el) el.textContent = sisa;
                    if (sisa <= 0) { clearInterval(t); keluar(); }
                }, 1000);
            },
            willClose: function () { clearInterval(t); }
        }).then(function (r) {
            if (r.isConfirmed) {
                dialog = false; terakhir = Date.now(); pingTerakhir = 0; aktif();
            } else if (r.dismiss === Swal.DismissReason.cancel) { keluar(); }
        });
    }

    pewaktu = setInterval(function () {
        if (!dialog && Date.now() - terakhir >= BATAS - PERINGATAN) tanya();
    }, 5000);
})();
</script>

</body>
</html>