<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'phone_e164', 'email', 'rut', 'document_type', 'passport_number',
    'nationality', 'birth_date', 'ghl_contact_id', 'ghl_synced_at',
    'has_loyalty_card', 'loyalty_card_number', 'loyalty_card_added_at',
    'blacklist_status', 'blacklist_reason', 'blacklisted_at',
])]
class Customer extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'ghl_synced_at' => 'datetime',
            'has_loyalty_card' => 'boolean',
            'loyalty_card_added_at' => 'datetime',
            'blacklisted_at' => 'datetime',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** Edad en años cumplidos, o null si no se cargó fecha de nacimiento. */
    public function age(): ?int
    {
        return $this->birth_date?->age;
    }

    /** Número de documento según el tipo cargado — RUT o pasaporte. */
    public function documentNumber(): ?string
    {
        return $this->document_type === 'pasaporte' ? $this->passport_number : $this->rut;
    }

    public function isFirstBooking(): bool
    {
        return $this->bookings()
            ->whereNotIn('booking_status', ['CANCELADA', 'EXPIRADA'])
            ->doesntExist();
    }

    /**
     * Cuántas estadías tiene en el mes calendario en curso (sin canceladas /
     * expiradas). 3+ = "recurrente del mes" — cliente caliente ahora mismo,
     * más allá de su segmento histórico.
     *
     * Solo cuenta las que YA arrancaron (starts_at <= ahora) — igual que
     * segment()'s total_stays. Una reserva agendada para más adelante en el
     * mes no es una "visita" todavía, y contarla confundía a recepción
     * cuando "reservas este mes" salía más alto que las "estadías" totales.
     */
    public function monthlyVisits(): int
    {
        return $this->bookings()
            ->whereNotIn('booking_status', ['CANCELADA', 'EXPIRADA'])
            ->where('starts_at', '<=', now())
            ->whereBetween('starts_at', [
                now('America/Santiago')->startOfMonth()->timezone('UTC'),
                now('America/Santiago')->endOfMonth()->timezone('UTC'),
            ])
            ->count();
    }

    /**
     * Clasifica al cliente según su historial real de estadías (nunca canceladas/expiradas):
     * cuántas veces vino, y qué tan seguido. No usa reservas futuras, solo lo que ya pasó.
     *
     * - nuevo: 0 o 1 estadía en total.
     * - esporádico: tiene historial, pero la última vez que vino fue hace más de 120 días
     *   (o nunca llegó a alojarse) — se desconectó, sin importar cuánto venía antes.
     *   Alto valor detectarlo para reactivarlo con una promo.
     * - frecuente: 2+ estadías, la última fue hace 120 días o menos, y en promedio
     *   vuelve cada 45 días o menos.
     * - ocasional: tiene historial reciente pero con más espacio entre visitas que lo anterior.
     *
     * @return array{type: string, label: string, total_stays: int, last_visit_at: ?\Carbon\Carbon, days_since_last: ?int, avg_interval_days: ?int}
     */
    public function segment(): array
    {
        $stays = $this->bookings()
            ->whereNotIn('booking_status', ['CANCELADA', 'EXPIRADA'])
            ->where('starts_at', '<=', now())
            ->orderBy('starts_at')
            ->pluck('starts_at');

        $total = $stays->count();

        if ($total <= 1) {
            return [
                'type' => 'nuevo', 'label' => 'Cliente nuevo', 'total_stays' => $total,
                'last_visit_at' => $stays->last(), 'days_since_last' => null, 'avg_interval_days' => null,
            ];
        }

        $lastVisit = $stays->last();
        $daysSinceLast = (int) $lastVisit->diffInDays(now());
        $spanDays = $stays->first()->diffInDays($lastVisit);
        $avgInterval = (int) round($spanDays / ($total - 1));

        $rules = CustomerSegmentRule::orderByDesc('stars')->orderByDesc('minimum_stays')->get();
        // "nuevo" y "esporadico" no entran a esta comparación por umbral: nuevo
        // ya se resolvió arriba (total<=1) y no debería volver a matchear acá
        // (con minimum_stays=0 matchea SIEMPRE, dejando "esporadico" inalcanzable
        // -- cualquier cliente inactivo hace 200 días terminaba mostrando "Cliente
        // nuevo" en vez de "Esporádico"). Esporádico se evalúa aparte, por
        // antigüedad de la última visita, no por cantidad de visitas recientes.
        $matched = null;
        foreach ($rules as $rule) {
            if (in_array($rule->segment, ['esporadico', 'nuevo'], true)) continue;
            $window = $rule->analysis_window_days;
            $visits = $window ? $stays->filter(fn ($date) => $date->greaterThanOrEqualTo(now()->subDays($window)))->count() : $total;
            if ($visits >= (int) $rule->minimum_stays) { $matched = $rule; break; }
        }
        if ($matched === null) {
            $stale = $rules->firstWhere('segment', 'esporadico');
            if ($stale && $daysSinceLast > ($stale->stale_after_days ?? 120)) { $matched = $stale; }
        }
        // Si ni un umbral de visitas ni el de inactividad calzó (ej. 90-120 días
        // sin volver), cae al criterio "ocasional" real de la base, no a un
        // string hardcodeado -- así respeta la etiqueta/estrellas configuradas.
        $matched ??= $rules->firstWhere('segment', 'ocasional');

        $type = $matched?->segment ?? 'ocasional';
        $label = $matched?->label ?? 'Baja recurrencia';
        $window = $matched?->analysis_window_days;
        $windowVisits = $window ? $stays->filter(fn ($date) => $date->greaterThanOrEqualTo(now()->subDays($window)))->count() : $total;

        return [
            'type' => $type, 'label' => $label, 'total_stays' => $total,
            'last_visit_at' => $lastVisit, 'days_since_last' => $daysSinceLast, 'avg_interval_days' => $avgInterval,
            'window_days' => $window, 'window_visits' => $windowVisits, 'stars' => (int) ($matched?->stars ?? 0),
        ];
    }
}
