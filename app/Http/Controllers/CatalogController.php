<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Número de WhatsApp del motel para el botón "Reservar" -- la reserva
     * real la sigue creando recepción, este catálogo es solo para que el
     * cliente vea las habitaciones y escriba.
     */
    private const WHATSAPP_NUMBER = '56977683108';

    public function index(): View
    {
        $today = Carbon::today();
        $liveOffers = Coupon::offers()->where('is_active', true)
            ->with(['rooms:id,room_category_id', 'roomCategories:id'])
            ->get()
            ->filter(fn (Coupon $offer) => (!$offer->starts_at || $today->greaterThanOrEqualTo($offer->starts_at)) && (!$offer->ends_at || $today->lessThanOrEqualTo($offer->ends_at)));
        $categories = RoomCategory::where('is_active', true)
            ->orderBy('display_order')
            ->with([
                'rooms',
                'rateRulePrices' => fn ($q) => $q->whereHas('rateRule', fn ($r) => $r->where('is_active', true))->with('rateRule'),
            ])
            ->get()
            ->map(function (RoomCategory $category) use ($liveOffers) {
                $photos = $category->rooms->flatMap(fn ($room) => $room->photos ?? [])->unique()->values();
                $videos = $category->rooms->flatMap(fn ($room) => $room->videos ?? [])->unique()->values();

                $offer = $liveOffers->first(function (Coupon $offer) use ($category) {
                    $categoryIds = $offer->roomCategories->pluck('id');
                    $roomCategoryIds = $offer->rooms->pluck('room_category_id');
                    return ($categoryIds->isEmpty() && $roomCategoryIds->isEmpty()) || $categoryIds->contains($category->id) || $roomCategoryIds->contains($category->id);
                });
                $salesTips = [
                    'GO' => 'Ambiente íntimo con mobiliario seleccionado para disfrutar una experiencia diferente.',
                    'LITE' => 'Privacidad y comodidad en un ambiente equipado para compartir sin apuros.',
                    'NEW LITE' => 'Baño interior con ducha integrada al ambiente, visible desde la cama.',
                    'PLUS' => 'Un espacio preparado para disfrutar su mobiliario y vivir una experiencia más intensa.',
                    'MAX' => 'Ambiente amplio para explorar y disfrutar en compañía; una de las preferidas para grupos de más de dos personas.',
                ];
                return [
                    'category' => $category,
                    'photos' => $photos,
                    'videos' => $videos,
                    'prices' => $this->pricesFromRows($category->rateRulePrices),
                    'offer' => $offer ? [
                        'label' => $offer->discount_type === 'percentage' ? $offer->discount_value.'% de descuento' : 'Desde $'.number_format($offer->discount_value, 0, ',', '.'),
                        'name' => $offer->internal_name,
                        'rooms' => $offer->rooms->filter(fn ($room) => $room->room_category_id === $category->id)->map(fn ($room) => ['id' => $room->id, 'name' => $room->name])->values()->all(),
                    ] : null,
                    'salesTip' => $salesTips[strtoupper($category->name)] ?? 'Conoce esta experiencia HH Motel.',
                    'whatsappUrl' => 'https://wa.me/'.self::WHATSAPP_NUMBER.'?text='.rawurlencode("Hola! Tengo una duda sobre el Playroom {$category->name} en HH Motel."),
                ];
            });

        return view('catalog.index', [
            'categories' => $categories,
            'whatsappUrl' => 'https://wa.me/'.self::WHATSAPP_NUMBER.'?text='.rawurlencode('Hola! Quiero reservar una habitación en HH Motel.'),
        ]);
    }

    /**
     * Ficha pública de una habitación puntual (no toda la categoría) --
     * pensada para mandar por WhatsApp a un cliente que pregunta por ESA
     * pieza específica, con sus propias fotos/videos.
     */
    public function room(Room $room): View
    {
        $room->load('category');

        return view('catalog.room', [
            'room' => $room,
            'category' => $room->category,
            'photos' => collect($room->photos ?? [])->values(),
            'videos' => collect($room->videos ?? [])->values(),
            'prices' => $this->pricesFromRows(
                $room->category->rateRulePrices()->whereHas('rateRule', fn ($r) => $r->where('is_active', true))->with('rateRule')->get()
            ),
        ]);
    }

    private function pricesFromRows(Collection $rows): Collection
    {
        return $rows
            ->groupBy('duration_minutes')
            ->map(fn ($rows, $duration) => [
                'duration' => (int) $duration,
                'hh' => $rows->first(fn ($p) => $p->rateRule->name === 'HH')?->price,
                'hot' => $rows->first(fn ($p) => $p->rateRule->name === 'HOT')?->price,
            ])
            ->sortBy('duration')
            ->values();
    }
}
