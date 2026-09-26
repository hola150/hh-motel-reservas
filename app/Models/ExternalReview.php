<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['source', 'rating', 'comment', 'reply', 'reviewer_name', 'external_id', 'reviewed_at', 'raw_payload'])]
class ExternalReview extends Model
{
    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }
}
