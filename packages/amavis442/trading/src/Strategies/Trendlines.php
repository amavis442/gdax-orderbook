<?php

declare(strict_types=1);

namespace Amavis442\Trading\Strategies;

use Amavis442\Trading\Contracts\Indicator;

/**
 * Class TrendingLinesStrategy
 *
 *
 * @see     https://www.quantopian.com/posts/trading-on-multiple-ta-lib-signals
 * @see     https://www.quantopian.com/posts/stocks-on-the-move-by-andreas-clenow
 *
 * @see     http://stockcharts.com/school/doku.php?id=chart_school:technical_indicators:money_flow_index_mfi
 * @see     https://tradingsim.com/blog/chande-momentum-oscillator-cmo-technical-indicator/
 * @see     http://stockcharts.com/school/doku.php?id=chart_school:technical_indicators:commodity_channel_index_cci
 *
 * @package Amavis442\Trading\Strategies
 *
 */
class Trendlines
{
    public function __invoke(array $data, $indicators): int
    {

        /**
         * Commodity channel index (cci)
         * The Commodity Channel Index (CCI) is a versatile indicator that can be used to identify a new trend
         * or warn of extreme conditions.
         */
        $cci = $indicators->cci($data);

        /**
         * Chande momentum oscillator (cmo)
         * The chande momentum oscillator (CMO) was developed by Tushar Chande and is a technical indicator that
         *
         * attempts to capture the momentum of a security.
         */
        $cmo = $indicators->cmo($data);

        /**
         * Money flow index (mfi)
         * The Money Flow Index (MFI) is an oscillator that uses both price and volume to measure buying
         * and selling pressure.
         */
        $mfi = $indicators->mfi($data);

        //Trends
        /**
         * Hilbert Transform - Trend vs Cycle Mode Simply tell us if the market is
         * either trending or cycling, with an additional parameter the method returns
         * the number of days we have been in a trend or a cycle.
         */
        $httc = $indicators->httc($data);

        /**
         * Hilbert Transform - Instantaneous Trendline — smoothed trendline, if the
         * price moves 1.5% away from the trendline we can declare a trend.
         */
        $htl = $indicators->htl($data);

        /**
         * Hilbert Transform - Sinewave (MESA indicator)— We are actually using DSP
         * on the prices to attempt to get a lag-free/low-lag indicator.
         * This indicator can be passed an extra parameter and it will tell you in
         * we are in a trend or not. (when used as an indicator do not use in a trending market)
         */
        $hts = $indicators->hts($data);

        /**
         * Market Meanness Index (link) — This indicator is not a measure of how
         * grumpy the market is, it shows if we are currently in or out of a trend
         * based on price reverting to the mean.
         */
        $mmi = $indicators->mmi($data);


        /** instrument is overbought, we will short */
        if ($cci == -1 && $cmo == -1 && $mfi == -1) {
            $this->msg[] = "..Overbought going Short (sell)";
        }

        /** It is underbought, we will go LONG */
        if ($cci == 1 && $cmo == 1 && $mfi == 1) {
            $this->msg[] = "..Underbought going LONG (buy)";
        }

        $adx = $indicators->adx($data);
        $_sma6 = trader_sma($data['close'], 6);
        $sma6 = array_pop($_sma6);
        $prior_sma6 = array_pop($_sma6);
        $_sma40 = trader_sma($data['close'], 40);
        $sma40 = array_pop($_sma40);
        $prior_sma40 = array_pop($_sma40);

        /** have the lines crossed? */
        $down_cross = (($prior_sma6 <= $sma40 && $sma6 > $sma40) ? 1 : 0);
        $up_cross = (($prior_sma40 <= $sma6 && $sma40 > $sma6) ? 1 : 0);

        if ($adx == 1 && $down_cross) {
            $this->msg[] = "..adx down_cross -> buy";
        }

        if ($adx == 1 && $up_cross) {
            $this->msg[] = "..adx up_cross -> sell";
        }

        // Check what On Balance Volume (OBV) does
        $obv = $indicators->obv($data);
        if ($obv == 1) {
            $this->msg[] = "..On Balance Volume (OBV): Upwards (buy)\n";
        }

        if ($obv == 0) {
            $this->msg[] = "..On Balance Volume (OBV): Hold\n";
        }

        if ($obv == -1) {
            $this->msg[] = "..On Balance Volume (OBV): Downwards (sell)\n";
        }

        if (($httc * $htl * $mmi) == 1) {
            $this->msg[] = "Buy";
        }
        if (($httc * $htl * $mmi) == -1) {
            $this->msg[] = "Sell";
        }

        $result = Indicator::HOLD;
        if ($httc == 1 && $htl == 1 && $mmi == 1 && $obv == 1) {
            $result = Indicator::BUY;
        }

        if ($httc == 1 && $htl == -1 && $mmi == 1 && $obv == -1) {
            $result = Indicator::SELL;
        }

        return $result;
    }
}
