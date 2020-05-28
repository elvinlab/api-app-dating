 <?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    public function services()
    {// Una Categoria va tener muchos servicios
        return $this->hasMany('App\Service');
    }
}
