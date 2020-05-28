<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{

    protected $fillable = [
        'commerce_id', 'coupon', 'max', 'amount', 'expiry', 'description', 'image', 'discount',
    ];

    protected $table = 'promotions';

    public function commerce()
    {
        return $this->belongsTo('App\Commerce');
    }
}
