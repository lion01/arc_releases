<?php
$vidId = $_GET['vidId'];
$uploadId = $_GET['uploadId'];
$server = $_GET['server'];

$url = $server.'/progress.php?vidId='.(int)$vidId.'&uploadId='.$uploadId;
$ch = curl_init( );
curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_HEADER, false );
curl_exec( $ch );
curl_close( $ch );
?>