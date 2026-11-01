<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSwipe extends Model
{
    protected $fillable = ['uid', 'product_id', 'liked'];

    protected function casts(): array
    {
        return ['liked' => 'boolean'];
    }
}