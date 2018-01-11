<?php

namespace Amavis442\Trading\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Amavis442\Trading\Models\Ticker
 *
 * @property int                 $id
 * @property string              $product_id
 * @property int|null            $timeid
 * @property float|null          $open
 * @property float|null          $high
 * @property float|null          $low
 * @property float|null          $close
 * @property float|null          $volume
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
 */
class Ticker extends Model
{

    protected $fillable = ['product_id', 'timeid', 'open', 'high', 'low', 'close', 'volume'];
}
