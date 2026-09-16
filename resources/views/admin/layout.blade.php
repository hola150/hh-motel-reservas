<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Administración') — HH Motel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { background:#111; color:#eee; font-family: -apple-system, "Segoe UI", sans-serif; margin: 0; }
        .wrap { max-width: 880px; margin: 0; padding: 26px 24px 70px; }
        h1 { font-size: 19px; margin: 0 0 4px; }
        .sub { color:#999; font-size:13px; margin-bottom:22px; }
        .card { background:#1c1c1c; border:1px solid #333; border-radius: 10px; padding: 16px 18px; margin-bottom: 16px; }
        table { width:100%; border-collapse: collapse; font-size: 14px; }
        th { text-align:left; color:#888; font-weight:500; font-size:11px; text-transform:uppercase; padding: 6px 8px; border-bottom:1px solid #333; }
        td { padding: 9px 8px; border-bottom:1px solid #262626; vertical-align: middle; }
        tr:last-child td { border-bottom:none; }
        a.btn, button.btn { display:inline-block; background:#ff7918; color:#fff; border:none; padding:9px 16px; border-radius:7px; font-size:13.5px; font-weight:600; text-decoration:none; cursor:pointer; }
        a.btn.secondary, button.btn.secondary { background:#2a2a2a; }
        a.link { color:#ff7918; text-decoration:none; font-size:13px; }
        label { display:block; font-size: 12.5px; color:#bbb; margin: 14px 0 6px; }
        input, select, textarea { width:100%; box-sizing:border-box; background:#1c1c1c; border:1px solid #333; color:#fff; padding:9px 11px; border-radius:8px; font-size:14.5px; font-family:inherit; }
        .row2 { display:flex; gap:12px; }
        .row2 > div { flex:1; }
        .pill { display:inline-block; font-family: ui-monospace, monospace; font-size: 10.5px; padding:2px 8px; border-radius:20px; background:#2a2a2a; color:#ccc; }
        .pill-warn { background:#3a331c; color:#e8c76f; }
        .pill-bad { background:#3a1c22; color:#e88a9a; }
        .stock-form { display:flex; gap:4px; align-items:center; }
        .stock-form input { width:56px; background:#111; border:1px solid #333; color:#eee; border-radius:6px; font-size:12px; padding:4px; }
        .stock-form button { background:#2a2a2a; color:#eee; border:1px solid #444; border-radius:6px; font-size:11px; padding:4px 8px; cursor:pointer; }
        .errors { background:#3a1c1c; border:1px solid #7a2d2d; color:#f3b8b8; padding:12px 14px; border-radius:8px; margin-bottom: 18px; font-size:14px; }
        .actions { display:flex; gap:16px; align-items:center; }
        form.inline { display:inline; }
    </style>
</head>
<body>
    @include('partials.navbar')
    <div class="wrap">
        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        @if (session('status'))
            <div class="card" style="border-color:#2e6e45; color:#6fd39a;">{{ session('status') }}</div>
        @endif
        @yield('content')
    </div>
</body>
</html>
