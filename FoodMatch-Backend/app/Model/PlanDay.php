<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanDay extends Model
{
    public $timestamps = false;

    protected $fillable = ['plan_id', 'day_of_week'];

    protected $casts = [
        'plan_id'     => 'integer',
        'day_of_week' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
