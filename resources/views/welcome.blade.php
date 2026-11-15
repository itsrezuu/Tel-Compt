<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#352a9a">
    <title>Beranda · TEL-COMPT</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #172033;
            background: #f5f7fb;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
        }
        * { box-sizing: border-box; }
        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(ellipse at 82% 8%, rgba(108, 94, 240, .12), transparent 30rem),
                radial-gradient(ellipse at 5% 86%, rgba(55, 119, 232, .09), transparent 26rem),
                #f5f7fb;
        }
        a { color: inherit; }
        .page {
            width: min(1120px, calc(100% - 48px));
            min-height: 100vh;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
        }
        .header {
            min-height: 92px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }
        .brand { display: inline-flex; align-items: center; gap: 13px; text-decoration: none; }
        .brand-mark {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            color: white;
            background: linear-gradient(140deg, #5142c8, #7669ee);
            font-size: 13px;
            font-weight: 800;
            letter-spacing: -.04em;
            box-shadow: 0 8px 18px rgba(79, 65, 195, .2);
        }
        .brand-name { display: block; font-size: 15px; font-weight: 800; letter-spacing: .04em; }
        .brand-campus { display: block; margin-top: 3px; color: #69738a; font-size: 12px; }
        .login-link {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            padding: 0 18px;
            border: 1px solid #d9deea;
            border-radius: 11px;
            background: rgba(255, 255, 255, .82);
            color: #29256d;
            font-size: 14px;
            font-weight: 650;
            text-decoration: none;
            transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease;
        }
        .login-link:hover { transform: translateY(-1px); border-color: #aaa3e8; box-shadow: 0 7px 18px rgba(35, 34, 70, .08); }
        .login-link:focus-visible, .brand:focus-visible { outline: 3px solid #aaa3e8; outline-offset: 3px; }
        main { flex: 1; display: flex; align-items: center; padding: 36px 0 70px; }
        .content { width: 100%; }
        .hero {
            position: relative;
            overflow: hidden;
            padding: clamp(34px, 7vw, 76px);
            border: 1px solid rgba(221, 225, 238, .9);
            border-radius: 28px;
            background: rgba(255, 255, 255, .9);
            box-shadow: 0 24px 70px rgba(36, 43, 78, .08);
        }
        .hero::after {
            position: absolute;
            top: -118px;
            right: -92px;
            width: 320px;
            height: 320px;
            border: 1px solid rgba(101, 89, 218, .12);
            border-radius: 50%;
            box-shadow: 0 0 0 34px rgba(101, 89, 218, .035), 0 0 0 68px rgba(101, 89, 218, .025);
            content: "";
            pointer-events: none;
        }
        .eyebrow {
            margin: 0 0 18px;
            color: #5648bf;
            font-size: 12px;
            font-weight: 750;
            letter-spacing: .13em;
            text-transform: uppercase;
        }
        h1 {
            max-width: 760px;
            margin: 0;
            color: #18203a;
            font-size: clamp(38px, 6vw, 66px);
            font-weight: 720;
            letter-spacing: -.055em;
            line-height: 1.06;
        }
        .intro {
            max-width: 650px;
            margin: 22px 0 28px;
            color: #5e687d;
            font-size: 17px;
            line-height: 1.7;
        }
        .primary-link {
            display: inline-flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            padding: 0 22px;
            border: 0;
            border-radius: 12px;
            background: #5142c8;
            color: white;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 9px 20px rgba(79, 65, 195, .2);
            transition: transform .18s ease, background .18s ease;
        }
        .primary-link:hover { transform: translateY(-1px); background: #4335b4; }
        .primary-link:focus-visible { outline: 3px solid #aaa3e8; outline-offset: 3px; }
        .features {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-top: 22px;
        }
        .feature {
            min-height: 150px;
            padding: 23px 24px;
            border: 1px solid #e2e6ef;
            border-radius: 18px;
            background: rgba(255, 255, 255, .86);
        }
        .feature-number { color: #7166cc; font-size: 12px; font-weight: 750; letter-spacing: .08em; }
        .feature h2 { margin: 13px 0 7px; color: #222b45; font-size: 16px; }
        .feature p { margin: 0; color: #6a7488; font-size: 13px; line-height: 1.6; }
        footer { padding: 20px 0 28px; color: #7a8396; font-size: 12px; }
        @media (max-width: 720px) {
            .page { width: min(calc(100% - 32px), 560px); }
            .header { min-height: 78px; }
            main { padding: 20px 0 44px; }
            .hero { border-radius: 22px; }
            .features { grid-template-columns: 1fr; gap: 11px; }
            .feature { min-height: auto; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <div class="page">
        <header class="header">
            <a class="brand" href="/" aria-label="TEL-COMPT, beranda">
                <span class="brand-mark" aria-hidden="true">TC</span>
                <span>
                    <span class="brand-name">TEL-COMPT</span>
                    <span class="brand-campus">Telkom University</span>
                </span>
            </a>
            @if (Route::has('login'))
                <a class="login-link" href="{{ route('login') }}">Masuk</a>
            @endif
        </header>

        <main>
            <div class="content">
                <section class="hero" aria-labelledby="welcome-title">
                    <p class="eyebrow">Informasi lomba · Pendampingan prestasi</p>
                    <h1 id="welcome-title">Temukan peluang. Wujudkan prestasi.</h1>
                    <p class="intro">TEL-COMPT membantu sivitas Telkom University menemukan informasi lomba, mengajukan pendampingan, dan mendokumentasikan pengalaman prestasi dalam satu platform.</p>
                    @if (Route::has('login'))
                        <a class="primary-link" href="{{ route('login') }}">Masuk ke TEL-COMPT</a>
                    @endif
                </section>

                <section class="features" aria-label="Fitur TEL-COMPT">
                    <article class="feature">
                        <span class="feature-number">01</span>
                        <h2>Informasi lomba</h2>
                        <p>Jelajahi informasi kompetisi yang relevan dengan minat dan bidang Anda.</p>
                    </article>
                    <article class="feature">
                        <span class="feature-number">02</span>
                        <h2>Pendampingan</h2>
                        <p>Ajukan pendampingan dan berkoordinasi dengan dosen pembimbing.</p>
                    </article>
                    <article class="feature">
                        <span class="feature-number">03</span>
                        <h2>Dokumentasi prestasi</h2>
                        <p>Catat pengalaman kompetisi dan pantau proses verifikasi prestasi.</p>
                    </article>
                </section>
            </div>
        </main>

        <footer>TEL-COMPT · Telkom University</footer>
    </div>
</body>
</html>
