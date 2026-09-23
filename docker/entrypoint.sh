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
echo 'ACCTDIAG --- users ---'.PHP_EOL;
foreach (App\Models\User::all() as \$u) { echo 'ACCTDIAG '.\$u->email.' | rol='.\$u->role.' | activo='.(\$u->is_active?'si':'no').PHP_EOL; }
echo 'ACCTDIAG --- mucamas ---'.PHP_EOL;
foreach (App\Models\Staff::where('role','Mucama')->get() as \$s) { echo 'ACCTDIAG '.\$s->name.' | pin='.(\$s->pin?'asignado':'sin PIN').' | activo='.(\$s->is_active?'si':'no').PHP_EOL; }
" 2>&1 | grep 'ACCTDIAG'

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
