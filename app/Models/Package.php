<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'vendor_id', 'name', 'description', 'room_type', 'style', 'price', 'duration_days',
        'delivery_fee', 'installation_fee', 'deposit', 'image_path', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackageItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** Paket yang tampil di katalog publik: aktif dan milik vendor terverifikasi. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereHas('vendor', fn (Builder $q) => $q->where('is_verified', true));
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function getRoomLabelAttribute(): string
    {
        return config('isiin.room_types.' . $this->room_type, $this->room_type);
    }
}
