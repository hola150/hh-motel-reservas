<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="color-scheme" content="light">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>¿Cómo fue tu experiencia? — HH Motel</title>
    <style>
        :root {
            color-scheme: light;
            --hh-accent: #ff7918;
            --hh-accent-hover: #ee6909;
            --hh-canvas: #f5f5f4;
            --hh-surface: #353638;
            --hh-ink: #202124;
        }
        * { box-sizing: border-box; }
        body { background:var(--hh-canvas); color:var(--hh-ink); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; line-height: 1.45; }
        .wrap { max-width: 440px; margin: 0 auto; padding: 0 18px 60px; }
        header.hero { text-align:center; padding: 26px 12px 22px; background:#111; border-radius:0 0 20px 20px; margin-bottom: 26px; box-shadow:0 5px 18px #0002; }
        header.hero img { display:block; width:min(170px, 54vw); max-width:100%; height:auto; margin:0 auto; }
        .card { background:var(--hh-surface); color:#f5f5f5; border-radius:16px; padding:32px 24px; box-shadow: 0 3px 14px #10111214; text-align:center; }
        h1 { font-size: 22px; margin: 0 0 8px; letter-spacing:-.02em; }
        p.sub { color:#c1c3c7; font-size:14.5px; margin:0 0 30px; }
        .stars { display:flex; justify-content:center; gap:8px; }
        .stars form { margin:0; }
        .stars button {
            background:none; border:none; cursor:pointer; font-size:40px; line-height:1;
            padding:4px; color:#54565a; transition: color .12s ease, transform .12s ease;
        }
        .stars button:hover { color: var(--hh-accent); transform: scale(1.14); }
        .stars-labels { display:flex; justify-content:space-between; margin-top:12px; padding:0 4px; font-size:11.5px; color:#9a9da3; text-transform:uppercase; letter-spacing:.03em; }
        .banner { border-radius:9px; padding:12px 14px; margin-bottom:18px; font-size:13.5px; background:#1c3a2a; color:#6fd39a; }
        p.footnote { text-align:center; color:#a2a5ab; font-size:12px; margin-top:22px; }
    </style>
</head>
<body>
    <header class="hero">
        <img src="https://assets.cdn.filesafe.space/ksYYfSiY8nP4YFvkrJFJ/media/6aab44cf9f8b31b6ab530587.png" alt="HH Motel">
    </header>
    <div class="wrap">
        <div class="card">
            @if (session('status'))
                <div class="banner">{{ session('status') }}</div>
            @endif
            <h1>¿Cómo fue tu experiencia?</h1>
            <p class="sub">Tocá las estrellas para calificarnos.</p>

            <div class="stars">
                @for ($i = 1; $i <= 5; $i++)
                    <form method="POST" action="{{ route('reviews.store', $booking?->code) }}">
                        @csrf
                        <input type="hidden" name="rating" value="{{ $i }}">
                        <button type="submit" aria-label="{{ $i }} estrellas" title="{{ $i }} estrellas">★</button>
                    </form>
                @endfor
            </div>
            <div class="stars-labels"><span>Muy mala</span><span>Excelente</span></div>
        </div>
        <p class="footnote">HH Motel · Santiago</p>
    </div>
</body>
</html>
