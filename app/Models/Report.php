<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['reporter_id', 'item_id', 'fund_request_id', 'reason', 'status'])]
class Report extends Model
{
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function fund(): BelongsTo
    {
        return $this->belongsTo(FundRequest::class, 'fund_request_id');
    }
}
