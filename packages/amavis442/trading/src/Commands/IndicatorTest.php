<?php

namespace Amavis442\Trading\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Amavis442\Trading\Models\Ticker;
use Amavis442\Trading\Indicators\AverageDirectionalMovementIndexIndicator;

/**
 * Description of RunBotCommand
 *
 * @author patrick
 */
class IndicatorTest extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:indicator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buys and sells position depending on the given strategy.';

    public function handle()
    {
        $t = new Ticker();

        $config = new Collection(['data' => $t->getMinuteTicker('ETH-EUR',168),'period' => 168]);

        $i = new AverageDirectionalMovementIndexIndicator();
        echo $i->check($config);
    }
}
