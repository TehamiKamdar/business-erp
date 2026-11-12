<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SalesInvoiceStatusHistory extends Model
{
    use HasUuids;

    protected $fillable = [
        'sales_invoice_id',
        'old_status',
        'new_status',
        'changed_by',
        'notes',
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}