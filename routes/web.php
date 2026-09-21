<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ComboController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\UpsellController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\RateRuleController;
use App\Http\Controllers\Admin\RoomCategoryController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\BookingAddonController;
use App\Http\Controllers\BookingCancelController;
use App\Http\Controllers\BookingCheckInController;
use App\Http\Controllers\AseoSummaryController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CustomerLoyaltyController;
use App\Http\Controllers\DailyAseoController;
use App\Http\Controllers\DailySalesController;
use App\Http\Controllers\BookingFinalizeController;
use App\Http\Controllers\BookingPassController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationSearchController;
use App\Http\Controllers\RoomBoardController;
use App\Http\Controllers\RoomInspectionController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('rooms.board');
});

// Público -- sin login, pensado para compartir el link con clientes.
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogo/habitacion/{room}', [CatalogController::class, 'room'])->name('catalog.room');
Route::get('/catalogo/reservar', [PublicBookingController::class, 'create'])->name('catalog.reserve');
Route::post('/catalogo/reservar', [PublicBookingController::class, 'store'])->middleware('throttle:8,1')->name('catalog.reserve.store');
Route::get('/catalogo/reservado/{code}', [PublicBookingController::class, 'booked'])->name('catalog.booked');

Route::get('/login', [LoginController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware(['guest', 'throttle:8,1'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {

Route::get('/reservar', [ReservationController::class, 'create'])->name('reservations.create');
Route::get('/clientes/buscar', [ReservationController::class, 'lookupCustomer'])->middleware('throttle:40,1')->name('customers.lookup');
Route::get('/reservar/precio', [ReservationController::class, 'priceQuote'])->middleware('throttle:80,1')->name('reservations.price');
Route::get('/buscar', [ReservationSearchController::class, 'index'])->name('reservations.search');
Route::get('/calendario', [CalendarController::class, 'index'])->name('calendar.index');
Route::post('/reservar', [ReservationController::class, 'store'])->name('reservations.store');
Route::get('/reservas/{code}', [ReservationController::class, 'show'])->name('reservations.show');
Route::get('/reservas/{code}/pase', [BookingPassController::class, 'preview'])->name('bookings.pass.preview');
Route::get('/reservas/{code}/pase.pdf', [BookingPassController::class, 'download'])->name('bookings.pass.pdf');
Route::get('/reservas/{code}/editar', [ReservationController::class, 'edit'])->name('reservations.edit');
Route::put('/reservas/{code}', [ReservationController::class, 'update'])->name('reservations.update');

Route::get('/ventas/{date?}', [DailySalesController::class, 'show'])->name('sales.daily');
Route::get('/caja/{date?}', [CashRegisterController::class, 'show'])->name('cash.show');
Route::post('/caja', [CashRegisterController::class, 'store'])->name('cash.store');
Route::get('/aseo/semana/{date?}', [AseoSummaryController::class, 'week'])->name('aseo.week');
Route::get('/aseo/mes/{date?}', [AseoSummaryController::class, 'month'])->name('aseo.month');
Route::get('/aseo/{date?}', [DailyAseoController::class, 'show'])->name('aseo.daily');

Route::get('/habitaciones', [RoomBoardController::class, 'index'])->name('rooms.board');
Route::get('/habitaciones/proximas', [RoomBoardController::class, 'upcoming'])->name('rooms.upcoming');
Route::get('/habitaciones/{room}/reservas', [RoomBoardController::class, 'bookings'])->name('rooms.bookings');
Route::post('/habitaciones/{room}/estado', [RoomBoardController::class, 'updateStatus'])->name('rooms.status');
Route::post('/habitaciones/{room}/aseo-listo', [RoomBoardController::class, 'markAseoReady'])->name('rooms.aseo_ready');
Route::get('/mantencion', [RoomInspectionController::class, 'panel'])->name('rooms.inspections.panel');
Route::get('/habitaciones/{room}/inspeccion', [RoomInspectionController::class, 'create'])->name('rooms.inspections.create');
Route::post('/habitaciones/{room}/inspeccion', [RoomInspectionController::class, 'store'])->name('rooms.inspections.store');

Route::get('/reservas/{code}/pago', [PaymentController::class, 'create'])->name('payments.create');
Route::post('/reservas/{code}/pago', [PaymentController::class, 'store'])->name('payments.store');
Route::post('/reservas/{code}/consumo', [BookingAddonController::class, 'store'])->name('addons.store');
Route::post('/reservas/{code}/hora-adicional', [BookingAddonController::class, 'extraHour'])->name('addons.extra-hour');
Route::get('/reservas/{code}/checkin', [BookingCheckInController::class, 'show'])->name('bookings.checkin.show');
Route::post('/reservas/{code}/checkin', [BookingCheckInController::class, 'store'])->name('bookings.checkin');
Route::post('/reservas/{code}/cancelar', [BookingCancelController::class, 'store'])->name('bookings.cancel');
Route::post('/reservas/{code}/fidelizacion', [CustomerLoyaltyController::class, 'store'])->name('customers.loyalty');
Route::get('/reservas/{code}/finalizar', [BookingFinalizeController::class, 'show'])->name('bookings.finalize.show');
Route::post('/reservas/{code}/finalizar', [BookingFinalizeController::class, 'store'])->name('bookings.finalize.store');

}); // fin del grupo auth

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    // Configuración -- solo administrador. Clientes/Productos/Combos/Analytics
    // quedan fuera de este bloque porque son herramientas de uso diario que
    // recepción también necesita (ver riel izquierdo del tablero).
    Route::middleware('role:administrador')->group(function () {
    Route::get('/mobiliario', [\App\Http\Controllers\Admin\FurnitureController::class, 'index'])->name('furniture.index');
    Route::post('/mobiliario/categorias', [\App\Http\Controllers\Admin\FurnitureController::class, 'category'])->name('furniture.categories.store');
    Route::put('/mobiliario/categorias/{category}', [\App\Http\Controllers\Admin\FurnitureController::class, 'category'])->name('furniture.categories.update');
    Route::delete('/mobiliario/categorias/{category}', [\App\Http\Controllers\Admin\FurnitureController::class, 'destroyCategory'])->name('furniture.categories.destroy');
    Route::post('/mobiliario/elementos', [\App\Http\Controllers\Admin\FurnitureController::class, 'item'])->name('furniture.items.store');
    Route::put('/mobiliario/elementos/{item}', [\App\Http\Controllers\Admin\FurnitureController::class, 'item'])->name('furniture.items.update');
    Route::delete('/mobiliario/elementos/{item}', [\App\Http\Controllers\Admin\FurnitureController::class, 'destroyItem'])->name('furniture.items.destroy');
    Route::redirect('/', '/admin/categorias');

    Route::get('/categorias', [RoomCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categorias/crear', [RoomCategoryController::class, 'create'])->name('categories.create');
    Route::post('/categorias', [RoomCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categorias/{category}/editar', [RoomCategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categorias/{category}', [RoomCategoryController::class, 'update'])->name('categories.update');
    Route::post('/categorias/{category}/toggle', [RoomCategoryController::class, 'toggleCategory'])->name('categories.toggle');
    Route::post('/ala-sur/toggle', [RoomCategoryController::class, 'toggleAlaSur'])->name('ala_sur.toggle');
    Route::post('/pisos/{floor}/toggle', [RoomCategoryController::class, 'toggleFloor'])->name('floors.toggle');

    Route::get('/habitaciones', [AdminRoomController::class, 'index'])->name('rooms.index');
    Route::get('/habitaciones/crear', [AdminRoomController::class, 'create'])->name('rooms.create');
    Route::post('/habitaciones', [AdminRoomController::class, 'store'])->name('rooms.store');
    Route::get('/habitaciones/{room}/editar', [AdminRoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/habitaciones/{room}', [AdminRoomController::class, 'update'])->name('rooms.update');

    Route::get('/tarifas', [RateRuleController::class, 'index'])->name('rates.index');
    Route::get('/tarifas/{rateRule}', [RateRuleController::class, 'edit'])->name('rates.edit');
    Route::put('/tarifas/{rateRule}', [RateRuleController::class, 'update'])->name('rates.update');
    Route::post('/tarifas/{rateRule}/precios', [RateRuleController::class, 'storePrice'])->name('rates.prices.store');
    Route::put('/tarifas/precios/{price}', [RateRuleController::class, 'updatePrice'])->name('rates.prices.update');
    Route::delete('/tarifas/precios/{price}', [RateRuleController::class, 'destroyPrice'])->name('rates.prices.destroy');

    Route::get('/ofertas', [OfferController::class, 'index'])->name('offers.index');
    Route::get('/ofertas/crear', [OfferController::class, 'create'])->name('offers.create');
    Route::post('/ofertas', [OfferController::class, 'store'])->name('offers.store');
    Route::get('/ofertas/{offer}/editar', [OfferController::class, 'edit'])->name('offers.edit');
    Route::put('/ofertas/{offer}', [OfferController::class, 'update'])->name('offers.update');
    Route::post('/ofertas/{offer}/toggle', [OfferController::class, 'toggle'])->name('offers.toggle');

    Route::get('/upsells', [UpsellController::class, 'index'])->name('upsells.index');
    Route::get('/upsells/crear', [UpsellController::class, 'create'])->name('upsells.create');
    Route::post('/upsells', [UpsellController::class, 'store'])->name('upsells.store');
    Route::get('/upsells/{upsell}/editar', [UpsellController::class, 'edit'])->name('upsells.edit');
    Route::put('/upsells/{upsell}', [UpsellController::class, 'update'])->name('upsells.update');
    Route::post('/upsells/{upsell}/toggle', [UpsellController::class, 'toggle'])->name('upsells.toggle');

    Route::get('/cupones', [CouponController::class, 'index'])->name('coupons.index');
    Route::get('/cupones/crear', [CouponController::class, 'create'])->name('coupons.create');
    Route::post('/cupones', [CouponController::class, 'store'])->name('coupons.store');
    Route::get('/cupones/{coupon}/editar', [CouponController::class, 'edit'])->name('coupons.edit');
    Route::put('/cupones/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
    }); // fin role:administrador

    Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
    Route::get('/productos/crear', [ProductController::class, 'create'])->name('products.create');
    Route::post('/productos', [ProductController::class, 'store'])->name('products.store');
    Route::get('/productos/{product}/editar', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/productos/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::post('/productos/{product}/stock', [ProductController::class, 'restock'])->name('products.restock');

    Route::get('/combos', [ComboController::class, 'index'])->name('combos.index');
    Route::get('/combos/crear', [ComboController::class, 'create'])->name('combos.create');
    Route::post('/combos', [ComboController::class, 'store'])->name('combos.store');
    Route::get('/combos/{combo}/editar', [ComboController::class, 'edit'])->name('combos.edit');
    Route::put('/combos/{combo}', [ComboController::class, 'update'])->name('combos.update');
    Route::post('/combos/{combo}/items', [ComboController::class, 'storeItem'])->name('combos.items.store');
    Route::delete('/combos/{combo}/items/{item}', [ComboController::class, 'destroyItem'])->name('combos.items.destroy');

    Route::get('/clientes', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/clientes/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/exportar', [AnalyticsController::class, 'export'])->name('analytics.export');

    Route::middleware('role:administrador')->group(function () {
    Route::get('/personal', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/personal', [StaffController::class, 'store'])->name('staff.store');
    Route::put('/personal/{staff}', [StaffController::class, 'update'])->name('staff.update');

    Route::get('/turnos', [ShiftController::class, 'index'])->name('shifts.index');
    Route::post('/turnos', [ShiftController::class, 'store'])->name('shifts.store');
    Route::put('/turnos/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
    Route::delete('/turnos/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');
    }); // fin role:administrador
});
