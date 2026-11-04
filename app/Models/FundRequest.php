<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['receiver_id', 'title', 'description', 'target_amount', 'deadline', 'bank_name', 'account_name', 'account_number', 'photo_url', 'proof_doc_url', 'status', 'admin_note'])]
class FundRequest extends Model
{
    // Batas kebutuhan dana aktif (menunggu verifikasi atau tayang) per penerima
    public const MAX_ACTIVE = 2;

    public const BANKS = ['BSI', 'BRI', 'BNI', 'Mandiri', 'BCA', 'Bank Aceh Syariah', 'BTN', 'Dana', 'OVO', 'GoPay', 'ShopeePay', 'Lainnya'];

    protected function casts(): array
    {
        return ['deadline' => 'date', 'target_amount' => 'integer'];
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    // Tayang di katalog: sudah disetujui admin dan belum melewati batas waktu
    public function scopeOpen($query)
    {
        return $query->where('status', 'published')->whereDate('deadline', '>=', now()->toDateString());
    }

    public function collectedAmount(): int
    {
        return (int) $this->donations()->where('status', 'confirmed')->sum('amount');
    }

    public function percent(int $collected): int
    {
        return $this->target_amount > 0 ? min(100, (int) floor(($collected / $this->target_amount) * 100)) : 0;
    }

    public function daysLeft(): int
    {
        return max(0, (int) now()->startOfDay()->diffInDays($this->deadline->copy()->startOfDay(), false));
    }

    public static function rupiah($amount): string
    {
        return 'Rp ' . number_format((int) $amount, 0, ',', '.');
    }
}
