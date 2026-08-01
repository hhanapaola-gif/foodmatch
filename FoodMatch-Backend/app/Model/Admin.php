<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['admin_role_id'];

    /**
     * `identity_number` holds an employee/admin's national ID, which is PII.
     * Same `encrypted` cast pattern as `DeliveryMan::$casts` — see that
     * model's docblock for how the transparent encrypt/decrypt works.
     */
    protected $casts = [
        'identity_number' => 'encrypted',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class, 'admin_role_id');
    }

    public function getImageFullPathAttribute(): string
    {
        $image = $this->image ?? null;
        $path = asset('public/assets/admin/img/400x400/img2.jpg');

        if (!is_null($image) && Storage::disk('public')->exists('admin/' . $image)) {
            $path = asset('storage/admin/' . $image);
        }
        return $path;
    }

    public function getIdentityImageFullPathAttribute()
    {
        $value = $this->identity_image ?? [];
        $imageUrlArray = is_array($value) ? $value : json_decode($value, true);
        if (is_array($imageUrlArray)) {
            foreach ($imageUrlArray as $key => $item) {
                if (Storage::disk('public')->exists('admin/' . $item)) {
                    $imageUrlArray[$key] = asset('storage/admin/' . $item);
                } else {
                    $imageUrlArray[$key] = asset('public/assets/admin/img/400x400/img2.jpg');
                }
            }
        }
        return $imageUrlArray;
    }
}
