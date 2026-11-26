<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    public const STATUS_LABELS = [
        'pending'   => 'Menunggu konfirmasi vendor',
        'needs_info' => 'Menunggu informasi dari pemilik properti',
        'confirmed' => 'Dikonfirmasi, menunggu pembayaran',
        'paid'      => 'Dibayar, menunggu pemasangan',
        'installed' => 'Terpasang (masa sewa)',
        'pickup_pending' => 'Menunggu pengambilan',
        'completed' => 'Selesai',
        'rejected'  => 'Ditolak vendor',
        'cancelled' => 'Dibatalkan',
    ];

    /** Status pesanan yang masih berjalan (menghalangi penghapusan akun). */
    public const ACTIVE_STATUSES = ['pending', 'needs_info', 'confirmed', 'paid', 'installed', 'pickup_pending'];

    protected $fillable = [
        'code', 'customer_id', 'package_id', 'vendor_id', 'start_date', 'end_date',
        'property_address', 'notes', 'room_length', 'room_width', 'ceiling_height',
        'room_photos', 'vendor_request_note', 'customer_response',
        'rental_fee', 'delivery_fee', 'installation_fee',
        'total', 'status', 'paid_at', 'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'paid_at'    => 'datetime',
        'completed_at' => 'datetime',
        'room_photos' => 'array',
    ];

    /** URL memakai kode pesanan (ISN-...) bukan id. */
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public static function generateCode(): string
    {
        do {
            $code = 'ISN-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    // withTrashed: riwayat pesanan tetap utuh walaupun akun sudah dihapus.
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id')->withTrashed();
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class)->withTrashed();
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class)->latest();
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(OrderPayment::class)->latestOfMany();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'pending', 'needs_info' => 'bg-amber-100 text-amber-800',
            'confirmed' => 'bg-sky-100 text-sky-800',
            'paid'      => 'bg-indigo-100 text-indigo-800',
            'installed' => 'bg-emerald-100 text-emerald-800',
            'pickup_pending' => 'bg-amber-100 text-amber-800',
            'completed' => 'bg-emerald-200 text-emerald-900',
            default     => 'bg-rose-100 text-rose-800',
        };
    }

    /** Komisi Isiin dari sewa, pengiriman, dan pemasangan. */
    public function getCommissionAttribute(): int
    {
        $serviceTotal = $this->rental_fee + $this->delivery_fee + $this->installation_fee;

        return (int) round($serviceTotal * config('isiin.commission_rate'));
    }

    /** Pendapatan vendor = sewa + ongkir + pemasangan - komisi. */
    public function getVendorPayoutAttribute(): int
    {
        return $this->rental_fee + $this->delivery_fee + $this->installation_fee - $this->commission;
    }

    /** Langkah timeline untuk halaman detail pesanan. */
    public function timeline(): array
    {
        $rank = [
            'pending' => 0,
            'needs_info' => 0,
            'confirmed' => 1,
            'paid' => 2,
            'installed' => 3,
            'pickup_pending' => 4,
            'completed' => 5,
        ];
        $current = $rank[$this->status] ?? -1;

        $steps = [
            0 => 'Pesanan dibuat',
            1 => 'Konfirmasi vendor',
            2 => 'Pembayaran',
            3 => 'Pemasangan',
            4 => 'Pengambilan furniture',
            5 => 'Selesai',
        ];

        $result = [];
        foreach ($steps as $index => $label) {
            $result[] = ['label' => $label, 'done' => $current >= $index];
        }

        return $result;
    }
}
