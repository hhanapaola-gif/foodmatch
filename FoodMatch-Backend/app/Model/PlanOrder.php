<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanOrder extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'start_date',
        'status',
        'payment_status',
        'payment_method',
        'total_price',
        'selected_days',
        'address',
        'notes',
    ];

    protected $casts = [
        'user_id'       => 'integer',
        'plan_id'       => 'integer',
        'start_date'    => 'date',
        'total_price'   => 'float',
        'selected_days' => 'array',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PlanOrderDetail::class, 'plan_order_id');
    }
}
