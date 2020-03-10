<?php
require_once '../vendor/autoload.php';

//$response = file_get_contents_curl('https://2ip.ru/', '96.77.77.53:54321', CURLPROXY_SOCKS4);
//$parts = explode('<big id="d_clip_button">', $response);
//$partsNext = explode('</big>', $parts[1]);
//var_dump('Response ip: ' . $partsNext[0]);
var_dump('guzzle:');
var_dump(file_get_contents_guzzle('https://2ip.ru/', '96.77.77.53:54321', CURLPROXY_SOCKS4));

function file_get_contents_guzzle($url, $proxy, $proxyType) {
    $client = new \GuzzleHttp\Client([
        'curl' => [
            CURLOPT_PROXY => $proxy,
            CURLOPT_PROXYTYPE => $proxyType,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2
        ],
        'timeout' => 10
    ]);

    return $client
        ->request('get', $url, [
            'connect_timeout' => 10
        ])->getBody()
        ->getContents();
}
function file_get_contents_curl($url, $proxy, $proxyType) {
    $ch = curl_init ();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt ($ch, CURLOPT_PROXY, $proxy);
    curl_setopt ($ch, CURLOPT_PROXYTYPE, $proxyType);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt ($ch, CURLOPT_FAILONERROR, true);
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
    $result = curl_exec($ch);
    //print curl_errno ($ch);
    //print $result;
    curl_close ($ch);

    return $result;
}
