<?php

namespace Amavis442\Trading\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Amavis442\Trading\Models\History
 *
 * @property int $id
 * @property string|null $pair
 * @property string|null $buckettime
 * @property float|null $low
 * @property float|null $high
 * @property float|null $open
 * @property float|null $close
 * @property float|null $volume
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\History whereBuckettime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\History whereClose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\History whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\History whereHigh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\History whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\History whereLow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\History whereOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\History wherePair($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\History whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Amavis442\Trading\Models\History whereVolume($value)
 * @mixin \Eloquent
 */
class History extends Model
{
    protected $fillable = ['pair','buckettime','low','high','open','close','volume'];
}
