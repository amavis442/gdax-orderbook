<?php

namespace Amavis442\Trading\Bot;

use Amavis442\Trading\Contracts\Bot;
use Amavis442\Trading\Contracts\Exchange;
use Amavis442\Trading\Models\Ticker;

class TickerBot implements Bot
{
    protected $msg = [];
    protected $exchange;

    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;
    }

    protected function updateTicker($pair = 'BTC-EUR')
    {

        $product = (new \GDAX\Types\Request\Market\Product())->setProductId($pair);

        /** @var \GDAX\Types\Response\Market\ProductTicker $tickerData */
        $tickerData = $this->exchange->getClient()->getProductTicker($product);

        if ($tickerData instanceof \GDAX\Types\Response\Market\ProductTicker) {

            /** @var \DateTime $time */
            $time = $tickerData->getTime(); // UTC
            $d = \Carbon\Carbon::instance($time);

            $timeidTicker = (int)$d->setTimezone('Europe/Amsterdam')->format('YmdHis');
            $volume = (int)round($tickerData->getVolume());
            $last_price = (float)number_format($tickerData->getPrice(), 2, '.', '');


            $tick = Ticker::wherePair($pair)->whereTimeid($timeidTicker)->first();
            if ($tick) {
                $tick->open = $last_price;
                $tick->timeid = $timeidTicker;
                $tick->close = $last_price;
                $tick->low = $last_price;
                $tick->high = $last_price;
                $tick->volume = $volume;
            } else {
                $tick = new Ticker();
                $tick->pair = $pair;
                $tick->timeid = $timeidTicker;
                $tick->open = $last_price;
                $tick->close = $last_price;
                $tick->low = $last_price;
                $tick->high = $last_price;
                $tick->volume = $volume;
            }
            $tick->save();
        }
    }

    public function run()
    {
        $this->updateTicker();
    }
}
