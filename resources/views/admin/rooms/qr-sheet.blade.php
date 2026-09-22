@extends('admin.layout')
@section('title', 'QR de habitaciones')
@section('content')
    <div class="actions" style="justify-content:space-between; margin-bottom:18px;">
        <div><h1>QR de habitaciones</h1><p class="sub">Uno por Playroom, para imprimir y pegar en la puerta. Ctrl/Cmd+P para imprimir esta página tal cual.</p></div>
        <a class="btn secondary" href="{{ route('rooms.qr.mucama_panel') }}" target="_blank" rel="noopener">Ver QR general de mucamas ↗</a>
    </div>
    <div class="qr-sheet-grid">
        @foreach ($rooms as $room)
            <div class="qr-sheet-item">
                <img src="{{ route('rooms.qr.image', $room) }}" alt="QR de {{ $room->name }}">
                <strong>{{ $room->name }}</strong>
                <span>{{ $room->category->name }}</span>
                <a class="link" href="{{ route('rooms.qr.image', $room) }}" target="_blank" rel="noopener">Descargar →</a>
            </div>
        @endforeach
    </div>
    <style>
        .qr-sheet-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:18px; }
        .qr-sheet-item { background:#fff; border-radius:10px; padding:14px; text-align:center; }
        .qr-sheet-item img { width:100%; height:auto; border-radius:6px; margin-bottom:8px; }
        .qr-sheet-item strong { display:block; color:#111; font-size:14px; }
        .qr-sheet-item span { display:block; color:#666; font-size:11.5px; margin-bottom:6px; }
        @media print {
            .hh-rail, .hh-nav-wrap, .actions, header, nav { display:none !important; }
            body { padding:0 !important; background:#fff !important; }
            .qr-sheet-grid { grid-template-columns:repeat(3, 1fr); }
            .qr-sheet-item { break-inside:avoid; border:1px solid #ddd; }
            .qr-sheet-item a.link { display:none; }
        }
    </style>
@endsection
