<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestReview;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Response;
use Illuminate\View\View;

class GuestReviewController extends Controller
{
    public function index(): View
    {
        $reviews = GuestReview::with('booking.customer', 'booking.room')
            ->latest()
            ->limit(200)
            ->get();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'lowCount' => GuestReview::where('rating', '<=', 3)->count(),
            'avgRating' => round((float) GuestReview::avg('rating'), 1),
        ]);
    }

    /**
     * QR fijo (sin reserva asociada) para pegar en recepción -- apunta al
     * mismo /opinion que se manda por link después del check-out.
     */
    public function qrImage(): Response
    {
        $result = (new Builder(
            writer: new PngWriter(),
            data: route('reviews.create'),
            size: 400,
            margin: 12,
            labelText: 'Danos tu opinión',
        ))->build();

        return response($result->getString(), 200, ['Content-Type' => $result->getMimeType()]);
    }
}
