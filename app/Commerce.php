<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commerce extends Model
{
    public function promotions()
    {
        return $this->hasMany('App\Promotion');
    }

    public function services()
    {
        return $this->hasMany('App\Service');
    }

    public function appointments()
    {
        return $this->hasMany('App\Appointment');
    }
}
