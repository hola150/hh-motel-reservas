<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomCategory;
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
        $categories = RoomCategory::where('is_active', true)
            ->orderBy('display_order')
            ->with([
                'rooms',
                'rateRulePrices' => fn ($q) => $q->whereHas('rateRule', fn ($r) => $r->where('is_active', true))->with('rateRule'),
            ])
            ->get()
            ->map(function (RoomCategory $category) {
                $photos = $category->rooms->flatMap(fn ($room) => $room->photos ?? [])->unique()->values();
                $videos = $category->rooms->flatMap(fn ($room) => $room->videos ?? [])->unique()->values();

                return [
                    'category' => $category,
                    'photos' => $photos,
                    'videos' => $videos,
                    'prices' => $this->pricesFromRows($category->rateRulePrices),
                    'whatsappUrl' => 'https://wa.me/'.self::WHATSAPP_NUMBER.'?text='.rawurlencode("Hola! Quiero reservar una habitación {$category->name} en HH Motel."),
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
            'whatsappUrl' => 'https://wa.me/'.self::WHATSAPP_NUMBER.'?text='.rawurlencode("Hola! Quiero reservar la habitación {$room->name} en HH Motel."),
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
