@extends('admin.layout')
@section('title', 'Opiniones')
@section('content')
    <style>
        /* Esta página sola, no el resto de /admin -- el .wrap compartido
           (880px) deja mucho espacio muerto a la derecha con el widget y
           la tabla, que sí aprovechan bien una columna más ancha. */
        .wrap { max-width: 1400px !important; }
        .opiniones-grid { display:grid; grid-template-columns: 1.6fr 1fr; gap:16px; align-items:start; }
        @media (max-width: 900px) { .opiniones-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="actions" style="justify-content:space-between; margin-bottom:18px;">
        <div><h1>Opiniones de huéspedes</h1><p class="sub">4-5 estrellas van directo a Google; 0-3 se quedan acá con el comentario.</p></div>
    </div>

    <div class="opiniones-grid">
        <div>
            <div class="card">
                <strong style="display:block; font-size:15px; margin-bottom:12px;">Puntuación real en Google</strong>
                <script type="text/javascript" src="https://link.hhmotel.cl/reputation/assets/review-widget.js"></script>
                <iframe class="lc_reviews_widget" src="https://link.hhmotel.cl/reputation/widgets/review_widget/ksYYfSiY8nP4YFvkrJFJ" frameborder="0" scrolling="auto" style="min-width:100%; width:100%; height:560px; border:none;"></iframe>
            </div>

            <div class="card">
                <div style="max-height:320px; overflow-y:auto;">
                <table>
                    <thead><tr><th style="position:sticky; top:0; background:#1c1c1c;">Calificación</th><th style="position:sticky; top:0; background:#1c1c1c;">Reserva</th><th style="position:sticky; top:0; background:#1c1c1c;">Cliente</th><th style="position:sticky; top:0; background:#1c1c1c;">Comentario</th><th style="position:sticky; top:0; background:#1c1c1c;">Fecha</th></tr></thead>
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
            </div>
        </div>

        <div>
            <div class="card" style="display:flex; gap:26px; flex-wrap:wrap;">
                <div>
                    <div class="sub" style="margin-bottom:2px;">Promedio interno (nuestro filtro 1-5)</div>
                    <div style="font-size:22px; font-weight:800;">{{ $avgRating ?: '—' }} ★</div>
                </div>
                <div>
                    <div class="sub" style="margin-bottom:2px;">Calificaciones bajas (0-3)</div>
                    <div style="font-size:22px; font-weight:800; {{ $lowCount > 0 ? 'color:#e88a9a;' : '' }}">{{ $lowCount }}</div>
                </div>
            </div>

            <div class="card">
                <img src="{{ route('admin.reviews.qr_image') }}" alt="QR de opinión del huésped" style="width:100%; max-width:220px; height:auto; border-radius:8px; display:block; margin:0 auto 14px;">
                <strong style="display:block; font-size:15px; margin-bottom:4px;">QR para pedir la opinión del huésped</strong>
                <p class="sub" style="margin-bottom:10px;">Para pegar en recepción o entregar al huésped -- 4-5 estrellas van directo a Google, 0-3 quedan acá con el detalle.</p>
                <a class="link" href="{{ route('admin.reviews.qr_image') }}" target="_blank" rel="noopener">Descargar →</a>
                &nbsp;·&nbsp;
                <a class="link" href="{{ route('reviews.create') }}" target="_blank" rel="noopener">Ver página →</a>
            </div>
        </div>
    </div>
@endsection
