<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Movimiento manual de caja chica — plata que entra (ingreso) o sale
 * (egreso) sin pasar por una reserva. El efectivo de las ventas se calcula
 * aparte, directo desde Payment (medio de pago = efectivo).
 */
#[Fillable(['type', 'amount', 'description', 'created_by'])]
class CashMovement extends Model
{
    //
}
