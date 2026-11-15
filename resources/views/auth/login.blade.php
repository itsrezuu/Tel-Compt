<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#352a9a">
<title>Masuk · TEL-COMPT</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
<div class="page">

  <!-- ===== PANEL KIRI ===== -->
  <aside class="brand-panel" id="panel">
    <div class="dots"></div>
    <div class="orb-wrap o1" data-depth="26"><div class="orb"></div></div>
    <div class="orb-wrap o2" data-depth="-20"><div class="orb"></div></div>
    <div class="orb-wrap o3" data-depth="40"><div class="orb"></div></div>

    <span class="float" style="top:18%;right:14%;width:22px;height:22px;--d:8s"><svg viewBox="0 0 24 24"><path d="M12 2l2.2 7.8L22 12l-7.8 2.2L12 22l-2.2-7.8L2 12l7.8-2.2z"/></svg></span>
    <span class="float" style="top:62%;right:9%;width:16px;height:16px;--d:11s;--dl:-3s"><svg viewBox="0 0 24 24"><path d="M12 2l2.2 7.8L22 12l-7.8 2.2L12 22l-2.2-7.8L2 12l7.8-2.2z"/></svg></span>
    <span class="float" style="bottom:14%;left:42%;width:26px;height:26px;--d:10s;--dl:-5s"><svg viewBox="0 0 24 24"><path d="M12 2l2.2 7.8L22 12l-7.8 2.2L12 22l-2.2-7.8L2 12l7.8-2.2z"/></svg></span>
    <span class="float ring" style="top:30%;right:28%;width:34px;height:34px;--d:12s;--dl:-2s"></span>
    <span class="float ring" style="bottom:26%;right:18%;width:18px;height:18px;--d:9s;--dl:-6s"></span>

    <header class="brand">
      <div class="logo">TEL-COMPT</div>
      <p>Telkom University</p>
    </header>

    <section class="hero">
      <div class="trophy" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
      </div>
      <h2 aria-label="Satu ruang untuk peluang dan prestasi.">
        <span class="word" style="--w:0" aria-hidden="true">Satu</span>
        <span class="word" style="--w:1" aria-hidden="true">ruang</span>
        <span class="word" style="--w:2" aria-hidden="true">untuk</span>
        <span class="word hl" style="--w:3" aria-hidden="true">peluang</span>
        <span class="word" style="--w:4" aria-hidden="true">dan</span>
        <span class="word hl" style="--w:5" aria-hidden="true">prestasi.</span>
      </h2>
      <p class="lead">Temukan lomba terkurasi, ajukan pendampingan dan dokumentasikan pengalaman Anda.</p>
      <p class="roles"><span class="r">Mahasiswa</span><span class="sep">·</span><span class="r">Admin HIMA</span><span class="sep">·</span><span class="r">Dosen</span><span class="sep">·</span><span class="r">Kaprodi</span></p>
      <p class="legal">Data contoh fiktif · Rancangan aplikasi web</p>
    </section>
  </aside>

  <!-- ===== PANEL KANAN ===== -->
  <main class="stage">
    <div class="glow g1"></div><div class="glow g2"></div>
    <div class="stage-inner">
      <section class="card" id="card">

        <!-- Tampilan login -->
        <div class="view view-login" id="viewLogin">
          <h1>Masuk ke TEL-COMPT</h1>
          <p class="sub">Gunakan akun kampus Anda untuk melanjutkan.</p>

          <div class="collapse" id="alertWrap">
            <div class="collapse-inner">
              <div class="alert" role="alert">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <strong>Tidak dapat masuk</strong>
                <p>Nama pengguna atau kata sandi tidak sesuai. Periksa kembali kredensial Anda.</p>
              </div>
            </div>
          </div>

          <form id="form" novalidate>
            <div class="field" id="fUser">
              <label for="username">Nama pengguna akun kampus</label>
              <div class="control">
                <input id="username" name="username" type="text" inputmode="email" autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="mis. nadiapramesti" aria-describedby="userMsg">
                <div class="ghost" id="ghost" aria-hidden="true"><span class="typed" id="typed"></span><span class="suffix" id="suffix"></span></div>
              </div>
              <p class="msg err" id="userMsg"></p>
            </div>

            <div class="field" id="fPass">
              <label for="password">Kata sandi</label>
              <div class="control">
                <input id="password" name="password" type="password" autocomplete="current-password" aria-describedby="passMsg capsNote">
                <button class="toggle" id="toggle" type="button" aria-label="Tampilkan kata sandi" aria-pressed="false">
                  <svg class="on" viewBox="0 0 24 24"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg class="off" viewBox="0 0 24 24"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
                </button>
              </div>
              <p class="msg err" id="passMsg"></p>
              <p class="msg note" id="capsNote">Caps Lock sedang aktif.</p>
            </div>

            <button class="btn btn-primary" id="submit" type="submit">
              <span class="lbl">Masuk</span>
              <svg class="spin" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.2-8.55"/></svg>
              <svg class="check" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
            </button>
          </form>

          <p class="help">Kesulitan mengakses akun? Hubungi pengelola akun kampus Anda.</p>
        </div>

        <!-- Tampilan akses ditolak -->
        <div class="view view-denied" id="viewDenied" hidden>
          <div class="shield" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
          </div>
          <h1 tabindex="-1" id="deniedTitle">Akses ditolak</h1>
          <p class="sub">Akun Anda tidak memiliki izin untuk masuk. TEL-COMPT hanya dapat diakses dengan akun mahasiswa Telkom University.</p>
          <div class="alert static" role="alert">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
            <strong>Izin tidak tersedia</strong>
            <p>Hanya akun berdomain <b id="deniedDomain"></b> yang dapat masuk. Akun yang dicoba: <span class="mono" id="deniedEmail"></span></p>
          </div>
          <button class="btn btn-primary" id="goHome" type="button"><span class="lbl">Kembali ke beranda</span></button>
          <button class="btn btn-secondary" id="otherAccount" type="button"><span class="lbl">Gunakan akun lain</span></button>
        </div>

      </section>
      <p class="foot">Data contoh fiktif · Tidak menyatakan integrasi autentikasi tertentu.</p>
    </div>
  </main>
</div>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
  window.TELCOMPT = { loginUrl: @json(route('login.store')), homeUrl: @json(url('/')) };
</script>
<script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
