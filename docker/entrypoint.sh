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

# DIAGNÓSTICO TEMPORAL -- para decidir la corrección histórica de timezone
# (ver commit 5277d74). Se saca en el próximo commit, esto es de una sola
# pasada para leer el resultado en los logs de Render.
php artisan tinker --execute="
\$cutoff = \Carbon\Carbon::parse('2026-09-21 15:34:00', 'UTC');
echo 'TZDIAG total_antes_del_fix='.App\Models\Booking::where('created_at', '<', \$cutoff)->count().PHP_EOL;
\$afectadas = App\Models\Booking::where('created_at', '<', \$cutoff)
    ->whereNotIn('booking_status', ['CANCELADA', 'EXPIRADA'])
    ->where('starts_at', '>', now()->subHours(3))
    ->orderBy('starts_at')
    ->get(['code', 'starts_at', 'booking_status']);
echo 'TZDIAG con_impacto_operativo='.\$afectadas->count().PHP_EOL;
foreach (\$afectadas as \$b) {
    echo 'TZDIAG '.\$b->code.' | guardado='.\$b->starts_at->toIso8601String().' | corregido='.\$b->starts_at->copy()->addHours(3)->toIso8601String().' | '.\$b->booking_status.PHP_EOL;
}
" 2>&1 | grep 'TZDIAG'

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
