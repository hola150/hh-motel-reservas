<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="color-scheme" content="light">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contanos qué pasó — HH Motel</title>
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
        .card { background:var(--hh-surface); color:#f5f5f5; border-radius:16px; padding:26px 24px; box-shadow: 0 3px 14px #10111214; }
        h1 { font-size: 19px; margin: 0 0 6px; text-align:center; }
        p.sub { color:#c1c3c7; font-size:13.5px; margin:0 0 20px; text-align:center; }
        label { display:block; font-size:12.5px; color:#c1c3c7; margin:0 0 8px; }
        textarea { width:100%; min-height:110px; background:#202123; border:1px solid #62656b; color:#f4f5f7; padding:12px; border-radius:8px; font-size:15px; font-family:inherit; resize:vertical; }
        button.submit { width:100%; margin-top:16px; background:var(--hh-accent); color:#21170e; border:none; padding:15px; border-radius:9px; font-size:15.5px; font-weight:800; cursor:pointer; }
        button.submit:hover { background:var(--hh-accent-hover); }
    </style>
</head>
<body>
    <header class="hero">
        <img src="https://assets.cdn.filesafe.space/ksYYfSiY8nP4YFvkrJFJ/media/6aab44cf9f8b31b6ab530587.png" alt="HH Motel">
    </header>
    <div class="wrap">
        <div class="card">
            <h1>Lamentamos que no haya sido excelente</h1>
            <p class="sub">Contanos qué pasó para poder mejorarlo -- esto lo lee directo el equipo, no es público.</p>
            <form method="POST" action="{{ route('reviews.comment.update', $review) }}">
                @csrf
                <label for="comment">¿Qué podemos mejorar?</label>
                <textarea id="comment" name="comment" placeholder="Contanos con el mayor detalle posible..." autofocus></textarea>
                <button class="submit" type="submit">Enviar</button>
            </form>
        </div>
    </div>
</body>
</html>
