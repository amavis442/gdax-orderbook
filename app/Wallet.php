<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Wallet
 *
 * @property-read \App\Wallet $fee
 * @property-read \App\Order $order
 * @mixin \Eloquent
 */
class Wallet extends Model
{
    public function fee(){
        return $this->hasOne('App\Wallet','parent_id','id');
    }
         
    public function order()
    {
        return $this->belongsTo('App\Order');
    }
}
