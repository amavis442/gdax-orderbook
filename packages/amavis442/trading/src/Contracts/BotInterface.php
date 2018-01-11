<?php
/**
 * Created by PhpStorm.
 * User: patrickteunissen
 * Date: 09-01-18
 * Time: 10:41
 */

namespace Amavis442\Trading\Contracts;


interface BotInterface
{
    public function getMessage(): array;

    /**
     * @return array
     */
    public function run();
}