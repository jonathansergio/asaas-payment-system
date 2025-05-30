<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'value',
        'payment_method',
        'status',
        'invoice_url',
        'pix_qr_code',
        'pix_code',
    ];
}
