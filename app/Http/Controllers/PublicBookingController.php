<?php

namespace App\Http\Controllers;

use App\Exceptions\PricingException;
use App\Exceptions\RoomNotAvailableException;
use App\Models\Customer;
use App\Models\RateRulePrice;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Services\Booking\AvailabilityChecker;
use App\Services\Booking\BookingService;
use App\Support\Phone;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Reserva self-service desde el catálogo público -- sin login, sin
 * documento (se verifica en persona al check-in), sin cupones ni upsells.
 * Recepción ve la reserva igual que si hubiera entrado por teléfono: queda
 * PENDIENTE_PAGO, sin nada cobrado todavía.
 */
class PublicBookingController extends Controller
{
    public function create(Request $request): View
    {
        $categories = RoomCategory::where('is_active', true)->orderBy('display_order')->get();
        $selectedCategoryId = (int) $request->query('categoria', $categories->first()?->id);

        $durationsByCategory = RateRulePrice::select('room_category_id', 'duration_minutes')
            ->distinct()
            ->get()
            ->groupBy('room_category_id')
            ->map(fn ($rows) => $rows->pluck('duration_minutes')->map(fn ($m) => (int) $m)->unique()->sort()->values());

        return view('catalog.reservar', [
            'categories' => $categories,
            'selectedCategoryId' => $selectedCategoryId,
            'durationsByCategory' => $durationsByCategory,
        ]);
    }

    public function store(Request $request, BookingService $bookingService, AvailabilityChecker $availability): RedirectResponse
    {
        // "00" con cero a la izquierda no pasa la regla integer de Laravel
        // (FILTER_VALIDATE_INT la rechaza), y una hora en punto siempre manda "00".
        $request->merge([
            'time_hour' => is_numeric($request->input('time_hour')) ? (int) $request->input('time_hour') : $request->input('time_hour'),
            'time_minute' => is_numeric($request->input('time_minute')) ? (int) $request->input('time_minute') : $request->input('time_minute'),
        ]);

        $validated = $request->validate([
            'room_category_id' => ['required', 'exists:room_categories,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time_hour' => ['required', 'integer', 'min:0', 'max:23'],
            'time_minute' => ['required', 'integer', 'min:0', 'max:59'],
            'duration_minutes' => ['required', 'integer'],
            'guests_count' => ['required', 'integer', 'min:1', 'max:10'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            // Campo trampa para bots -- invisible para una persona real
            // (oculto por CSS), un bot que autocompleta todo lo llena.
            'website' => ['prohibited'],
        ]);

        $startsAt = Carbon::parse($validated['date'].' '.sprintf('%02d:%02d', $validated['time_hour'], $validated['time_minute']));
        $endsAt = $startsAt->copy()->addMinutes((int) $validated['duration_minutes']);

        $operationalSetting = \App\Models\OperationalSetting::current();
        $room = Room::where('room_category_id', $validated['room_category_id'])
            ->where('operational_status', 'activa')
            ->floorWingEnabled($operationalSetting)
            ->orderBy('name')
            ->get()
            ->first(fn (Room $r) => $availability->isAvailable($r, $startsAt, $endsAt));

        if (! $room) {
            return back()->withInput()->withErrors(['duration_minutes' => 'No hay habitaciones libres de ese tipo para ese horario — probá otra fecha, hora o duración.']);
        }

        $phone = Phone::toE164($validated['phone']);
        $customerName = trim($validated['first_name'].' '.$validated['last_name']);
        $customer = Customer::firstOrCreate(
            ['phone_e164' => $phone],
            ['name' => $customerName, 'email' => $validated['email'] ?? null]
        );

        try {
            $booking = $bookingService->create([
                'room' => $room,
                'customer' => $customer,
                'starts_at' => $startsAt,
                'duration_minutes' => (int) $validated['duration_minutes'],
                'guests_count' => (int) $validated['guests_count'],
                'coupon_code' => null,
                'deposit_amount' => 0,
                'created_by' => null,
                'verified_by' => null,
                'notes' => 'Reserva online desde el catálogo público.',
            ]);
        } catch (RoomNotAvailableException|PricingException $e) {
            return back()->withInput()->withErrors(['duration_minutes' => $e->getMessage()]);
        }

        return redirect()->route('catalog.booked', $booking->code);
    }

    public function booked(string $code): View
    {
        $booking = \App\Models\Booking::with(['room.category'])->where('code', $code)->firstOrFail();

        return view('catalog.booked', ['booking' => $booking]);
    }
}
