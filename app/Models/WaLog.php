<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['request_id', 'direction', 'phone', 'message', 'send_status', 'sent_at'])]
class WaLog extends Model
{
    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(DonationRequest::class, 'request_id');
    }
}
