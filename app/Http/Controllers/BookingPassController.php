<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class BookingPassController extends Controller
{
    private function pdf(string $code)
    {
        $booking = Booking::with(['room.category', 'customer', 'payments', 'addons'])->where('code', $code)->firstOrFail();

        return Pdf::loadView('reservations.pass-pdf', [
            'booking' => $booking,
            'paid' => $booking->paidAmount(),
            'balance' => $booking->balanceDue(),
            'logo' => public_path('images/hh-motel-logo.png'),
        ])->setPaper([0, 0, 396, 720], 'portrait');
    }

    public function preview(string $code): Response
    {
        return $this->pdf($code)->stream('pase-'.$code.'.pdf');
    }

    public function download(string $code): Response
    {
        return $this->pdf($code)->download('pase-'.$code.'.pdf');
    }
}
