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
        .wrap { max-width: 440px; margin: 0 auto; padding: 40px 18px 60px; }
        .brand { font-size: 13px; font-weight: 800; letter-spacing: .1em; color: var(--hh-accent); text-align:center; margin-bottom: 20px; }
        .card { background:var(--hh-surface); color:#f5f5f5; border-radius:16px; padding:30px 24px; box-shadow: 0 3px 14px #10111214; text-align:center; }
        h1 { font-size: 21px; margin: 0 0 8px; }
        p.sub { color:#c1c3c7; font-size:14px; margin:0 0 28px; }
        .stars { display:flex; justify-content:center; gap:8px; }
        .stars form { margin:0; }
        .stars button {
            background:none; border:none; cursor:pointer; font-size:38px; line-height:1;
            padding:4px; color:#54565a; transition: color .12s ease, transform .12s ease;
        }
        .stars button:hover { color: var(--hh-accent); transform: scale(1.12); }
        .stars-labels { display:flex; justify-content:space-between; margin-top:10px; padding:0 2px; font-size:11px; color:#9a9da3; }
        .banner { border-radius:9px; padding:12px 14px; margin-bottom:18px; font-size:13.5px; background:#1c3a2a; color:#6fd39a; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">HH MOTEL</div>
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
    </div>
</body>
</html>
