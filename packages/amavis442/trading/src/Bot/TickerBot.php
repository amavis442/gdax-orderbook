<?php
/**
 * Created by PhpStorm.
 * User: patrickteunissen
 * Date: 09-01-18
 * Time: 10:41
 */

namespace Amavis442\Trading\Bot;

use Amavis442\Trading\Contracts\BotInterface;
use Amavis442\Trading\Contracts\GdaxServiceInterface;
use Amavis442\Trading\Models\Ticker;
use Amavis442\Trading\Models\Ticker1m;

class TickerBot implements BotInterface
{
    protected $msg = [];
    protected $gdax;


    public function __construct(GdaxServiceInterface $gdax)
    {
        $this->gdax = $gdax;
    }


    public function getMessage(): array
    {
        return $this->msg;
    }

    protected function updateTicker($pair = 'BTC-EUR')
    {

        $product = (new \GDAX\Types\Request\Market\Product())->setProductId($pair);

        /** @var \GDAX\Types\Response\Market\ProductTicker $tickerData */
        $tickerData = $this->gdax->getClient()->getProductTicker($product);

        if ($tickerData instanceof \GDAX\Types\Response\Market\ProductTicker) {

            /** @var \DateTime $time */
            $time = $tickerData->getTime(); // UTC
            $d    = \Carbon\Carbon::instance($time);

            $timeidTicker = (int)$d->setTimezone('Europe/Amsterdam')->format('YmdHis');
            $volume       = (int)round($tickerData->getVolume());
            $last_price   = (float)number_format($tickerData->getPrice(), 2, '.', '');


            $tick = Ticker::wherePair($pair)->whereTimeid($timeidTicker)->first();
            if ($tick) {
                $tick->open   = $last_price;
                $tick->timeid = $timeidTicker;
                $tick->close  = $last_price;
                $tick->low    = $last_price;
                $tick->high   = $last_price;
                $tick->volume = $volume;
            } else {
                $tick         = new Ticker();
                $tick->pair   = $pair;
                $tick->timeid = $timeidTicker;
                $tick->open   = $last_price;
                $tick->close  = $last_price;
                $tick->low    = $last_price;
                $tick->high   = $last_price;
                $tick->volume = $volume;
            }
            $tick->save();


            $timeid = (int)$d->setTimezone('Europe/Amsterdam')->format('YmdHi');

            $this->update1MinuteTicker($pair, $timeid);
        }
    }


    /**
     * @param string $product_id
     * @param int    $timeid
     * @param int    $volume
     */
    public function update1MinuteTicker(string $pair, int $timeid)
    {
        $open   = null;
        $close  = null;
        $high   = null;
        $low    = null;
        $volume = null;

        /*
        select MAX(high),MIN(low), AVG(volume) from tickers where timeid >= 20180112152700 AND timeid <= 20180112152759;
        select timeid,`open` from tickers where timeid >= 20180112152700 AND timeid <= 20180112152759 order by timeid asc limit 1;
        select timeid,`close` from tickers where timeid >= 20180112152700 AND timeid <= 20180112152759 order by timeid desc limit 1;
        */


        $lastRecordedTimeId = Ticker1m::selectRaw('MAX(timeid) AS timeid')->where('pair', $pair)->first();

        if ($lastRecordedTimeId && $lastRecordedTimeId->timeid) {
            $lasttimeidRecorded = (int)\Carbon\Carbon::createFromFormat('YmdHi', $lastRecordedTimeId->timeid)->format('YmdHi');
        } else {
            $lasttimeidRecorded = 0;
        }

        if ($lasttimeidRecorded <= $timeid) {

            /* Get High and Low from ticker data for insertion */
            $starttimeid = (int)(\Carbon\Carbon::now()->subMinute(1)->setTimezone('Europe/Amsterdam')->format('YmdHi') . '00');
            $lasttimeid  = (int)$starttimeid + 59;


            $accumHighLowVolume = Ticker::selectRaw('MAX(high) as high, MIN(low) as low, AVG(volume) as volume')
                                        ->where('pair', $pair)
                                        ->where('timeid', '>=', $starttimeid)
                                        ->where('timeid', '<=', $lasttimeid)
                                        ->first();


            $high   = (float)$accumHighLowVolume->high;
            $low    = (float)$accumHighLowVolume->low;
            $volume = (int)round($accumHighLowVolume->volume);

            /* Get Open price from ticker data and last minute */
            $accumOpen = Ticker::select('open')
                               ->where('pair', $pair)
                               ->where('timeid', '>=', $starttimeid)
                               ->where('timeid', '<=', $lasttimeid)
                               ->orderBy('timeid', 'asc')
                               ->limit(1)
                               ->first();

            if ($accumOpen) {
                $open = (float)$accumOpen->open;
            }

            /* Get close price from ticker data and last minute */
            $accumClose = Ticker::select('close')
                                ->where('pair', $pair)
                                ->where('timeid', '>=', $starttimeid)
                                ->where('timeid', '<=', $lasttimeid)
                                ->orderBy('created_at', 'desc')
                                ->orderBy('timeid', 'desc')
                                ->limit(1)
                                ->first();

            if ($accumClose) {
                $close = (float)$accumClose->close;
            }

            dump($pair . ' ' . $timeid . ' ' . $starttimeid . ' to ' . $lasttimeid . ' open ' . $open . ' close ' . $close . ' high ' . $high . ' low ' . $low);


            if ($open && $close && $high && $low) {
                $tick = Ticker1m::wherePair($pair)->whereTimeid($timeid)->first();
                if ($tick) {
                    $tick->pair   = $pair;
                    $tick->timeid = $timeid;
                    $tick->open   = $open;
                    $tick->close  = $close;
                    $tick->low    = $low;
                    $tick->high   = $high;
                    $tick->volume = $volume;
                } else {
                    $tick         = new Ticker1m();
                    $tick->pair   = $pair;
                    $tick->timeid = $timeid;
                    $tick->open   = $open;
                    $tick->close  = $close;
                    $tick->low    = $low;
                    $tick->high   = $high;
                    $tick->volume = $volume;

                }
                $tick->save();
            }
        }
    }


    public function run(): array
    {
        $this->updateTicker();

        return [];
    }
}