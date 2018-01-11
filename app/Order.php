<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Order
 *
 * @property int $id
 * @property string $pair
 * @property int|null $parent_id
 * @property int|null $position_id
 * @property string $side
 * @property string $size
 * @property float $amount
 * @property string|null $status
 * @property string $order_id
 * @property string|null $strategy
 * @property float|null $take_profit
 * @property string|null $close_reason
 * @property float|null $fee
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Wallet $wallet
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereCloseReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order wherePair($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereSide($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereStrategy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereTakeProfit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
