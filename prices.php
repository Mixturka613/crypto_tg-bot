<?php

require_once "vendor/autoload.php";
use Codenixsv\CoinGeckoApi\CoinGeckoClient;

class Prices {

    public function getPrice($identyfi) {
        $client = new CoinGeckoClient();
        $data = $client->simple()->getPrice($identyfi, 'usd,rub');
        return $data;
    }

}