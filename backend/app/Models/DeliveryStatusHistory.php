<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DeliveryStatusHistory extends Model
{
    use HasUuids;

    protected $fillable = [
        'delivery_id',
        'old_status',
        'new_status',
        'changed_by',
        'notes',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
