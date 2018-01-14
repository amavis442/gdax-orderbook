<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amavis442\Trading\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Amavis442\Trading\Bot\Gdaxbot;
use Symfony\Component\Console\Helper\Table;

/**
 * Description of ReportCommand
 *
 * @author patrick
 */
class ReportCommand extends Command {
    
    protected function configure() {
        $this->setName('report')

                // the short description shown while running "php bin/console list"
                ->setDescription('Report of the wallet.')
                ->setHelp('Gets the balance, current price and value of the account')
        ;
    }
    
     protected function execute(InputInterface $input, OutputInterface $output) {
         $exchange = new \Amavis442\Trading\Exchanges\GDaxExchange();
        $exchange->setCoin(getenv('CRYPTOCOIN'));
        $exchange->connect();
        
    
        $data = $exchange->getAccountReport(getenv('CRYPTOCOIN'));
          
        $rows[] = [getenv('CRYPTOCOIN'), $data['balance'], $data['koers'], $data['waarde']];
        
        
        $table = new Table($output);
        $table
            ->setHeaders(['Name', 'Balance', 'Current price','Value'])
            ->setRows($rows);
        $table->render();

     }
}
