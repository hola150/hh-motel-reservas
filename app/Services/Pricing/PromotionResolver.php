<?php

namespace App\Services\Pricing;

use App\Exceptions\InvalidCouponException;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Room;
use Carbon\Carbon;

/**
 * Ofertas programadas: busca el mejor cupón con auto_apply que aplique a una
 * habitación en una fecha/hora/duración dada y lo devuelve ya evaluado. No
 * tira excepciones — una oferta que no aplica simplemente no se ofrece.
 */
class PromotionResolver
{
    public function __construct(private CouponValidator $validator)
    {
    }

    /**
     * @return array{coupon: Coupon, discount: int}|null
     */
    public function bestFor(Room $room, Carbon $startsAt, int $durationMinutes, ?Customer $customer, int $priceOriginal): ?array
    {
        $candidates = Coupon::query()
            ->offers()
            ->where('is_active', true)
            ->with(['rooms:id', 'roomCategories:id'])
            ->get();

        $best = null;

        foreach ($candidates as $coupon) {
            try {
                $discount = $this->validator->validate($coupon, $room, $startsAt, $durationMinutes, $customer, $priceOriginal);
            } catch (InvalidCouponException) {
                continue;
            }

            if (($discount > 0 || $coupon->included_extra_guests > 0) && ($best === null || $discount > $best['discount'])) {
                $best = ['coupon' => $coupon, 'discount' => $discount];
            }
        }

        return $best;
    }
}
