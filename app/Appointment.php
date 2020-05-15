<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

// Modulo en desarrollo
class Appointment extends Model
{

    protected $fillable = [
        'user_id', 'commerce_id', 'service_id', 'schedule_day', 'schedule_hour', 'status',
    ];
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    public function commerce()
    {
        return $this->belongsTo('App\Commerce');
    }

}
