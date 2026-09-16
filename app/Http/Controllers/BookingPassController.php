<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class BookingPassController extends Controller
{
    public function download(string $code): Response
    {
        $booking = Booking::with(['room.category', 'customer', 'payments', 'addons'])->where('code', $code)->firstOrFail();

        return Pdf::loadView('reservations.pass-pdf', [
            'booking' => $booking,
            'paid' => $booking->paidAmount(),
            'balance' => $booking->balanceDue(),
            'logo' => public_path('images/hh-motel-logo.png'),
        ])->setPaper('a5', 'portrait')->download('pase-'.$booking->code.'.pdf');
    }
}
