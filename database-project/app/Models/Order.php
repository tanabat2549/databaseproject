<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['uid', 'shipping_address', 'delivery_date', 'total_amount', 'status'];
}
