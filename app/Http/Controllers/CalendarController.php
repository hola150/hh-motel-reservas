<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $view = in_array($request->query('view'), ['month', 'week', 'day'], true) ? $request->query('view') : 'month';
        $date = Carbon::parse($request->query('date', now()->toDateString()), config('app.timezone'));
        $rangeStart = $view === 'month' ? $date->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY) : $date->copy()->startOf($view === 'week' ? 'week' : 'day');
        $rangeEnd = $view === 'month' ? $date->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY) : $date->copy()->endOf($view === 'week' ? 'week' : 'day');

        $bookings = Booking::with(['room.category', 'customer', 'addons'])
            ->whereNotIn('booking_status', ['CANCELADA', 'EXPIRADA', 'NO_SHOW'])
            ->where('starts_at', '<', $rangeEnd)
            ->where('ends_at', '>', $rangeStart)
            ->orderBy('starts_at')
            ->get();

        $days = collect();
        for ($cursor = $rangeStart->copy(); $cursor->lte($rangeEnd); $cursor->addDay()) {
            $days->push(['date' => $cursor->copy(), 'bookings' => $bookings->filter(fn ($booking) => $booking->starts_at->lt($cursor->copy()->endOfDay()) && $booking->ends_at->gt($cursor->copy()->startOfDay()))]);
        }

        $previous = $date->copy()->sub($view === 'month' ? '1 month' : ($view === 'week' ? '1 week' : '1 day'))->toDateString();
        $next = $date->copy()->add($view === 'month' ? '1 month' : ($view === 'week' ? '1 week' : '1 day'))->toDateString();

        // Resumen del rango que se está mostrando (mes, semana o día según
        // $view) -- mismas reservas ya cargadas para el calendario, sin
        // consulta aparte.
        $roomsRevenue = (int) $bookings->sum('price_final');
        $extrasRevenue = (int) $bookings->flatMap->addons->sum('amount');
        $summary = [
            'count' => $bookings->count(),
            'rooms_revenue' => $roomsRevenue,
            'extras_revenue' => $extrasRevenue,
            'total' => $roomsRevenue + $extrasRevenue,
        ];

        return view('calendar.index', compact('view', 'date', 'rangeStart', 'rangeEnd', 'bookings', 'days', 'previous', 'next', 'summary'));
    }
}
