<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_id',
        'invoice_number',
        'po_number',
        'invoice_date',
        'received_date',
        'due_date',
        'payment_type',
        'subtotal_items',
        'discount_value',
        'discount_type',
        'ppn_percentage',
        'ppn_amount',
        'other_taxes',
        'total_amount',
        'is_paid',
        'public_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invoice_date' => 'date',
        'received_date' => 'date',
        'due_date' => 'date',
        'is_paid' => 'boolean',
        'other_taxes' => 'array',
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

    public function referenceImages()
    {
        return $this->hasMany(InvoiceImage::class)->where('type', 'reference');
    }

    public function paymentProofImages()
    {
        return $this->hasMany(InvoiceImage::class)->where('type', 'payment_proof');
    }
}
