<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'company_name',
        'address',
        'payment_details', // DITAMBAHKAN
        'email',
    ];
    

    // Cast ini secara otomatis mengubah JSON dari DB menjadi array, dan sebaliknya.
    protected $casts = [
        'payment_details' => 'array', // DITAMBAHKAN
    ];
}
