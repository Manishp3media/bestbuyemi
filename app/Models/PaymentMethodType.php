<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethodType extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'key' => 'string',
        'name' => 'string',
        'status' => 'integer'
    ];
}
