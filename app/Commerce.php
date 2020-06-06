<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commerce extends Model
{

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

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

    public function categories()
    {
        return $this->hasMany('App\Category');
    }
}
