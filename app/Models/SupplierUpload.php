<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'filename',
        'filepath',
        'title',
        'supplier_reference_code',
        'is_linked',
        'invoice_id',
     ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}