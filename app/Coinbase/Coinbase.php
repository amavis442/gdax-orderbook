<?php
namespace App\Coinbase;

class CoinbaseExchange {

    protected $timestamp;

    public function __construct($key, $secret, $passphrase) {
        
        $this->key = $key;
        $this->secret = $secret;
        $this->passphrase = $passphrase;
    
        
    }

    public function getTimestamp() {
        
        $client = new \GuzzleHttp\Client();
        
        $res = $client->request('GET', 'https://api.gdax.com/time');
        $json = json_decode($res->getBody());
        
        $this->timestamp = (int)round($json->epoch);
    }

    public function signature($request_path = '', $body = '', $method = 'GET') {
        $body = is_array($body) ? json_encode($body) : $body;
        $what = $this->timestamp . $method . $request_path . $body;

        return base64_encode(hash_hmac("sha256", $what, base64_decode($this->secret), true));
    }

    public function sendRequest($ext = '/accounts', $extraHeaders = null) {
        $this->getTimestamp();
        
        $client = new \GuzzleHttp\Client();

        $mandatoryHeaders = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',
                'Accept' => 'application/json',
                "CB-ACCESS-KEY" => $this->key,
                "CB-ACCESS-PASSPHRASE" => $this->passphrase,
                "CB-ACCESS-SIGN" => $this->signature($ext),
                "CB-ACCESS-TIMESTAMP" => $this->timestamp,
            ];
        
        if (!is_null($extraHeaders) && is_array($extraHeaders)) {
            $headers = array_merge($mandatoryHeaders, $extraHeaders);
        } else {
            $headers = $mandatoryHeaders;
        }
        
        
        
        $res = $client->request('GET', 'https://api.gdax.com' . $ext, [
            'headers' => $headers
        ]);

        if ($res->getStatusCode() == 200) {
            return json_decode($res->getBody());
        }
    }
    
    public function getFills()
    {
        $headers = ['order_id' =>'all',
                'product_id' => 'all'];
        
        return $this->sendRequest('/fills', $headers);
    }
    
}
