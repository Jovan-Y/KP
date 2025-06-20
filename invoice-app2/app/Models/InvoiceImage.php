<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'filename',
        'filepath',
        'title',
        'type',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}