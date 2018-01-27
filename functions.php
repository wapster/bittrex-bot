<?php

define ( "API_KEY", 'XXXXXXXXX' );
define ( "API_SECRET", 'XXXXXXXXX' );
define ( "NONCE", time() );

// простое форматирование текста
function debug( $txt ) {
    echo "<pre>";
    print_r( $txt );
    echo "</pre>";
}

// Отправляем запрос - Получаем данные (в виде массива)
function getData( $uri ) {
    $sign = hash_hmac( 'sha512', $uri, API_SECRET );
    $ch = curl_init( $uri );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign) );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    $execResult = curl_exec( $ch );
    $data = json_decode( $execResult, true );
    return $data;
}

// Разница между двумя датами
function isTimeToCloseOrder( $time  ) {
    $currentTime = date("Y-m-d H:i:s");
    $targetTime = date_format( $time, "Y-m-d H:i:s" );
    $difference = strtotime($currentTime) - strtotime($targetTime); // разница, она же количество секунд
    $minuteDifference = floor($difference / 60); // полная разница в минутах
    return ( $minuteDifference >= 2880 ) ? true : false;
}


// Доступный баланс по конкретной монете
function getAvailableCoinBalance( $coin ) {
    $uri = 'https://bittrex.com/api/v1.1/account/getbalance?apikey=' . API_KEY . '&currency=' . $coin . '&nonce=' . NONCE;
    $obj = getData( $uri );
    $balance = $obj["result"]["Available"];
    return $balance;
}


// Получаем список торгуемых криптовалют
function getCurrencies() {
    $uri = 'https://bittrex.com/api/v1.1/public/getcurrencies';
    $obj = getData( $uri );
    return $obj["result"];
}

// Сведения о торговле по паре BTC-$coin
function getMarketSummary( $coin ) {
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

// Получаем значение Last по монете
function getLastPrice( $coin ) {
    $uri = 'https://bittrex.com/api/v1.1/public/getticker?market=btc-' . $coin;
    $data = getData( $uri );
    return $data["result"]["Last"];
}


// Проверка возможности продажи монеты по текущей (Last) цене
// fee 0,25% - процент Bittrex'a
// Если можно продать текущее количество монет по текущей цене -> true
function isCanSellCoin( $coin, $fee = (100 - 0.25), $min_size_order = 0.00050000 ) {
    $available = getAvailableCoinBalance( $coin );
    $last = getLastPrice( $coin );
    $bid = round( $available * $last, 8 );
    $currentBid = round ( ( $bid * $fee / 100), 8 );
    return ( $currentBid >= $min_size_order ) ? true : false;
}
