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
    </div>

    <div class="card" style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
        <img src="{{ route('admin.reviews.qr_image') }}" alt="QR de opinión del huésped" style="width:150px; height:150px; border-radius:8px; flex:none;">
        <div>
            <strong style="display:block; font-size:15px; margin-bottom:4px;">QR para pedir la opinión del huésped</strong>
            <p class="sub" style="margin-bottom:10px;">Para pegar en recepción o entregar al huésped -- 4-5 estrellas van directo a Google, 0-3 quedan acá con el detalle.</p>
            <a class="link" href="{{ route('admin.reviews.qr_image') }}" target="_blank" rel="noopener">Descargar →</a>
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

    <div class="actions" style="justify-content:space-between; margin:32px 0 18px;">
        <div><h1 style="font-size:16px;">Reseñas públicas (Google / Facebook)</h1><p class="sub">Sincronizadas en tiempo real desde GHL apenas alguien publica una reseña real.</p></div>
    </div>
    <div class="card">
        <table>
            <thead><tr><th>Calificación</th><th>Fuente</th><th>Autor</th><th>Comentario</th><th>Fecha</th></tr></thead>
            <tbody>
                @forelse ($externalReviews as $review)
                    <tr>
                        <td style="{{ $review->rating && $review->rating <= 3 ? 'color:#e88a9a;' : 'color:#6fd39a;' }} font-weight:700; white-space:nowrap;">
                            {{ $review->rating ? str_repeat('★', $review->rating).str_repeat('☆', 5 - $review->rating) : '—' }}
                        </td>
                        <td class="sub" style="text-transform:capitalize;">{{ $review->source ?? '—' }}</td>
                        <td>{{ $review->reviewer_name ?? '—' }}</td>
                        <td style="max-width:320px;">{{ $review->comment ?? '—' }}</td>
                        <td class="sub">{{ ($review->reviewed_at ?? $review->created_at)->timezone('America/Santiago')->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="color:#666;">Todavía no llegó ninguna reseña por GHL -- falta configurar el Workflow "Reviews Received" allá (ver instrucciones).</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
