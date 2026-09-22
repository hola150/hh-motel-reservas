<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['type', 'booking_id', 'payload', 'status', 'attempts', 'last_error', 'processed_at'])]
class IntegrationOutbox extends Model
{
    // La migración creó la tabla en singular ("integration_outbox") -- sin
    // esto, Laravel busca "integration_outboxes" (plural por convención,
    // "Outbox" -> "Outboxes") y no existe, así que CADA sync a GHL fallaba
    // con "relation does not exist" antes de siquiera intentar la llamada.
    protected $table = 'integration_outbox';

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
