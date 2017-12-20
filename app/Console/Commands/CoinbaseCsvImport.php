<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use App\Services\OrderService;


class CoinbaseCsvImport extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coinbase:csvimport {file : Csv bestand}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $filePath = $this->argument('file');
        
        $orderService = new OrderService();
        
        $reader = ReaderFactory::create(Type::CSV);
        $reader->open($filePath);
        $n = 1;
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                if ($n == 1) {
                    $n++;
                    continue;
                }
                $data = [];
                $data['trade'] = $row[1] > 0 ? "BUY" : "SELL";
                $data['wallet'] = $row[2];
                $data['amount'] = abs($row[1]);
                $data['coinprice'] = '0.0';
                $data['tradeprice'] = $row[3];
                $data['fee'] = $row[4];
                $data['orderhash'] = $row[5];
                $data['created_at'] = substr($row[0],0,19);
               
                $orderService->create($data);
                $n++;
            }
        }
        $reader->close();
    }
}
