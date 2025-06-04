<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

     protected $fillable = ['name', 'phone', 'company_name', 'email', 'otp_code', 'otp_expires_at'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}