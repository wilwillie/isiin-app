<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispute extends Model
{
    public const CATEGORIES = [
        'service_quality' => 'Kualitas layanan atau pemasangan',
        'furniture_damage' => 'Furniture rusak atau hilang',
        'schedule' => 'Jadwal atau pengambilan',
        'package_mismatch' => 'Paket tidak sesuai',
        'payment' => 'Pembayaran atau biaya',
        'other' => 'Lainnya',
    ];

    public const OUTCOMES = [
        'mutual_agreement' => 'Kesepakatan bersama',
        'customer_favor' => 'Keputusan mendukung customer',
        'vendor_favor' => 'Keputusan mendukung vendor',
        'no_violation' => 'Tidak ditemukan pelanggaran',
        'other' => 'Keputusan lainnya',
    ];

    protected $fillable = [
        'order_id', 'opened_by', 'category', 'description', 'evidence_photos',
        'status', 'resolution_outcome', 'resolution_note', 'resolved_by', 'resolved_at',
    ];

    protected $casts = [
        'evidence_photos' => 'array',
        'resolved_at' => 'datetime',
    ];

    public static function categoriesForOrder(Order $order): array
    {
        if (in_array($order->status, ['confirmed', 'paid'], true)) {
            return array_intersect_key(self::CATEGORIES, array_flip([
                'payment',
                'schedule',
                'package_mismatch',
            ]));
        }

        if (in_array($order->status, ['installed', 'completed'], true)) {
            return self::CATEGORIES;
        }

        return [];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by')->withTrashed();
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by')->withTrashed();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DisputeMessage::class)->with('sender')->oldest();
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getOutcomeLabelAttribute(): ?string
    {
        return $this->resolution_outcome
            ? (self::OUTCOMES[$this->resolution_outcome] ?? $this->resolution_outcome)
            : null;
    }
}
