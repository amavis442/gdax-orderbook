<?php

namespace Amavis442\Trading\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Amavis442\Trading\Models\Ticker1m
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
class Ticker1m extends Model
{
    protected $table    = 'ticker_1m';
    protected $fillable = ['product_id', 'timeid', 'open', 'high', 'low', 'close', 'volume'];


    /**
     * Transform data for the trader functions
     * vb array trader_cdl2crows ( array $open , array $high , array $low , array $close )
     *
     * @param \Illuminate\Support\Collection $datas
     *
     * @return array
     */
    public function transformPairData(Collection $datas): Collection
    {
        $retCollection = new Collection();

        $ret['date']   = [];
        $ret['low']    = [];
        $ret['high']   = [];
        $ret['open']   = [];
        $ret['close']  = [];
        $ret['volume'] = [];

        $ret = [];

        foreach ($datas as $data) {
            $ret['date'][]   = $data->buckettime;
            $ret['low'][]    = $data->low;
            $ret['high'][]   = $data->high;
            $ret['open'][]   = $data->open;
            $ret['close'][]  = $data->close;
            $ret['volume'][] = $data->volume;
        }

        foreach ($ret as $key => $rettemmp) {
            $ret[$key] = array_reverse($rettemmp);
        }

        $retCollection->put('date',$ret['date']);
        $retCollection->put('low',$ret['low']);
        $retCollection->put('high',$ret['high']);
        $retCollection->put('open',$ret['open']);
        $retCollection->put('close',$ret['close']);
        $retCollection->put('volume',$ret['volume']);



        return $retCollection;
    }

    /**
     * @param string $product_id
     * @param int    $limit
     * @param bool   $day_data
     * @param int    $hour
     * @param string $periodSize
     * @param bool   $returnResultSet
     *
     * @return array|\Illuminate\Support\Collection|null
     */
    public function getRecentData(string $product_id = 'BTC-EUR', int $limit = 168, bool $day_data = false, int $hour = 12, bool $returnResultSet = false)
    {
        /**
         *  we need to cache this as many strategies will be
         *  doing identical pulls for signals.
         */
        $key   = 'recent.' . $product_id . '.' . $limit . ".$day_data.$hour.1m";
        $value = Cache::get($key);


        if ($value) {
            return $value;
        }

        $rows = Ticker1m::selectRaw('*, unix_timestamp(created_at) as buckettime')
                        ->where('product_id', $product_id)
                        ->orderby('timeid', 'DESC')
                        ->limit($limit)
                        ->get();

        $starttime    = null;
        $validperiods = 0;
        $oldrow       = null;
        foreach ($rows as $row) {
            $endtime = $row->buckettime;
            if (!is_null($starttime)) {
                /** Check for missing periods * */
                $variance = (int)119;

                $periodcheck = $starttime - $endtime;

                if ((int)$periodcheck > (int)$variance) {
                    echo "** YOU HAVE " . $validperiods . " PERIODS OF VALID PRICE DATA OUT OF ' . $limit . '. Please ensure price sync is running and wait for additional data to be logged before trying again. Additionally you could use a smaller time period if available.\n";
                }
                $validperiods++;
            }
            $starttime = $endtime;
        }

        if ($returnResultSet) {
            $ret = $rows;
        } else {
            $ret = $this->transformPairData($rows);
        }

        Cache::put($key, $ret, 60);

        return $ret;
    }

}
