<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commerce extends Model
{
    public function promotions()
    {
        return $this->hasMany('App\promotions');
    }
    
}
