<?php

define ( "API_KEY", 'b52a27406d4f49bc87d70713b023ee3a' );
define ( "API_SECRET", 'dc41d95d73544bddab1b5c615569540c' );
define ( "NONCE", time() );

function getData( $uri ) {
    $sign = hash_hmac( 'sha512', $uri, API_SECRET );
    $ch = curl_init( $uri );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign) );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    $execResult = curl_exec( $ch );
    $obj = json_decode( $execResult, true );
    return $obj;
}

function bittrexbalance() {
    $uri = 'https://bittrex.com/api/v1.1/account/getbalance?apikey=' . API_KEY . '&currency=BTC&nonce=' . NONCE;
    $obj = getData( $uri );
    $balance = $obj["result"]["Available"];
    return $balance;
}

echo bittrexbalance();

// Получаем список торгуемых криптовалют
function getCurrencies() {
    $uri = 'https://bittrex.com/api/v1.1/public/getcurrencies';
    $obj = getData( $uri );
    return $obj["result"];
}

// Сведения о торговле по паре BTC-$coin
function getMarkeSummary( $coin ) {
    $uri = 'https://bittrex.com/api/v1.1/public/getmarketsummary?market=btc-' . $coin;
    $data = getData( $uri );
    return $data["result"][0];
}


function getOpenOrders( $coin ) {
    $uri = 'https://bittrex.com/api/v1.1/market/getopenorders?apikey=' . API_KEY . '&market=BTC-' . $coin . '&nonce=' . NONCE;
    $data = getData( $uri );
    return $data["result"];
}

// Получаем значения BID, ASK, LAST по нужной монете $coin
function getTicker( $coin ) {
    $uri = 'https://bittrex.com/api/v1.1/public/getticker?market=btc-' . $coin;
    $data = getData( $uri );
    return $data["result"];
}

echo "<pre>";
print_r( getTicker('OMG') );
echo "</pre>";
