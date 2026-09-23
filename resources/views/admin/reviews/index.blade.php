@extends('admin.layout')
@section('title', 'Opiniones')
@section('content')
    <div class="actions" style="justify-content:space-between; margin-bottom:18px;">
        <div><h1>Opiniones de huéspedes</h1><p class="sub">4-5 estrellas van directo a Google; 0-3 se quedan acá con el comentario.</p></div>
    </div>

    <div class="card" style="display:flex; gap:26px; flex-wrap:wrap;">
        <div>
            <div class="sub" style="margin-bottom:2px;">Promedio</div>
            <div style="font-size:22px; font-weight:800;">{{ $avgRating ?: '—' }} ★</div>
        </div>
        <div>
            <div class="sub" style="margin-bottom:2px;">Calificaciones bajas (0-3)</div>
            <div style="font-size:22px; font-weight:800; {{ $lowCount > 0 ? 'color:#e88a9a;' : '' }}">{{ $lowCount }}</div>
        </div>
        <div style="margin-left:auto; align-self:center;">
            <a class="link" href="{{ route('admin.reviews.qr_image') }}" target="_blank" rel="noopener">Descargar QR de opinión →</a>
            &nbsp;·&nbsp;
            <a class="link" href="{{ route('reviews.create') }}" target="_blank" rel="noopener">Ver página →</a>
        </div>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Calificación</th><th>Reserva</th><th>Cliente</th><th>Comentario</th><th>Fecha</th></tr></thead>
            <tbody>
                @forelse ($reviews as $review)
                    <tr>
                        <td style="{{ $review->rating <= 3 ? 'color:#e88a9a;' : 'color:#6fd39a;' }} font-weight:700; white-space:nowrap;">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</td>
                        <td>
                            @if ($review->booking)
                                <span class="pill">{{ $review->booking->code }}</span> {{ $review->booking->room->name }}
                            @else
                                <span class="sub">QR general</span>
                            @endif
                        </td>
                        <td>{{ $review->booking?->customer?->name ?? '—' }}</td>
                        <td style="max-width:320px;">{{ $review->comment ?? '—' }}</td>
                        <td class="sub">{{ $review->created_at->timezone('America/Santiago')->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="color:#666;">Todavía no hay opiniones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
