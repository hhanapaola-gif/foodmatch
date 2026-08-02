<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class PlanCategory extends Model
{
    protected $fillable = ['name', 'image', 'is_active', 'priority'];

    protected $casts = [
        'is_active' => 'integer',
        'priority'  => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class, 'category_id');
    }

    public function getImageFullPathAttribute(): string
    {
        $placeholder = asset('assets/admin/img/160x160/img2.jpg');
        if ($this->image && Storage::disk('public')->exists('plan-category/' . $this->image)) {
            return asset('storage/plan-category/' . $this->image);
        }
        return $placeholder;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
