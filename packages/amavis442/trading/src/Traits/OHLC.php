<?php

namespace Amavis442\Trading\Traits;
use Illuminate\Support\Facades\Cache;

class OHLC
{
    /**
     * Transform data for the trader functions
     * vb array trader_cdl2crows ( array $open , array $high , array $low , array $close )
     *
     * @param \Illuminate\Support\Collection $datas
     *
     * @return array
     */
    public function transformPairData(\Illuminate\Support\Collection $datas): array
    {
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

        return $ret;
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
    public function getRecentData(string $product_id = 'BTC-EUR', int $limit = 168, bool $day_data = false, int $hour = 12, string $periodSize = '1m', bool $returnResultSet = false)
    {
        /**
         *  we need to cache this as many strategies will be
         *  doing identical pulls for signals.
         */
        $key   = 'recent.' . $product_id . '.' . $limit . ".$day_data.$hour.$periodSize";
        $value = Cache::get($key);


        if ($value) {
            return $value;
        }

        $rows = Ticker1m::selectRaw('*, unix_timestamp(ctime) as buckettime')
                ->where('product_id', $product_id)
                ->orderby('timeid', 'DESC')
                ->limit($limit)
                ->get();

        $starttime    = null;
        $validperiods = 0;
        $oldrow       = null;
        foreach ($rows as $row) {

            $endtime = $row->buckettime;

            if ($starttime == null) {
                $starttime = $endtime;
            } else {
                /** Check for missing periods * */
                if ($periodSize == '1m') {
                    $variance = (int) 119;
                } else {
                    if ($periodSize == '5m') {
                        $variance = (int) 375;
                    } else {
                        if ($periodSize == '15m') {
                            $variance = (int) 1125;
                        } else {
                            if ($periodSize == '30m') {
                                $variance = (int) 2250;
                            } else {
                                if ($periodSize == '1h') {
                                    $variance = (int) 4500;
                                } else {
                                    if ($periodSize == '1d') {
                                        $variance = (int) 108000;
                                    }
                                }
                            }
                        }
                    }
                }

                $periodcheck = $starttime - $endtime;

                if ((int) $periodcheck > (int) $variance) {
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
