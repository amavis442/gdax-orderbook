<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $cast = [
        'profit' => 'decimal'
    ];
    
    public function wallet($wallet = 'EUR')
    {
        $q = $this->hasOne('App\Wallet');
        if ($wallet != 'all') {
            $q->where('wallet', $wallet);
        }
        
        return $q;
    }
    

}
