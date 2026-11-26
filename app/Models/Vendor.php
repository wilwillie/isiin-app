<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'business_name', 'description', 'city', 'is_verified'];

    protected $casts = ['is_verified' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function portfolioProjects(): HasMany
    {
        return $this->hasMany(PortfolioProject::class)
            ->orderByDesc('completion_year')
            ->orderByDesc('id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function completedOrderReviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Order::class, 'vendor_id', 'order_id')
            ->where('orders.status', 'completed');
    }
}
