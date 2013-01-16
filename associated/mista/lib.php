<?php
/**
 * @package     Arc
 * @subpackage  Mista
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

// #####  Globals  #####

// $path must be defined in the calling file
define( 'DS', '\\' ); // directory separator for all file operations
define( 'P_CONF',  PATH.DS.'config.php' );
define( 'P_QUEUE', PATH.DS.'queue.txt' );
define( 'P_VERSION',  PATH.DS.'version.txt' );
define( 'P_LOCK',  PATH.DS.'_lock' );
define( 'F_MAINT', 'update_scripts.php' );
define( 'P_MAINT', PATH.DS.F_MAINT );
$c = loadConf();
date_default_timezone_set( $c['timezone'] ); // apparently this is a "right" thing to do

require( PATH.'/HTTP_OAuth_Wrapper.php' );

// #####  Utilities  #####
function loadConf()
{
	static $c = array();
	if( empty($c) ) {
		require( P_CONF );
		$c = $config;
	}
	return $c;
}

function writeConf( $key, $val )
{
	$config = loadConf();
	$confData = file_get_contents( P_CONF );
	if( array_key_exists( $key, $config ) ) {
		$find = '~(?<=^'.preg_quote( '$config[\''.$key.'\']', '~' )." = ).*;\\\r?\\\n~m";
		$replace = confQuote( $val ).";\r\n";
	}
	else {
		$find = '~(?<=^)(\\?>)(?=$)~m';
		$replace = '$config[\''.$key.'\'] = '.confQuote( $val ).";\r\n".'\\1';
	}
	$confData = preg_replace( $find, $replace, $confData );
//	var_dump($confData);
	$r = file_put_contents( P_CONF, $confData );
	return $r !== false;
}

function clearConf( $key )
{
	$config = loadConf();
	$confData = file_get_contents( P_CONF );
	if( array_key_exists( $key, $config ) ) {
		$confData = preg_replace( '~^'.preg_quote( '$config[\''.$key.'\']', '~' ).' = .*(\\r\\n|\\r|\\n)~m', '', $confData );
		$r = file_put_contents( P_CONF, $confData ) !== false;
	}
	else {
		$r = true;
	}
//	var_dump($confData);
	return $r;
}

function confQuote( $val )
{
	if( is_array($val) ) {
		$rv = array();
		foreach( $val as $k=>$v ) {
			$rv[] = confQuote($k).' => '.confQuote($v);
		}
		$rv = 'array( '.implode( ', ', $rv ).' )';
	}
	else {
		$rv = '\''.addslashes( $val ).'\'';
	}
	return $rv;
}

function urlExists( $url )
{
	if( (strpos($url, "http")) === false ) {
		$url = "http://" . $url;
	}
	$ch = curlInit( $url );
	
	$r = curl_exec( $ch );
	$headers = curlHeaders();
	
	// Anything but a 404 means the URL exists. We don't know at this point if there are
	// other params to be passed to it later so must ignore 303 (moved), 400 (bad request), etc responses
	return ( ($r !== false) && ( stripos( $headers['response'], '404 not found' ) === false ) );
}

function loadJobs()
{
	$queue = array();
	if( is_file( P_QUEUE ) ) {
		$data = file_get_contents( P_QUEUE );
	}
	else {
		$data = '';
	}
	if( !empty( $data ) ) {
		$rows = explode( "\r\n", $data );
		foreach( $rows as $row ) {
			if( !empty($row) ) {
				$parts = explode( '=', $row, 2 );
				$queue[$parts[0]] = json_decode( $parts[1], true );
			}
		}
	}
	return $queue;
}

function saveJobs( $queue )
{
	if( !is_array($queue) ) {
		return false;
	}
	foreach( $queue as $id=>$val ) {
		$queue[$id] = $id.'='.json_encode( $val );
	}
	$data = implode( "\r\n", $queue );
	$r = file_put_contents( P_QUEUE, $data );
	return ( $r !== false );
}

function loadVersion()
{
	$data = null;
	if( is_file( P_VERSION ) ) {
		$data = file_get_contents( P_VERSION );
		if( !empty( $data ) ) {
			$data = json_decode( $data, true );
		}
	}
	if( empty( $data ) ) {
		$data = array( 'current'=>'1.6.4-01' );
	}
	
	return $data;
}

function saveVersion( $data )
{
	if( !is_array( $data ) ) {
		return false;
	}
	
	$data = json_encode( $data );
	$r = file_put_contents( P_VERSION, $data );
	return ( $r !== false );
}

/**
 * Attempts to secure the lock file
 * @return boolean  True on successful aquisition of the lock, false otherwise
 */
function getLock( $process = '')
{
	global $path;
	$lockFile = P_LOCK.$process;
	if( file_exists( $lockFile ) ) {
		return false;
	}
	else {
		$r = file_put_contents( $lockFile, '1' );
		return (bool)$r;
	}
	
}

/**
 * Attempts to release (remove) the lock file
 * @return boolean  True on successful release of the lock, false otherwise
 */
function releaseLock( $process = '' )
{
	global $path;
	return ( is_file( P_LOCK.$process ) ? unlink( P_LOCK.$process ) : true );
}

/**
 * Initialise a curl handler setting proxy if specified
 * 
 * @param string $url  The curl url
 * @param boolean $retTrans  Set 'CURLOPT_RETURNTRANSFER, true' option? Defaults to true
 * @return $ch  The curl handler
 */
function curlInit( $url )
{
	$config = loadConf();
	
	curlHeaders( false ); // clean out any previous headers
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false ); // should use ca_info, but had trouble making it work
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 ); // should use ca_info, but had trouble making it work
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_HEADERFUNCTION, 'curlHeaders' );
	
	if( !empty($config['proxy_host']) ) {
		curl_setopt( $ch, CURLOPT_PROXY, $config['proxy_host'].':'.$config['proxy_port'] );
	}
	
	return $ch;
}

function curlHeaders( $ch = null, $header = null )
{
	static $data = array();
	
	// reset data
	if( $ch === false ) {
		$data = array();
	}
	// retrieve data
	if( is_null($ch) ) {
		$retVal = $data;
	}
	// parse headers into data
	else {
		$retVal = strlen( $header );
		$header = trim( $header );
		if( !empty( $header ) ) {
			$parts = explode( ':', $header, 2 );
			if( count( $parts ) == 1 ) {
				$parts = array( 'Response', $parts[0] );
			}
			$parts[0] = trim( strtolower( $parts[0] ) );
			$parts[1] = trim( $parts[1] );
			
			$data[$parts[0]] = $parts[1];
		}
	}
	return $retVal;
}

function getOAuth()
{
	$config = loadConf();
	
	$o = new HTTP_OAuth_Consumer_Arc( $config['cons_key'], $config['cons_secret'] );
	$rConf = array( 
		  'proxy_host'     =>$config['proxy_host']
		, 'proxy_port'     =>$config['proxy_port']
		, 'ssl_verify_peer'=>$config['ssl_verify']
		, 'ssl_verify_host'=>$config['ssl_verify']
		, 'adapter'        =>'HTTP_Request2_Adapter_Curl_Arc'
	);
	$o->setConfig( $rConf );
	
	return $o;
}

?>