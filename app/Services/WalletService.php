<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Services;

/**
 * Description of WalletService
 *
 * @author patrick
 */
class WalletService {
    public function getWallets() {
        $client = new \GDAX\Clients\AuthenticatedClient(
                config('coinbase.api_key'), config('coinbase.api_secret'), config('coinbase.password')
        );


        $accounts = $client->getAccounts();

        $portfolio = 0;
        /** @var  \GDAX\Types\Response\Authenticated\Account $account */
        foreach ($accounts as $account) {
            $currency = $account->getCurrency();
            $balance = $account->getBalance();

            if ($currency != 'EUR') {
                $product = (new \GDAX\Types\Request\Market\Product())->setProductId($currency . '-EUR');
                $productTicker = $client->getProductTicker($product);
                $koers = number_format($productTicker->getPrice(), 3, '.', '');
            } else {
                $koers = 0.0;
            }
            $waarde = 0.0;
            if ($currency == 'EUR') {
                $balance = number_format($balance, 4, '.', '');
                $waarde = $balance;
            } else {
                $waarde = number_format($balance * $koers, 4, '.', '');
            }

            $portfolio += $waarde;

            $balances['wallets'][] = ['name' => $currency, 'balance' => $balance, 'koers' => $koers, 'waarde' => $waarde];
        }
        $balances['portfolio'] = number_format($portfolio, 3, '.', '');
        
        return $balances;
    }
}
