<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Untuk soft delete

class Invoice extends Model
{
    use HasFactory, SoftDeletes; // Gunakan SoftDeletes

    protected $fillable = [
        'supplier_id',
        'invoice_name',
        'invoice_number',
        'invoice_date',
        'received_date',
        'time_zone',
        'payment_method',
        'due_date',
        'has_ppn',
        'ppn_type',
        'subtotal_items',
        'discount',
        'shipping_cost',
        'ppn_amount',
        'total_amount',
        'is_paid',
        'public_code',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'received_date' => 'date',
        'due_date' => 'date',
        'has_ppn' => 'boolean',
        'is_paid' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoiceImages()
    {
        return $this->hasMany(InvoiceImage::class);
    }
}