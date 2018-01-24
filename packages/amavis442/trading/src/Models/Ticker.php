<?php

namespace Amavis442\Trading\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Amavis442\Trading\Models\Ticker
 *
 * @property int $id
 * @property string $product_id
 * @property int|null $timeid
 * @property float|null $open
 * @property float|null $high
 * @property float|null $low
 * @property float|null $close
 * @property float|null $volume
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Ticker whereClose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Ticker whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Ticker whereHigh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Ticker whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Ticker whereLow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Ticker whereOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Ticker whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Ticker whereTimeid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Ticker whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Ticker whereVolume($value)
 * @mixin \Eloquent
 * @property string $pair
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\Ticker wherePair($value)
 */
class Ticker extends Model
{

    protected $fillable = [
        'sequence',
        'pair',
        'timeid',
        'price',
        'open',
        'high',
        'low',
        'close',
        'volume',
        'volume_30d',
        'best_bid',
        'best_ask',
    ];


    public function getMinuteTicker($pair, $period = 168)
    {
        return $this->select('pair')->selectRaw('DATE_FORMAT(created_at,"%Y%m%d%H%i") as timeid, MAX(price) high, Min(price) low, MAX(`open`) `open`, Min(`close`) `close`, AVG(volume)  `volume`')
            ->wherePair($pair)
            ->groupBy('pair')
            ->groupBy(DB::raw('DATE_FORMAT(created_at,"%Y%m%d%H%i")'))
            ->orderBy('timeid', 'desc')
            ->limit($period)
            ->get();
    }
}
