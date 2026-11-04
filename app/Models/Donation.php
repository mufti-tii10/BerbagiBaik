<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['fund_request_id', 'donor_id', 'amount', 'proof_photo_url', 'message', 'is_anonymous', 'status', 'confirmed_at'])]
class Donation extends Model
{
    protected function casts(): array
    {
        return ['is_anonymous' => 'boolean', 'confirmed_at' => 'datetime', 'amount' => 'integer'];
    }

    public function fundRequest(): BelongsTo
    {
        return $this->belongsTo(FundRequest::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }
}
