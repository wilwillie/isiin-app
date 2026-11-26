<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileChangeRequest extends Model
{
    public const TYPE_LABELS = [
        'profile'  => 'Ubah profil',
        'password' => 'Ganti password',
        'delete'   => 'Hapus akun',
    ];

    public const STATUS_LABELS = [
        'pending'   => 'Menunggu admin',
        'approved'  => 'Disetujui',
        'rejected'  => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ];

    public const FIELD_LABELS = [
        'name'          => 'Nama lengkap',
        'business_name' => 'Nama usaha',
        'city'          => 'Kota',
        'email'         => 'Email',
        'phone'         => 'No. telepon',
    ];

    protected $fillable = ['user_id', 'type', 'status', 'payload', 'admin_note', 'reviewed_by', 'reviewed_at'];

    protected $casts = [
        'payload'     => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withTrashed();
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'bg-amber-100 text-amber-800',
            'approved' => 'bg-emerald-100 text-emerald-800',
            'rejected' => 'bg-rose-100 text-rose-800',
            default    => 'bg-ink/10 text-ink/60',
        };
    }

    /** Nilai saat ini milik pemohon, untuk dibandingkan dengan nilai yang diminta. */
    public function currentValues(): array
    {
        $user = $this->user;
        $vendor = $user?->vendor;

        return [
            'name'          => $user?->name,
            'business_name' => $vendor?->business_name,
            'city'          => $vendor?->city,
            'email'         => $user?->email,
            'phone'         => $user?->phone,
        ];
    }
}
