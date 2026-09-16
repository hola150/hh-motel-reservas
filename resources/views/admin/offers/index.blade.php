@extends('admin.layout')
@section('title', 'Ofertas')
@section('content')
    <style>
        .off-scope { font-size:12px; color:#888; margin-top:3px; }
        .off-scope b { color:#b088e8; }
        .pill-vigente { background:#1c3a2a; color:#6fd39a; }
        .pill-pausada { background:#3a331c; color:#e8c76f; }
        .pill-vencida { background:#2a2a2a; color:#999; }
        .toggle-btn { background:#2a2a2a; border:1px solid #444; color:#ccc; border-radius:6px; font-size:11.5px; padding:5px 10px; cursor:pointer; }
        .toggle-btn:hover { border-color:#ff7918; }
    </style>

    <div class="actions" style="justify-content:space-between; margin-bottom:18px;">
        <div>
            <h1>Ofertas</h1>
            <p class="sub">Precios especiales temporales por habitación o categoría — se aplican solos, sin código, cuando el cliente reserva dentro de la vigencia.</p>
        </div>
        <a class="btn" href="{{ route('admin.offers.create') }}">+ Nueva oferta</a>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Oferta</th><th>Descuento</th><th>Vigencia</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse ($offers as $offer)
                    @php
                        $venc = $offer->ends_at && $offer->ends_at->isPast();
                        $stateClass = ! $offer->is_active ? 'pill-pausada' : ($venc ? 'pill-vencida' : 'pill-vigente');
                        $stateLabel = ! $offer->is_active ? 'PAUSADA' : ($venc ? 'VENCIDA' : 'VIGENTE');
                    @endphp
                    <tr>
                        <td>
                            {{ $offer->internal_name }}
                            <div class="off-scope">
                                @if ($offer->roomCategories->isNotEmpty())
                                    Categorías: <b>{{ $offer->roomCategories->pluck('name')->implode(', ') }}</b>
                                @endif
                                @if ($offer->rooms->isNotEmpty())
                                    @if ($offer->roomCategories->isNotEmpty()) · @endif
                                    Habitaciones: <b>{{ $offer->rooms->pluck('name')->implode(', ') }}</b>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if ($offer->discount_type === 'percentage')
                                −{{ $offer->discount_value }}%
                            @else
                                ${{ number_format($offer->discount_value, 0, ',', '.') }} <span style="color:#888; font-size:11px;">precio fijo</span>
                            @endif
                        </td>
                        <td style="font-size:13px;">
                            {{ $offer->starts_at ? $offer->starts_at->format('d/m/Y') : 'sin inicio' }}
                            →
                            {{ $offer->ends_at ? $offer->ends_at->format('d/m/Y') : 'sin fin' }}
                        </td>
                        <td><span class="pill {{ $stateClass }}">{{ $stateLabel }}</span></td>
                        <td style="white-space:nowrap;">
                            <a class="link" href="{{ route('admin.offers.edit', $offer) }}">Editar</a>
                            <form method="POST" action="{{ route('admin.offers.toggle', $offer) }}" class="inline" style="margin-left:8px;">
                                @csrf
                                <button type="submit" class="toggle-btn">{{ $offer->is_active ? 'Pausar' : 'Activar' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="color:#666;">Todavía no hay ofertas. Creá una para que aparezcan habitaciones con precio especial al reservar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
