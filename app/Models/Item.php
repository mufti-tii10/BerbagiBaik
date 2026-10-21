<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['donor_id', 'category_id', 'name', 'description', 'condition', 'location', 'photo_url', 'status', 'admin_note'])]
class Item extends Model
{
    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(DonationRequest::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
