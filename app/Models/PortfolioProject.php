<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PortfolioProject extends Model
{
    protected $fillable = [
        'vendor_id', 'title', 'room_type', 'location', 'completion_year', 'description',
        'image_path', 'before_image_path', 'after_image_path',
    ];

    protected $casts = ['completion_year' => 'integer'];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function getBeforeImageUrlAttribute(): ?string
    {
        return $this->before_image_path ? Storage::disk('public')->url($this->before_image_path) : null;
    }

    public function getAfterImageUrlAttribute(): ?string
    {
        return $this->after_image_path ? Storage::disk('public')->url($this->after_image_path) : null;
    }
}
