<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $segmentFilter = (string) $request->query('segment', '');

        $customers = Customer::query()
            ->when($query !== '', function ($q) use ($query) {
                $digits = preg_replace('/\D/', '', $query);
                $q->where(function ($c) use ($query, $digits) {
                    $c->where('name', 'ilike', "%{$query}%");
                    if ($digits !== '') {
                        $c->orWhere('phone_e164', 'ilike', "%{$digits}%");
                    }
                });
            })
            ->withCount(['bookings as bookings_count' => function ($q) {
                $q->whereNotIn('booking_status', ['CANCELADA', 'EXPIRADA']);
            }])
            ->orderByDesc('bookings_count')
            ->get()
            ->map(function (Customer $customer) {
                $customer->computed_segment = $customer->segment();

                return $customer;
            });

        if ($segmentFilter !== '') {
            $customers = $customers->filter(fn (Customer $c) => $c->computed_segment['type'] === $segmentFilter)->values();
        }

        return view('admin.customers.index', ['customers' => $customers, 'query' => $query, 'segmentFilter' => $segmentFilter]);
    }

    public function show(Customer $customer): View
    {
        $now = now();

        $upcoming = $customer->bookings()
            ->with('room.category')
            ->whereNotIn('booking_status', ['CANCELADA', 'EXPIRADA'])
            ->where('ends_at', '>=', $now)
            ->orderBy('starts_at')
            ->get();

        $past = $customer->bookings()
            ->with('room.category')
            ->where('ends_at', '<', $now)
            ->orderByDesc('starts_at')
            ->get();

        return view('admin.customers.show', [
            'customer' => $customer,
            'segment' => $customer->segment(),
            'monthlyVisits' => $customer->monthlyVisits(),
            'upcoming' => $upcoming,
            'past' => $past,
            'now' => $now,
        ]);
    }
}
