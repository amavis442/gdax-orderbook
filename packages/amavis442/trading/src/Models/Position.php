<?php

namespace Amavis442\Trading\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Amavis442\Trading\Models\Position
 *
 * @property int $id
 * @property string $pair
 * @property string $order_id
 * @property string $size
 * @property float $amount
 * @property float $open
 * @property float $close
 * @property string $position
 * @property string|null $close_reason
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position whereClose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position whereCloseReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position whereOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position wherePair($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property float|null $sellfor
 * @property float|null $trailingstop
 * @property string $status
 * @property int $watch
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position whereSellfor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position whereTrailingstop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Position whereWatch($value)
 */
class Position extends Model
{
    protected $fillable = ['pair', 'order_id', 'size', 'amount', 'open', 'close', 'position'];
}
