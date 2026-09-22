<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Integrations\GhlClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

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

    /**
     * El teléfono no se edita acá -- es la llave con la que identificamos al
     * cliente (y con la que GHL empareja el contacto); cambiarlo sería crear
     * una identidad distinta, no corregir un dato. Al guardar, sincroniza el
     * contacto en GHL al toque (no solo al crear una reserva nueva) para que
     * un correo agregado ahora mismo ya quede reflejado si el cliente
     * escribe por WhatsApp antes de su próxima estadía.
     */
    public function update(Request $request, Customer $customer, GhlClient $ghl): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'document_type' => ['nullable', 'in:rut,pasaporte'],
            'rut' => ['nullable', 'string', 'max:20'],
            'passport_number' => ['nullable', 'string', 'max:30'],
            'nationality' => ['nullable', 'string', 'max:60'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $customer->update($validated);

        $status = 'Datos del cliente actualizados.';
        try {
            if ($ghl->isConfigured()) {
                $contactId = $ghl->upsertContact($customer->phone_e164, $customer->name, $customer->email);
                $customer->update(['ghl_contact_id' => $customer->ghl_contact_id ?: $contactId, 'ghl_synced_at' => now()]);
                $status .= ' Sincronizado con GHL.';
            } else {
                $status .= ' (GHL no está configurado -- no se sincronizó.)';
            }
        } catch (Throwable $e) {
            Log::warning('GHL sync falló al editar el cliente '.$customer->id, ['error' => $e->getMessage()]);
            $status .= ' No se pudo sincronizar con GHL (se reintentará en su próxima reserva).';
        }

        return redirect()->route('admin.customers.show', $customer)->with('status', $status);
    }
}
