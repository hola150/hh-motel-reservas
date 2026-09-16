@extends('admin.layout')
@section('title', 'Upsells')
@section('content')
    <style>
        .u-type { font-size:11px; padding:2px 8px; border-radius:20px; }
        .u-type.category_upgrade { background:#241f2e; color:#c9a6f5; }
        .u-type.time_extension { background:#1c2f3a; color:#7fbcdc; }
        .u-type.combo { background:#1c3a2a; color:#6fd39a; }
        .u-detail { font-size:12px; color:#888; margin-top:3px; }
        .toggle-btn { background:#2a2a2a; border:1px solid #444; color:#ccc; border-radius:6px; font-size:11.5px; padding:5px 10px; cursor:pointer; }
        .toggle-btn:hover { border-color:#ff7918; }
    </style>

    <div class="actions" style="justify-content:space-between; margin-bottom:18px;">
        <div>
            <h1>Upsells</h1>
            <p class="sub">Sugerencias con precio fijo que recepción ofrece al reservar o al llegar el cliente — subir de categoría, sumar tiempo, o un pack.</p>
        </div>
        <a class="btn" href="{{ route('admin.upsells.create') }}">+ Nuevo upsell</a>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Upsell</th><th>Tipo</th><th>Precio</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse ($offers as $o)
                    <tr>
                        <td>
                            {{ $o->name }}
                            <div class="u-detail">
                                @if ($o->type === 'category_upgrade')
                                    {{ $o->fromCategory?->name }} → {{ $o->toCategory?->name }}
                                @elseif ($o->type === 'time_extension')
                                    +{{ $o->extra_minutes / 60 }} h de estadía
                                @else
                                    Combo: {{ $o->combo?->name ?? '—' }}
                                @endif
                            </div>
                        </td>
                        <td><span class="u-type {{ $o->type }}">
                            {{ ['category_upgrade' => 'Subir categoría', 'time_extension' => 'Más tiempo', 'combo' => 'Pack'][$o->type] }}
                        </span></td>
                        <td>${{ number_format($o->chargeAmount(), 0, ',', '.') }}</td>
                        <td><span class="pill">{{ $o->is_active ? 'ACTIVO' : 'PAUSADO' }}</span></td>
                        <td style="white-space:nowrap;">
                            <a class="link" href="{{ route('admin.upsells.edit', $o) }}">Editar</a>
                            <form method="POST" action="{{ route('admin.upsells.toggle', $o) }}" class="inline" style="margin-left:8px;">
                                @csrf
                                <button type="submit" class="toggle-btn">{{ $o->is_active ? 'Pausar' : 'Activar' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="color:#666;">Sin upsells todavía. Creá uno para que aparezca al reservar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
