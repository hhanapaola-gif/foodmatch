<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanOrderDetail extends Model
{
    protected $fillable = [
        'plan_order_id',
        'meal_type',
        'day_of_week',
        'price',
    ];

    protected $casts = [
        'plan_order_id' => 'integer',
        'day_of_week'   => 'integer',
        'price'         => 'float',
    ];

    public function planOrder(): BelongsTo
    {
        return $this->belongsTo(PlanOrder::class, 'plan_order_id');
    }
}
