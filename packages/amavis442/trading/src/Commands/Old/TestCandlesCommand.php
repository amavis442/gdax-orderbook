<?php

namespace Amavis442\Trading\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Amavis442\Trading\Traits\CandleMap;
use Amavis442\Trading\Traits\Pivots;
use Amavis442\Trading\Traits\OHLC;
use Amavis442\Trading\Util\Candles;
use Amavis442\Trading\Util\Console;
use Amavis442\Trading\Util\Indicators;
use Amavis442\Trading\Util\Cache;

/**
 * Description of TestCandlesCommand
 *
 * @author patrick
 */
class TestCandlesCommand extends Command {

    use OHLC,
        Pivots,
        CandleMap;

    protected $candles;
    protected $indicators;

    public function __construct(string $name = null) {
        parent::__construct($name);
        $this->candles = new Candles();
    }


    protected function configure() {
        $this->setName('test:candles')

                // the short description shown while running "php bin/console list"
                ->setDescription('Test the candles.')
                ->setHelp('Test the candles.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->indicators = $ind = new Indicators();
        $instrument = 'BTC-EUR';
        $data       = $this->getRecentData($instrument, 70);

        $all                  = [];
        while (1) {
            $candles              = [];
            $data                 = $this->getRecentData($instrument, 200);
            $cand                 = $this->candles->allCandles($data);
            $candles[$instrument] = $cand['current'] ?? [];

            dump($candles);
            sleep(5);
        }
    }

}
