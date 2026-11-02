<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SalesOrderStatusHistory extends Model
{
    use HasUuids;

    protected $fillable = [
        'sales_order_id',
        'old_status',
        'new_status',
        'changed_by',
        'notes',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
