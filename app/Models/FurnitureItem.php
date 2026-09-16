<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['furniture_category_id', 'name', 'icon'])]
class FurnitureItem extends Model
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(FurnitureCategory::class, 'furniture_category_id');
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_furniture')
            ->withPivot(['quantity', 'condition', 'notes']);
    }
}
