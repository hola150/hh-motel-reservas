<!doctype html>
<html lang="es" style="background:#111;color:#eee">
<head>
    <meta charset="utf-8">
    <meta name="color-scheme" content="dark">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Habitaciones — HH Motel</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { background:#111; color:#eee; font-family: -apple-system, "Segoe UI", sans-serif; margin: 0; line-height: 1.45; }
        a { color: inherit; }
        .wrap { max-width: 980px; margin: 0 auto; padding: 0 20px 60px; }

        header.hero { text-align:center; padding: 44px 20px 32px; border-bottom: 1px solid #262626; margin-bottom: 36px; }
        header.hero .brand { font-size: 15px; font-weight: 800; letter-spacing: .1em; color: #ff7918; margin-bottom: 10px; }
        header.hero h1 { font-size: 26px; margin: 0 0 10px; letter-spacing: -.01em; }
        header.hero p { color:#999; font-size: 14.5px; max-width: 520px; margin: 0 auto 22px; }
        .hero-btn-row { display:inline-flex; gap:10px; flex-wrap:wrap; justify-content:center; }
        a.hero-btn { display:inline-block; background:#262626; border:1px solid #383838; color:#eee; text-decoration:none; padding: 13px 26px; border-radius: 30px; font-weight:700; font-size:14.5px; }
        a.hero-btn:hover { border-color:#25d366; }
        a.hero-btn.primary { background:#ff7918; border-color:#ff7918; color:#fff; }
        a.hero-btn.primary:hover { background:#df6209; }

        /* Mismo lenguaje visual que las tarjetas del tablero interno (rooms/board.blade.php):
           borde izquierdo de color por categoría, elevación al hover, título coloreado. */
        .cat-card { background:#1a1a1a; border:1px solid #2c2c2c; border-left-width:4px; border-radius: 16px; overflow:hidden; margin-bottom: 28px; transition: transform .13s ease, box-shadow .13s ease, border-color .13s ease; }
        .cat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.45); }
        @media (prefers-reduced-motion: reduce) { .cat-card { transition: none; } .cat-card:hover { transform: none; } }
        .cat-card.cat-go { border-left-color:#5b9dd9; }
        .cat-card.cat-go:hover { box-shadow: 0 6px 20px rgba(91,157,217,.22); }
        .cat-card.cat-lite { border-left-color:#4ecdc4; }
        .cat-card.cat-lite:hover { box-shadow: 0 6px 20px rgba(78,205,196,.22); }
        .cat-card.cat-plus { border-left-color:#b088e8; }
        .cat-card.cat-plus:hover { box-shadow: 0 6px 20px rgba(176,136,232,.22); }
        .cat-card.cat-max { border-left-color:#f2994a; }
        .cat-card.cat-max:hover { box-shadow: 0 6px 20px rgba(242,153,74,.22); }
        .cat-card.cat-new-lite { border-left-width:3px; box-shadow: 0 0 0 1px rgba(232,199,111,.25) inset; }
        .cat-card.cat-new-lite:hover { box-shadow: 0 6px 20px rgba(232,199,111,.3), 0 0 0 1px rgba(232,199,111,.25) inset; }
        .cat-go h2 { color:#5b9dd9; }
        .cat-lite h2 { color:#4ecdc4; }
        .cat-plus h2 { color:#b088e8; }
        .cat-max h2 { color:#f2994a; }
        .cat-new-lite h2 { color:#e8c76f; }

        .cat-gallery { display:grid; grid-template-columns: repeat(4, 1fr); gap:2px; background:#111; }
        .cat-gallery a { display:block; aspect-ratio: 4/3; overflow:hidden; }
        .cat-gallery img { width:100%; height:100%; object-fit:cover; display:block; transition: transform .2s ease; }
        .cat-gallery a:hover img { transform: scale(1.05); }
        .cat-gallery.empty { aspect-ratio: 16/5; display:flex; align-items:center; justify-content:center; color:#555; font-size:13px; grid-template-columns:none; }

        .cat-body { padding: 22px 24px 26px; }
        .cat-body h2 { font-size: 20px; margin: 0 0 4px; }
        .cat-cap { font-size: 12.5px; color: #6fd39a; font-weight:600; margin-bottom: 12px; }
        .cat-desc { font-size: 14px; color: #ccc; margin-bottom: 14px; }
        .cat-features { display:flex; flex-wrap:wrap; gap: 7px; margin-bottom: 18px; }
        .cat-features span { display:inline-block; font-family: ui-monospace, monospace; background:#262626; border:1px solid #383838; color:#ccc; font-size: 11px; font-weight:600; padding: 3px 11px; border-radius: 20px; }

        table.price-table { width:100%; border-collapse:collapse; margin-bottom: 20px; font-size: 13.5px; }
        table.price-table th { text-align:left; color:#888; font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:.03em; padding: 0 10px 8px 0; }
        table.price-table td { padding: 8px 10px 8px 0; border-top: 1px solid #262626; }
        table.price-table td.price { font-family: ui-monospace, monospace; font-weight:700; color:#fff; }
        table.price-table td.dash { color:#555; }

        .cat-btn-row { display:flex; gap:10px; }
        a.cat-btn { flex:1; display:block; text-align:center; background:#262626; border:1px solid #383838; color:#eee; text-decoration:none; padding: 13px; border-radius: 9px; font-weight:700; font-size:13.5px; }
        a.cat-btn:hover { border-color:#25d366; }
        a.cat-btn.primary { background:#ff7918; border-color:#ff7918; color:#fff; }
        a.cat-btn.primary:hover { background:#df6209; }
        @media (max-width: 420px) { .cat-btn-row { flex-direction:column; } }

        .video-links { display:flex; flex-wrap:wrap; gap:10px; margin: -6px 0 18px; }
        .video-links a { font-size: 12.5px; color:#7fbcdc; text-decoration:none; border:1px solid #2f4a5a; padding:5px 11px; border-radius:20px; }
        .video-links a:hover { border-color:#7fbcdc; }

        footer.catalog-footer { text-align:center; color:#666; font-size:12.5px; padding: 20px 0 10px; }

        @media (max-width: 560px) {
            .cat-gallery { grid-template-columns: repeat(2, 1fr); }
            header.hero { padding: 32px 16px 26px; }
            header.hero h1 { font-size: 21px; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <header class="hero">
            <div class="brand">HH MOTEL</div>
            <h1>Nuestras habitaciones</h1>
            <p>Elegí la que más te acomode — reservá online al toque o escribinos por WhatsApp.</p>
            <div class="hero-btn-row">
                <a class="hero-btn primary" href="{{ route('catalog.reserve') }}">Reservar online →</a>
                <a class="hero-btn" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Por WhatsApp →</a>
            </div>
        </header>

        @forelse ($categories as $entry)
            @php [$category, $photos, $videos, $prices, $catWhatsapp] = [$entry['category'], $entry['photos'], $entry['videos'], $entry['prices'], $entry['whatsappUrl']]; @endphp
            <section class="cat-card cat-{{ Str::slug($category->name) }}">
                @if ($photos->isNotEmpty())
                    <div class="cat-gallery">
                        @foreach ($photos->take(4) as $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener"><img src="{{ $url }}" alt="Foto {{ $category->name }}" loading="lazy"></a>
                        @endforeach
                    </div>
                @else
                    <div class="cat-gallery empty">Fotos próximamente</div>
                @endif

                <div class="cat-body">
                    <h2>{{ $category->name }}</h2>
                    <div class="cat-cap">Hasta {{ $category->base_capacity }} personas{{ $category->extra_guest_from ? ' · desde la '.$category->extra_guest_from.'ª persona, cargo adicional' : '' }}</div>

                    @if ($category->description)
                        <div class="cat-desc">{{ $category->description }}</div>
                    @endif

                    @if (!empty($category->features))
                        <div class="cat-features">
                            @foreach ($category->features as $feature)
                                <span>{{ $feature }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if ($videos->isNotEmpty())
                        <div class="video-links">
                            @foreach ($videos as $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener">Ver video {{ $loop->index + 1 }} →</a>
                            @endforeach
                        </div>
                    @endif

                    @if ($prices->isNotEmpty())
                        <table class="price-table">
                            <thead>
                                <tr>
                                    <th>Duración</th>
                                    <th>Lun a jue</th>
                                    <th>Vie a dom</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($prices as $row)
                                    <tr>
                                        <td>{{ $row['duration'] >= 60 ? intdiv($row['duration'], 60).' h' : $row['duration'].' min' }}</td>
                                        <td class="{{ $row['hh'] ? 'price' : 'dash' }}">{{ $row['hh'] ? '$'.number_format($row['hh'], 0, ',', '.') : '—' }}</td>
                                        <td class="{{ $row['hot'] ? 'price' : 'dash' }}">{{ $row['hot'] ? '$'.number_format($row['hot'], 0, ',', '.') : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <div class="cat-btn-row">
                        <a class="cat-btn primary" href="{{ route('catalog.reserve', ['categoria' => $category->id]) }}">Reservar online →</a>
                        <a class="cat-btn" href="{{ $catWhatsapp }}" target="_blank" rel="noopener">Por WhatsApp →</a>
                    </div>
                </div>
            </section>
        @empty
            <p style="text-align:center; color:#777;">Todavía no hay categorías activas para mostrar.</p>
        @endforelse

        <footer class="catalog-footer">HH Motel</footer>
    </div>
</body>
</html>
