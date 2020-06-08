<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'commerce_id', 'category_id', 'name', 'description', 'price',
    ];

    public function category()
    {// Muchos Servicios estaran en una categoria
        return $this->belongsTo('App\Category');//Category es foranea en services
    }

    public function commerce()
    {//Muchos Servicios estaran en un comercio
        return $this->belongsTo('App/Commerce');
    }

    public function appointments()
    {
        return $this->hasMany('App\Appointment');
    }
}
