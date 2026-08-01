<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeliveryMan extends Authenticatable
{
    use Notifiable;

    /**
     * Attributes cast to native types / transparently encrypted.
     *
     * `identity_number` holds the delivery man's national ID / cedula,
     * which is PII. Laravel's `encrypted` cast transparently encrypts the
     * value with APP_KEY (AES-256-CBC via the Crypt facade) on write and
     * decrypts it on read, so every existing Eloquent read/write path
     * (DeliveryManController, DeliveryManLoginController, exports, etc.)
     * keeps working unchanged while the column is encrypted at rest.
     */
    protected $casts = [
        'identity_number' => 'encrypted',
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(DMReview::class, 'delivery_man_id');
    }

    public function rating(): HasMany
    {
        return $this->hasMany(DMReview::class)
            ->select(DB::raw('avg(rating) average, delivery_man_id'))
            ->groupBy('delivery_man_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'delivery_man_id');
    }

    public function getImageFullPathAttribute(): string
    {
        $image = $this->image ?? null;
        $path = asset('public/assets/admin/img/160x160/img1.jpg');

        if (!is_null($image) && Storage::disk('public')->exists('delivery-man/' . $image)) {
            $path = asset('storage/delivery-man/' . $image);
        }
        return $path;
    }

    public function getIdentityImageFullPathAttribute()
    {
        $value = $this->identity_image ?? [];
        $imageUrlArray = is_array($value) ? $value : json_decode($value, true);
        if (is_array($imageUrlArray)) {
            foreach ($imageUrlArray as $key => $item) {
                if (Storage::disk('public')->exists('delivery-man/' . $item)) {
                    $imageUrlArray[$key] = asset('storage/delivery-man/' . $item);
                } else {
                    $imageUrlArray[$key] = asset('public/assets/admin/img/160x160/img1.jpg');
                }
            }
        }
        return $imageUrlArray;
    }
}
