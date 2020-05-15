<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    public function service(){//Un servicio puede tener muchas ventas
        return $this->belongsTo('App/Service');// Se ocupa identificar el servicio al cual se le hizo la venta
    }
}
