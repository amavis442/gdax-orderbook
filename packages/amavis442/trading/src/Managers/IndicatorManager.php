<?php
/**
 * Created by PhpStorm.
 * User: patrickteunissen
 * Date: 09-01-18
 * Time: 17:26
 */

namespace Amavis442\Trading\Managers;

use Amavis442\Trading\Contracts\IndicatorInterface;
use Amavis442\Trading\Contracts\IndicatorManagerInterface;


class IndicatorManager implements IndicatorManagerInterface
{
    /**
     * The indicators collection.
     *
     * @var array
     */
    protected $indicators = [];

    /**
     * Add an indicator resolver.
     *
     * @param string  $indicator
     * @param IndicatorInterface $resolver
     */
    public function add(string $indicator, IndicatorInterface $resolver)
    {
        $this->indicators[$indicator] = $resolver;
    }

    /**
     * Dynamically handle the indicator calls.
     *
     * @param string $indicator
     * @param array  $parameters
     *
     * @return int
     */
    public function __call(string $indicator, array $parameters): int
    {
        if (!isset($this->indicators[$indicator])) {
            throw new \BadMethodCallException("Indicator [{$indicator}] does not exist");
        }

        return call_user_func($this->indicators[$indicator],$parameters[0]);
    }
}
