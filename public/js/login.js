(() => {
  /* ================= KONFIGURASI ================= */
  const APP = window.TELCOMPT || {};
  const CONFIG = {
    ALLOWED_DOMAIN: 'student.telkomuniversity.ac.id', // satu-satunya domain yang boleh masuk
    LOGIN_URL: APP.loginUrl,   // diisi dari Blade (route('login.store'))
    HOME_URL: APP.homeUrl || '' // tujuan tombol "Kembali ke beranda"
  };

  /* Kirim kredensial ke Laravel. Backend juga memeriksa domain (wajib). */
  async function authenticate(email, password) {
    const res = await fetch(CONFIG.LOGIN_URL, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ email, password })
    });
    const data = await res.json().catch(() => ({}));
    return { ok: res.ok, status: res.status, denied: res.status === 403, redirect: data.redirect };
  }

  /* ================= HELPER ================= */
  const $ = (s) => document.querySelector(s);
  const sleep = (ms) => new Promise(r => setTimeout(r, ms));
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const fine = matchMedia('(pointer: fine)').matches;

  const card = $('#card'), viewLogin = $('#viewLogin'), viewDenied = $('#viewDenied');
  const form = $('#form'), userEl = $('#username'), passEl = $('#password');
  const fUser = $('#fUser'), fPass = $('#fPass'), userMsg = $('#userMsg'), passMsg = $('#passMsg');
  const alertWrap = $('#alertWrap'), btn = $('#submit'), capsNote = $('#capsNote');
  const ghost = $('#ghost'), typed = $('#typed'), suffix = $('#suffix');
  const toastEl = $('#toast');

  suffix.textContent = '@' + CONFIG.ALLOWED_DOMAIN;
  $('#deniedDomain').textContent = '@' + CONFIG.ALLOWED_DOMAIN;
  document.querySelectorAll('.view').forEach(v => [...v.children].forEach((c, i) => c.style.setProperty('--i', i)));

  /* ================= VALIDASI AKUN ================= */
  function resolveAccount(raw) {
    const v = raw.trim().toLowerCase();
    if (!v) return { status: 'empty' };
    if (v.includes('@')) {
      const parts = v.split('@');
      if (parts.length !== 2 || !parts[0] || !parts[1]) return { status: 'invalid' };
      if (parts[1] !== CONFIG.ALLOWED_DOMAIN) return { status: 'denied', email: v };
      return { status: 'ok', email: v };
    }
    if (!/^[a-z0-9._-]+$/.test(v)) return { status: 'invalid' };
    return { status: 'ok', email: v + '@' + CONFIG.ALLOWED_DOMAIN };
  }

  /* ================= STATE ERROR ================= */
  function setFieldError(field, msgEl, text) { field.classList.add('invalid'); msgEl.textContent = text; }
  function clearField(field) { field.classList.remove('invalid'); }
  function clearAll() {
    clearField(fUser); clearField(fPass);
    alertWrap.classList.remove('open');
  }
  function shake() {
    if (reduce) return;
    viewLogin.animate(
      [{transform:'translateX(0)'},{transform:'translateX(-9px)'},{transform:'translateX(8px)'},{transform:'translateX(-6px)'},{transform:'translateX(4px)'},{transform:'translateX(0)'}],
      { duration: 420, easing: 'ease-out' }
    );
  }
  function setBtn(state) {
    btn.classList.remove('is-loading', 'is-success');
    if (state) btn.classList.add('is-' + state);
    btn.disabled = state === 'loading' || state === 'success';
    btn.setAttribute('aria-busy', state === 'loading' ? 'true' : 'false');
  }

  /* ================= INTERAKSI INPUT ================= */
  function updateGhost() {
    const v = userEl.value;
    typed.textContent = v;
    ghost.classList.toggle('show', v.length > 0 && !v.includes('@'));
  }
  userEl.addEventListener('input', () => { updateGhost(); clearField(fUser); alertWrap.classList.remove('open'); clearField(fPass); });
  userEl.addEventListener('blur', () => {
    const acc = resolveAccount(userEl.value);
    if (acc.status === 'denied') setFieldError(fUser, userMsg, 'Hanya akun @' + CONFIG.ALLOWED_DOMAIN + ' yang dapat masuk.');
  });
  passEl.addEventListener('input', () => { clearField(fPass); alertWrap.classList.remove('open'); });

  const toggle = $('#toggle');
  toggle.addEventListener('mousedown', e => e.preventDefault()); // jaga fokus & posisi kursor
  toggle.addEventListener('click', () => {
    const show = passEl.type === 'password';
    passEl.type = show ? 'text' : 'password';
    toggle.setAttribute('aria-pressed', String(show));
    toggle.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
  });

  const caps = e => capsNote.classList.toggle('show', !!(e.getModifierState && e.getModifierState('CapsLock')));
  passEl.addEventListener('keydown', caps);
  passEl.addEventListener('keyup', caps);
  passEl.addEventListener('blur', () => capsNote.classList.remove('show'));

  /* ripple pada tombol */
  document.addEventListener('pointerdown', e => {
    const b = e.target.closest('.btn');
    if (!b || b.disabled) return;
    const r = b.getBoundingClientRect(), size = Math.max(r.width, r.height) * 2;
    const s = document.createElement('span');
    s.className = 'ripple';
    s.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - r.left - size / 2}px;top:${e.clientY - r.top - size / 2}px`;
    b.appendChild(s);
    s.addEventListener('animationend', () => s.remove());
  });

  /* ================= GANTI TAMPILAN ================= */
  function swapView(from, to, after) {
    const startH = card.offsetHeight;
    card.style.height = startH + 'px';
    card.style.overflow = 'hidden';
    from.classList.add('leaving');
    setTimeout(() => {
      from.hidden = true; from.classList.remove('leaving');
      to.hidden = false;
      card.style.height = 'auto';
      const endH = card.offsetHeight;
      card.style.height = startH + 'px';
      void card.offsetHeight;
      card.style.height = endH + 'px';
      setTimeout(() => { card.style.height = ''; card.style.overflow = ''; }, reduce ? 0 : 480);
      if (after) after();
    }, reduce ? 0 : 200);
  }

  function showDenied(email) {
    $('#deniedEmail').textContent = email;
    swapView(viewLogin, viewDenied, () => $('#deniedTitle').focus({ preventScroll: true }));
  }

  $('#otherAccount').addEventListener('click', () => {
    form.reset(); updateGhost(); clearAll(); setBtn(null);
    passEl.type = 'password'; toggle.setAttribute('aria-pressed', 'false');
    swapView(viewDenied, viewLogin, () => setTimeout(() => userEl.focus({ preventScroll: true }), 350));
  });
  $('#goHome').addEventListener('click', () => {
    if (CONFIG.HOME_URL) { location.href = CONFIG.HOME_URL; }
    else $('#otherAccount').click();
  });

  /* ================= SUBMIT ================= */
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearAll();
    const acc = resolveAccount(userEl.value);
    const pw = passEl.value;
    let first = null;

    if (acc.status === 'empty') { setFieldError(fUser, userMsg, 'Nama pengguna wajib diisi.'); first = userEl; }
    else if (acc.status === 'invalid') { setFieldError(fUser, userMsg, 'Gunakan huruf, angka, titik, atau garis bawah saja.'); first = userEl; }
    else if (acc.status === 'denied') {
      setBtn('loading'); await sleep(550); setBtn(null);
      showDenied(acc.email);
      return;
    }
    if (!pw) { setFieldError(fPass, passMsg, 'Kata sandi wajib diisi.'); first = first || passEl; }
    if (first) { shake(); first.focus(); return; }

    setBtn('loading');
    let res;
    try { res = await authenticate(acc.email, pw); } catch (err) { res = { ok: false }; }

    if (res.denied) { setBtn(null); showDenied(acc.email); return; }
    if (res.status === 429) { setBtn(null); toast('Terlalu banyak percobaan masuk. Coba lagi sebentar lagi.'); return; }

    if (!res.ok) {
      setBtn(null);
      alertWrap.classList.add('open');
      setFieldError(fPass, passMsg, 'Periksa nama pengguna dan kata sandi.');
      shake();
      passEl.focus(); passEl.select();
      return;
    }

    setBtn('success');
    confetti(btn);
    const target = res.redirect || '/';
    toast('Berhasil masuk. Mengalihkan ke beranda…');
    setTimeout(() => { location.href = target; }, 1400);
  });

  /* ================= EFEK ================= */
  let toastTimer;
  function toast(text) {
    toastEl.textContent = text;
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toastEl.classList.remove('show'), 3200);
  }

  function confetti(el) {
    if (reduce) return;
    const r = el.getBoundingClientRect(), cx = r.left + r.width / 2, cy = r.top + r.height / 2;
    const colors = ['#2563eb', '#6d5ef0', '#fbbf24', '#34d399', '#f472b6'];
    for (let i = 0; i < 26; i++) {
      const p = document.createElement('i');
      p.className = 'confetti';
      p.style.cssText = `left:${cx}px;top:${cy}px;background:${colors[i % colors.length]}`;
      document.body.appendChild(p);
      const a = Math.random() * Math.PI * 2, d = 70 + Math.random() * 130;
      const x = Math.cos(a) * d, y = Math.sin(a) * d - 50;
      p.animate([
        { transform: 'translate(-50%,-50%) rotate(0deg) scale(1)', opacity: 1 },
        { transform: `translate(calc(-50% + ${x}px), calc(-50% + ${y + 110}px)) rotate(${Math.random() * 600}deg) scale(.5)`, opacity: 0 }
      ], { duration: 900 + Math.random() * 600, easing: 'cubic-bezier(.2,.7,.3,1)' }).onfinish = () => p.remove();
    }
  }

  /* parallax + cahaya mengikuti kursor di panel kiri */
  if (!reduce && fine) {
    const panel = $('#panel'), wraps = panel.querySelectorAll('.orb-wrap');
    let raf = 0, mx = 0, my = 0, gx = 0, gy = 0;
    addEventListener('pointermove', e => {
      mx = (e.clientX / innerWidth - .5) * 2; my = (e.clientY / innerHeight - .5) * 2;
      const r = panel.getBoundingClientRect(); gx = e.clientX - r.left; gy = e.clientY - r.top;
      if (!raf) raf = requestAnimationFrame(() => {
        raf = 0;
        panel.style.setProperty('--gx', gx + 'px'); panel.style.setProperty('--gy', gy + 'px');
        wraps.forEach(w => { const d = +w.dataset.depth; w.style.transform = `translate3d(${mx * d}px,${my * d}px,0)`; });
      });
    }, { passive: true });
  }

  /* fokus awal setelah animasi masuk (hanya perangkat dengan mouse) */
  if (fine) setTimeout(() => userEl.focus({ preventScroll: true }), 900);
})();
