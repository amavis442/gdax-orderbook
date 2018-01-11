<?php

namespace Amavis442\Trading\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Amavis442\Trading\Models\Order
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
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order whereCloseReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order wherePair($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order whereSide($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order whereStrategy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order whereTakeProfit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    protected $fillable = ['pair', 'parent_id','position_id','side', 'size', 'amount', 'status', 'order_id', 'strategy','take_profit','close_reason','fee'];
}
