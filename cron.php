<?php
/*
 .d8b.  db    db d888888b  .d88b.  d8888b.  .d88b.   .d88b.  d888888b      db    db d88888b d8888b.          db 
d8' `8b 88    88 `~~88~~' .8P  Y8. 88  `8D .8P  Y8. .8P  Y8. `~~88~~'      88    88 88'     88  `8D         o88 
88ooo88 88    88    88    88    88 88oooY' 88    88 88    88    88         Y8    8P 88ooooo 88oobY'          88 
88~~~88 88    88    88    88    88 88~~~b. 88    88 88    88    88         `8b  d8' 88~~~~~ 88`8b            88 
88   88 88b  d88    88    `8b  d8' 88   8D `8b  d8' `8b  d8'    88          `8bd8'  88.     88 `88. db       88 
YP   YP ~Y8888P'    YP     `Y88P'  Y8888P'  `Y88P'   `Y88P'     YP            YP    Y88888P 88   YD VP       VP

Released under the GNU GPLv3 license.
*/

require 'config.php';

function convertToArray($arr) {
    preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $arr, $match);
    $result = array();
    foreach ($match[1] as $x => $y){
    	$result[$y] = $match[2][$x];
    }
    return $result;
}

function checkStatus($vmarray) {
    $postArray = array("key" => $vmarray["key"], "hash" => $vmarray["hash"], "action" => "status");
    $conn = curl_init();
    curl_setopt($conn, CURLOPT_URL, $vmarray["host"]);
    curl_setopt($conn, CURLOPT_POST, 1);
    curl_setopt($conn, CURLOPT_TIMEOUT, 20);
    curl_setopt($conn, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($conn, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($conn, CURLOPT_HEADER, 0);
    curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($conn, CURLOPT_POSTFIELDS, $postArray);
    $data = curl_exec($conn);
    curl_close($conn);
    return convertToArray($data);
}

function startVM($vmarray) {
    $postArray = array("key" => $vmarray["key"], "hash" => $vmarray["hash"], "action" => "boot");
    $conn = curl_init();
    curl_setopt($conn, CURLOPT_URL, $vmarray["host"]);
    curl_setopt($conn, CURLOPT_POST, 1);
    curl_setopt($conn, CURLOPT_TIMEOUT, 20);
    curl_setopt($conn, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($conn, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($conn, CURLOPT_HEADER, 0);
    curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($conn, CURLOPT_POSTFIELDS, $postArray);
    $data = curl_exec($conn);
    curl_close($conn);
    $resultArray = convertToArray($data);
    return ($resultArray["statusmsg"] === "booted");
}

$pos = 1;
foreach ($cfgservers as $vm) {
    if ($vm["ignore"]) continue;
    $info = checkStatus($vm);
    if ($info["status"] === "error") {
        echo "<p>Error for VM " . $pos . ": " . $info["statusmsg"] . "</p>";
    } else if ($info["statusmsg"] === "offline") {
        echo "<p>VM " . $pos . " (" . $info["ipaddress"] . "/" . $info["hostname"] . ") is offline.</p>";
        echo "<p>Starting VM...</p>";
        if (startVM($vm)) {
            echo "<p>VM booted.</p>";
        } else {
            echo "<p>Error: The server was not booted.</p>";
        }
    } else {
        echo "<p>VM " . $pos . " (" . $info["ipaddress"] . "/" . $info["hostname"] . ") is online.</p>";
    }
    echo "<hr>";
    $pos++;
}
