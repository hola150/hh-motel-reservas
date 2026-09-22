#!/bin/sh
set -e

php artisan migrate --force

# Los seeders base (categorías, habitaciones, tarifas, cupones, métodos de
# pago, productos) usan insert() plano, no son idempotentes -- solo se
# corren una vez, cuando la base recién creada todavía está vacía.
EMPTY_DB=$(php artisan tinker --execute="echo App\Models\RoomCategory::count();" 2>/dev/null | tail -1)
if [ "$EMPTY_DB" = "0" ]; then
    php artisan db:seed --force
fi

# LIMPIEZA TEMPORAL DE DATOS DE PRUEBA -- confirmado explícitamente por el
# usuario tras revisar el listado completo (15 reservas, 6 clientes, todas
# de prueba, incluida "Alain Marchant"). Payments primero (restrictOnDelete
# bloquea borrar la reserva si no). Booking_addons/guests/coupon_redemptions/
# integration_outbox/tracking_data se van solos por cascadeOnDelete. Se saca
# del arranque en el próximo commit -- corre una sola vez.
php artisan tinker --execute="
echo 'CLEANUP antes: reservas='.App\Models\Booking::count().' clientes='.App\Models\Customer::count().' pagos='.App\Models\Payment::count().PHP_EOL;
App\Models\Payment::query()->delete();
App\Models\Booking::query()->delete();
App\Models\Customer::query()->delete();
echo 'CLEANUP despues: reservas='.App\Models\Booking::count().' clientes='.App\Models\Customer::count().' pagos='.App\Models\Payment::count().PHP_EOL;
" 2>&1 | grep 'CLEANUP'

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
