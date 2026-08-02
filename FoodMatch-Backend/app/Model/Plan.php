<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use App\Model\Branch;

class Plan extends Model
{
    protected $fillable = [
        'restaurant_id',
        'category_id',
        'title',
        'description',
        'type',
        'min_days',
        'meals_per_day',
        'breakfast_price',
        'lunch_price',
        'dinner_price',
        'image',
        'menu_pdf',
        'is_active',
    ];

    protected $casts = [
        'restaurant_id'   => 'integer',
        'category_id'     => 'integer',
        'min_days'        => 'integer',
        'meals_per_day'   => 'integer',
        'breakfast_price' => 'float',
        'lunch_price'     => 'float',
        'dinner_price'    => 'float',
        'is_active'       => 'integer',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'restaurant_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PlanCategory::class, 'category_id');
    }

    public function days(): HasMany
    {
        return $this->hasMany(PlanDay::class, 'plan_id');
    }

    public function savedByUsers(): HasMany
    {
        return $this->hasMany(SavedPlan::class, 'plan_id');
    }

    public function getImageFullPathAttribute(): string
    {
        $image = $this->image ?? null;
        $placeholder = asset('assets/admin/img/160x160/img2.jpg');

        if (!is_null($image) && Storage::disk('public')->exists('plan/' . $image)) {
            return asset('storage/plan/' . $image);
        }
        return $placeholder;
    }

    public function scopeActive($query)
    {
        // A plan can't be "active" if the restaurant that offers it was
        // deactivated — otherwise it keeps showing up (and stays orderable)
        // in the app after the branch is removed from the Admin panel.
        return $query->where('is_active', 1)
            ->whereHas('branch', fn ($q) => $q->where('status', 1));
    }
}
