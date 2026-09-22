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

# DIAGNÓSTICO TEMPORAL -- listar todas las reservas antes de decidir si se
# limpian como datos de prueba. Solo lectura. Se saca en el próximo commit.
php artisan tinker --execute="
echo 'BOOKDIAG total_reservas='.App\Models\Booking::count().PHP_EOL;
echo 'BOOKDIAG total_clientes='.App\Models\Customer::count().PHP_EOL;
echo 'BOOKDIAG total_pagos='.App\Models\Payment::count().PHP_EOL;
foreach (App\Models\Booking::with('customer')->orderBy('created_at')->get() as \$b) {
    echo 'BOOKDIAG '.\$b->code.' | '.\$b->created_at->toDateTimeString().' | cliente='.(\$b->customer->name ?? '?').' '.(\$b->customer->phone_e164 ?? '?').' | estado='.\$b->booking_status.' | pago='.\$b->payment_status.PHP_EOL;
}
" 2>&1 | grep 'BOOKDIAG'

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
