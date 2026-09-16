<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationSearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $results = collect();
        $customerCard = null;

        if ($query !== '') {
            $phoneDigits = preg_replace('/\D/', '', $query);

            $results = Booking::with(['room.category', 'customer'])
                ->where(function ($q) use ($query, $phoneDigits) {
                    $q->where('code', 'ilike', "%{$query}%")
                        ->orWhereHas('customer', function ($c) use ($query, $phoneDigits) {
                            $c->where('name', 'ilike', "%{$query}%");
                            if ($phoneDigits !== '') {
                                $c->orWhere('phone_e164', 'ilike', "%{$phoneDigits}%");
                            }
                        });
                })
                ->orderByDesc('starts_at')
                ->limit(25)
                ->get();

            // Si todos los resultados son del mismo cliente, se muestra su
            // ficha resumen arriba — categoría + historial de un vistazo.
            $customers = $results->pluck('customer')->unique('id');
            if ($customers->count() === 1) {
                $c = $customers->first();
                $seg = $c->segment();
                $customerCard = [
                    'model' => $c,
                    'segment' => $seg,
                    'monthly_visits' => $c->monthlyVisits(),
                ];
            }
        }

        return view('reservations.search', [
            'query' => $query,
            'results' => $results,
            'customerCard' => $customerCard,
        ]);
    }
}
