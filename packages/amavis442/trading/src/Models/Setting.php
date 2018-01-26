<?php

namespace Amavis442\Trading\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Amavis442\Trading\Models\Setting
 *
 * @property int $id
 * @property float|null $spread
 * @property float|null $sellspread
 * @property float|null $stoploss
 * @property float $takeprofit
 * @property int $max_orders
 * @property float $bottom
 * @property float $top
 * @property string $size
 * @property int $lifetime
 * @property int $botactive
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereBotactive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereBottom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereLifetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereMaxOrders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereSellspread($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereSpread($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereStoploss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereTakeprofit($value)
 * @property float $takeprofittreshold
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting
 * whereTakeprofittreshold($value)
 * @property float $trailingstop
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Setting whereTrailingstop($value)
 */
class Setting extends Model
{
    protected $fillable = [
        'spread',
        'sellspread',
        'stoploss',
        'takeprofit',
        'max_orders',
        'bottom',
        'top',
        'size',
        'lieftime',
        'botactive'
    ];
}
