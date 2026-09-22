@extends('admin.layout')
@section('title', 'Integración GHL')
@section('content')
    <h1>Integración con GoHighLevel</h1>
    <p class="sub">Cada reserva nueva crea/actualiza el contacto en GHL y le pone un tag "Reservó:{fecha}" -- reemplaza el de la reserva anterior, no se acumulan. Prueba acá con un número tuyo antes de confiar en que la sincronización automática funciona.</p>

    <div class="card">
        @if ($configured)
            <p style="color:#6ee7b7; margin:0;">✓ Token y Location ID configurados.</p>
        @else
            <p style="color:#f3b8b8; margin:0;">✗ Falta configurar GHL_PRIVATE_TOKEN y/o GHL_LOCATION_ID en las variables de entorno -- ninguna reserva se está sincronizando todavía.</p>
        @endif
    </div>

    <div class="card">
        <h2 style="font-size:14px; margin:0 0 4px;">Probar conexión</h2>
        <p class="sub" style="margin:0 0 10px;">Usa un número que sea tuyo (o de alguien avisado) -- esto crea/edita de verdad un contacto en la cuenta real de GHL.</p>
        <form method="POST" action="{{ route('admin.ghl.test') }}">
            @csrf
            <label for="name">Nombre de prueba</label>
            <input type="text" id="name" name="name" value="{{ old('name', 'Prueba GHL') }}" required>
            <label for="phone">Teléfono</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone', $testedPhone ?? '') }}" placeholder="+56 9 1234 5678" required>
            <button class="btn" type="submit" style="margin-top:14px;">Probar ahora →</button>
        </form>
    </div>

    @if ($errors->any())
        <div class="card" style="border-color:#7a2d2d; background:#2a1414;">
            @foreach ($errors->all() as $error)
                <p style="color:#f3b8b8; margin:4px 0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @isset($log)
        <div class="card">
            <h2 style="font-size:14px; margin:0 0 10px;">Resultado</h2>
            @foreach ($log as $line)
                <p style="margin:4px 0; font-family:ui-monospace,monospace; font-size:12.5px; color:#c1c3c7;">{{ $line }}</p>
            @endforeach
            @if ($error)
                <p style="margin:10px 0 0; color:#f3b8b8;">✗ Error: {{ $error }}</p>
            @else
                <p style="margin:10px 0 0; color:#6ee7b7;">✓ Listo -- revisa el contacto en GHL para confirmar que se ve como esperas.</p>
            @endif
        </div>
    @endisset
@endsection
