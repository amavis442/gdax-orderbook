<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function wallet()
    {
        return $this->hasOne('App\Wallet');
    }
}
