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

php artisan tinker --execute="
echo 'CHECK2 reservas='.App\Models\Booking::count().' clientes='.App\Models\Customer::count().' pagos='.App\Models\Payment::count().PHP_EOL;
foreach (App\Models\Booking::with(['customer','room'])->get() as \$b) {
    echo 'CHECK2 '.\$b->code.' | creada='.\$b->created_at->timezone('America/Santiago')->toDateTimeString().' | '.(\$b->customer->name ?? '?').' | '.(\$b->room->name ?? '?').' | '.\$b->booking_status.PHP_EOL;
}
" 2>&1 | grep 'CHECK2'

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
