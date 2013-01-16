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

if( php_sapi_name() !== 'cli' ) {
	echo 'Only to be used from the command line.';
	exit(1);
}

date_default_timezone_set( 'Europe/London' ); // apparently this is a "right" thing to do
define( 'PATH', realpath(pathinfo( __FILE__, PATHINFO_DIRNAME)) );
require( PATH.'/lib.php' );

$config = loadConf();

//var_dump( $argv );
//var_dump( $config );

if( !isset($argv[1]) ) {
	$argv[1] = '-undefined';
}

if( !getLock('setup') && ($argv[1] != '-freelock') ) {
	$r = 'Unable to secure process lock. Perhaps another setup process is currently running?';
	exit( $r."\n" );
}

switch( $argv[1] ) {
case( '-settimezone' ):
	// attempts to set the timezone
	if( !isset($argv[2]) ) {
		$r = 'Please specify a time zone';
	}
	elseif( !date_default_timezone_set($argv[2]) ) {
		$r = 'The timezone you specified was invalid. Please try again.';
		date_default_timezone_set( $config['timezone'] );
	}
	else {
		if( writeConf('timezone', $argv[2]) ) {
			$r = 'OK';
		}
		else {
			$r = 'Unable to save timezone setting.';
		}
	}
	break;

case( '-setproxy' ):
	// attempts to set a proxy
	if( !isset($argv[2]) ) {
		$r = 'Please specify a proxy.';
	}
	else {
		$p = parse_url( $argv[2] );
		$r1 = writeConf('proxy_host', $p['host']);
		$r2 = writeConf('proxy_port', $p['port']);
		if( $r1 && $r2 ) {
			$r = 'OK';
		}
		else {
			$r = 'Unable to save proxy setting.';
		}
	}
	break;

case( '-checkweb' ):
	// attempts to access the ArcHub central Arc app administration
	$f = urlExists( $config['ArcHub'] );
	if( $f === false ) {
		$r = 'Unable to access ArcHub.';
	}
	else {
		$r = 'OK';
	}
	break;

case( '-setapi' ):
	// sets the instance id and loads the instance's api urls
	if( !isset($argv[2]) ) {
		$r = 'Please specify an instance id. This can be found on your Arc Hub account page.';
	}
	elseif( !writeConf('instance', (int)$argv[2]) ) {
		$r = 'Unable to save instance id';
	}
	else {
		$config['instance'] = $argv[2];
		// get school's api urls from ArcHub
		$ch = curlInit( $config['ArcHub'].'/index.php?option=com_arc&view=instance&id='.$config['instance'].'&format=json' );
		$response = curl_exec( $ch );
		
		$data = json_decode( $response );
		if( $data == false ) {
			$r = 'Corrupt instance details. Please try again.';
		}
		elseif( !isset($data->url) || (($urlBase = $data->url) == '') ) {
			$r = 'Unable to retrieve api URLs.';
		}
		else {
			// add the (known and fixed) api parts of the url
			$url_request = $urlBase.'/index.php?option=com_arc_api&view=oauth&format=raw&task=request_token';
			$url_auth    = $urlBase.'/index.php?option=com_arc_api&view=oauth&task=authorise';
			$url_access  = $urlBase.'/index.php?option=com_arc_api&view=oauth&format=raw&task=access_token';
			$url_read    = $urlBase.'/index.php?option=com_arc_api&view=data&task=read&call=~api.call~&format=~api.format~';
			$url_write   = $urlBase.'/index.php?option=com_arc_api&view=data&task=write&call=~api.call~&format=~api.format~';
			
			// write the retrieved urls to the config
			$r1 = writeConf( 'url_request', $url_request );
			$r2 = writeConf( 'url_auth',    $url_auth );
			$r3 = writeConf( 'url_access',  $url_access );
			$r4 = writeConf( 'url_read',    $url_read );
			$r4 = writeConf( 'url_write',   $url_write );
			
			if( $r1 && $r2 && $r3 && $r4 ) {
				$r = 'OK';
			}
			else {
				$r = 'Unable to save url settings.';
			}
		}
	}
	break;

case( '-checkapi' ):
	// checks that the api urls loaded when customer id was set are accessible
	// attempts to access the ArcHub central Arc app administration
	$u1 = urlExists( $config['url_request'] );
	$u2 = urlExists( $config['url_auth'] );
	$u3 = urlExists( $config['url_access'] );
	$u4 = urlExists( $config['url_read'] );
	$u4 = urlExists( $config['url_write'] );
	
	$r = array();
	if( $u1 === false ) {
		$r[] = 'Unable to access request url.';
	}
	if( $u2 === false ) {
		$r[] = 'Unable to access auth url.';
	}
	if( $u3 === false ) {
		$r[] = 'Unable to access access url.';
	}
	if( $u4 === false ) {
		$r[] = 'Unable to access data-read url.';
	}
	if( $u4 === false ) {
		$r[] = 'Unable to access data-write url.';
	}
	
	if( empty($r) ) {
		$r = 'OK';
	}
	else {
		$r = implode( "\n", $r );
	}
	break;

case( '-oauthrequest' ):
	// goes to the url of the customer's api to get a request token
	$oauth = getOauth();
	
	try{
		$oauth->getRequestToken( $config['url_request'], $config['url_callback'] );
		$r1 = writeConf( 'oauth_r_token' , $oauth->getToken() );
		$r2 = writeConf( 'oauth_r_secret', $oauth->getTokenSecret() );
		
		if( $r1 && $r2 ) {
			echo 'Please open a browser and go to:'.PHP_EOL
				.$config['url_auth'].'&oauth_token='.$oauth->getToken()
				."\n";
			$r = 'OK';
		}
		else {
			$r = 'Unable to save request token.';
		}
	}
	catch( HTTP_OAuth_Consumer_Exception_InvalidResponse $E ) {
		echo $E->getStatus().': '.$E->getReasonPhrase().PHP_EOL;
		echo $E->getBody().PHP_EOL;
		$r = 'Failed to get a request token';
	}
	
	break;

case( '-oauthaccess' ):
	// takes a verification code and uses it to get an access token
	$oauth = getOauth();
	$oauth->setToken( $config['oauth_r_token'] );
	$oauth->setTokenSecret( $config['oauth_r_secret'] );
	
	try {
		$oauth->getAccessToken( $config['url_access'], $argv[2] );
		$r1 = writeConf( 'oauth_access_token' , $oauth->getToken() );
		$r2 = writeConf( 'oauth_access_secret', $oauth->getTokenSecret() );
		$r3 = clearConf( 'oauth_r_token' );
		$r4 = clearConf( 'oauth_r_secret' );
		
		if( $r1 && $r2 && $r3 && $r4 ) {
			$r = 'OK';
		}
		else {
			$r = 'Unable to save access token.';
		}
	}
	catch( HTTP_OAuth_Consumer_Exception_InvalidResponse $E ) {
		echo $E->getStatus().': '.$E->getReasonPhrase().PHP_EOL;
		echo $E->getBody().PHP_EOL;
		$r = 'Failed to get an access token.';
	}
	break;

case( '-oauthtest' ):
	$oauth = getOauth();
	$oauth->setToken( $config['oauth_access_token'] );
	$oauth->setTokenSecret( $config['oauth_access_secret'] );
	$url_read = str_replace( '~api.call~', urlencode('api.test.'.urlencode( json_encode( array('arg1'=>array('key_1'=>'test special chars like: &-/=%20%+')) ) ) ), $config['url_read'] );
	$url_read = str_replace( '~api.format~', 'json', $url_read );
	$url_write = str_replace( '~api.call~', urlencode('api.test'), $config['url_write'] );
	$url_write = str_replace( '~api.format~', 'json', $url_write );
	
	// add write (POST) test?
	
	try {
		$response = $oauth->sendRequest( $url_read, array(), 'GET' );
//		echo 'read got: ';var_dump( $response->getBody() );
		$data = json_decode( $response->getBody() );
		
		$response = $oauth->sendRequest( $url_write, array('key_1'=>'test special chars like: &-/=%20%+'), 'POST' );
//		echo 'write got: ';var_dump( $response->getBody() );
		$data = json_decode( $response->getBody() );
		
		if( isset($data->success) ) {
			$r = 'OK';
		}
		else {
			$r = 'Got bad data.';
		}
	}
	catch( HTTP_OAuth_Consumer_Exception_InvalidResponse $E ) {
		echo $E->getStatus().': '.$E->getReasonPhrase().PHP_EOL;
		echo $E->getBody().PHP_EOL;
		$r = 'Test failed: '.$rExtra;
	}
	break;

case( '-setschedule' ):
	
	break;

case( '-resetapi' ):
	$r = true;
	$r = clearConf( 'proxy' )               && $r;
	$r = clearConf( 'url_request' )         && $r;
	$r = clearConf( 'url_auth' )            && $r;
	$r = clearConf( 'url_access' )          && $r;
	$r = clearConf( 'url_read' )            && $r;
	$r = clearConf( 'url_write' )           && $r;
	$r = clearConf( 'oauth_r_token' )       && $r;
	$r = clearConf( 'oauth_r_secret' )      && $r;
	$r = clearConf( 'oauth_access_token' )  && $r;
	$r = clearConf( 'oauth_access_secret' ) && $r;
	$r = ( $r ? 'OK' : 'Could not reset all variables.' );
	break;

// #####  Command line reporter config
case( '-setrptuser' ):
	if( !isset($argv[2]) ) {
		$r = 'Please specify a user name.';
	}
	else {
		if( writeConf('rpt_user', $argv[2]) ) {
			$r = 'OK';
		}
		else {
			$r = 'Unable to save report user setting.';
		}
	}
	break;

case( '-setrptpwd' ):
	if( !isset($argv[2]) ) {
		$r = 'Please specify a password.';
	}
	else {
		if( writeConf('rpt_pwd', $argv[2]) ) {
			$r = 'OK';
		}
		else {
			$r = 'Unable to save report password setting.';
		}
	}
	break;

case( '-setrptclr' ):
	if( !isset($argv[2]) ) {
		$r = 'Please specify the location of the command line reporter.';
	}
	elseif( !is_file($argv[2]) ) {
		$r = 'Specified location not a file.';
	}
	else {
		if( writeConf('rpt_clr', $argv[2]) ) {
			$r = 'OK';
		}
		else {
			$r = 'Unable to save report clr setting.';
		}
	}
	break;

case( '-setrptoutdir' ):
	if( !isset($argv[2]) ) {
		$r = 'Please specify a target directory.';
	}
	elseif( !is_dir($argv[2]) ) {
		$r = 'Specified location not a directory.';
	}
	else {
		if( writeConf('rpt_outdir', $argv[2]) ) {
			$r = 'OK';
		}
		else {
			$r = 'Unable to save report outdir setting.';
		}
	}
	break;

case( '-resetrpt' ):
	$r = true;
	$r = clearConf( 'rpt_user' ) && $r;
	$r = clearConf( 'rpt_pwd' )  && $r;
	$r = clearConf( 'rpt_clr' )  && $r;
	break;

case( '-freelock' ):
	releaseLock( 'setup' );
	echo 'Lock freed. I hope you know what you\'re doing.'."\r\n";
	$r = 'OK';
	break;

case( '-undefined' ):
default:
	echo 'Valid options are: -settimezone, -setproxy, -checkweb, -setidapi, -checkapi, -oauthrequest, -oauthaccess, -oauthtest, -resetapi, -setrptuser, -setrptpwd, -setrptclr, -setrptoutdir, -resetrpt, -freelock'.PHP_EOL;
	$r = 'OK';
	break;
}

releaseLock( 'setup' );

exit( $r );
//exit( ( $r == 'OK' ) ? 1 : 0 );
?>