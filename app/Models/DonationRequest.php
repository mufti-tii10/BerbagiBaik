<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['item_id', 'receiver_id', 'reason', 'status', 'confirmation_code', 'code_expires_at', 'confirmed_at', 'proof_photo_url'])]
class DonationRequest extends Model
{
    protected $table = 'requests';

    protected function casts(): array
    {
        return [
            'code_expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function waLogs(): HasMany
    {
        return $this->hasMany(WaLog::class, 'request_id');
    }
}
