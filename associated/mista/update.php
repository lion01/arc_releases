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
	echo 'only to be used from the command line';
	exit(1);
}

date_default_timezone_set( 'Europe/London' ); // apparently this is a "right" thing to do
define( 'PATH', realpath( pathinfo( __FILE__, PATHINFO_DIRNAME ) ) );
require( PATH.'/lib.php' );

$config = loadConf();
$version = loadVersion();
//var_dump( $argv );
//var_dump( $config );

if( !isset($argv[1]) ) {
	$argv[1] = '-undefined';
}

if( !getLock( 'update' ) && ($argv[1] != '-freelock') ) {
	$r = 'Unable to secure process lock. Perhaps another update is currently running?';
	exit( $r."\n" );
}

$_break = !( isset($argv[2]) && ($argv[2] == '-runon') );

switch( $argv[1] ) {
case( '-run' ):
	$_break = false;
case( '-get' ):
	$r = 'OK'; // assume everything will be fine until shown otherwise
	
	// Contacts the Arc hub to find out what jobs are queued up
	
	// check the hub is accessible
	$f = urlExists( $config['ArcHub'], 'r' );
	if( $f === false ) {
		$r = 'Unable to access Arc hub';
		break;
	}
	
	// retrieve the update list
	$ch = curlInit( $config['ArcHub'].'/index.php?option=com_arc_app&view=updates&id='.$config['app_id'].'&format=json' );
	$updates = curl_exec( $ch );
	
	if( $updates == false ) {
		$r = 'Unable to retrieve update';
		break;
	}
	$updates = json_decode( $updates );
	
	if( !is_array( $updates ) ) {
		$r = 'Unable to read update list';
		break;
	}
	
	// parse update list for new versions,
	// tracing back to the last complete update OR the current version 
	$done = false;
	$dlQueue = array();
	$cur = array_pop( $updates );
	while( !$done && ($cur->version != $version['current']) ) {
		array_unshift( $dlQueue, $cur );
		if( $cur->type == 'complete' ) {
			$done = true;
		}
		else {
			// find the preceding update in the list
			$found = false;
			foreach( $updates as $u ) {
				if( $u->id == $cur->prev_version ) {
					$cur = $u;
					$found = true;
					break;
				}
			}
			if( $found == false ) {
				$r = 'Unable to find previous version';
				break;
			}
		}
		
	}
	
	if( !empty( $dlQueue ) ) {
		// having found a non-empty update route, reconstruct the versions data with the up-to-date state of affairs
		$newVer = array( 'current'=>$version['current'] );
		
		foreach( $dlQueue as $dl ) {
			if( isset( $version[$dl->version] ) ) {
				$newVer[$dl->version] = $version[$dl->version];
				unset( $version[$dl->version] );
			}
			else {
				$newVer[$dl->version] = array( 'status'=>'new', 'extId'=>$dl->id, 'file'=>null, 'started'=>null );
			}
		}
		// remove any downloads that are no longer needed
		foreach( $version as $old ) {
			if( isset($old['file']) && is_file( $old['file'] ) ) {
				unlink( $old['file'] );
			}
		}
		
		$version = $newVer;
		saveVersion( $version );
	}
	
	// now there's a list of "now -> most recent" download them in turn
	reset( $version ); // skip over the first item which is "current"
	while( next( $version ) && ($r == 'OK') ) {
		$nv = &$version[key($version)];
		
		// abort the file download if it was started too long ago
		$timeout = 82800; // 23 hours = (23*60*60) 82800 seconds
		if( $nv['status'] == 'downloading'
		 && ( ($nv['started'] + $timeout) > time() ) ) {
			$nv['status'] = 'cancelled';
			saveVersion( $version );
			unlink( $nv['file'] );
		}
		
		// download the file if it hasn't already been downloaded
		if( $nv['status'] != 'ready' ) {
			$tmpFile = dirname( __FILE__).'/updates/tmp';
			$nv['status'] = 'downloading';
			$nv['started'] = time();
			saveVersion( $version );
			
			$handle = fopen( $tmpFile, 'w' );
			if( !$handle ) {
				$r = 'Unable to open download file';
				break;
			}
			$ch = curlInit( $config['ArcHub'].'/index.php?option=com_arc_app&view=update&id='.$nv['extId'].'&format=file', false );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
			curl_setopt( $ch, CURLOPT_FILE, $handle );
			$res = curl_exec( $ch );
			fclose( $handle );
			
			// with the file downloaded or not, mark it as ready or remove any junk that formed
			if( $res == false ) {
				$nv['status'] = 'failed';
				saveVersion( $version );
				unlink( $fName );
				$r = 'Failed to download the update';
			}
			else {
				$headers = curlHeaders();
				if( isset( $headers['content-disposition'] ) ) {
					$matches = array();
					preg_match( '~(?<=filename=").*(?=")~i', $headers['content-disposition'], $matches );
					$nv['file'] = ( isset( $matches[0] ) ? $matches[0] : '' );
				}
				if( !isset( $nv['file'] ) || empty( $nv['file'] ) ) {
					$nv['file'] = key( $version );
				}
				$nv['file'] = dirname( __FILE__).'/updates/'.$nv['file'];
				$nv['status'] = 'ready';
				rename( $tmpFile, $nv['file'] );
				saveVersion( $version );
			}
		}
	}
	
	if( $_break ) { break; }
	

/*
 * When applying updates the following describes the use of the functions in the update_scripts file
 * That file must define a class named like "Maint_1_2_3_rc4_5" for release 1.2.3-rc4.5.
 * which extends the class "Maint"
 * 
 * presence of updates checked
 * Maint class for current version instanciated
 * --
 * Maint->beforeUpdateFrom
 * new version's Maint file copied and class instanciated to replace old
 * Maint->beforeUpdateTo
 * copy files
 * Maint->afterUpdateTo
 * clean up update files
 * -- repeat for subsequent versions
 */
case( '-apply' ):
	if( count($version) <= 1 ) {
		echo 'No updates to install';
		$r = 'OK';
		break;
	}
	if( !getLock( 'setup' ) || !getLock( 'poll' ) ) {
		$r = 'Unable to secure secondary locks. The update process must not conflict with a running setup or poll script';
		break;
	}
	
	// If we've got this far then there's at least 1 update to apply
	$ok = true;
	$updateCount = 0;
	$destBase = PATH.'/updates/';
	
	$maintainer = getMaintainer( $version['current'] );
	if( $maintainer ) { $maintainer->beforeUpdateFrom(); }
	
	$zip = new ZipArchive();
	reset( $version );
	while( $ok && ( $update = next( $version ) ) !== false ) {
		echo 'applying update '.key( $version )."\r\n";
		$updateCount++;
		
		if( $zip->open( $update['file'] ) && $zip->numFiles > 0 ) {
			$dest = $destBase.key($version);
			_remove( $dest );
			mkdir( $dest );
			// extract and copy files over current files
			if( $zip->extractTo( $dest.'/' ) ) {
				// maintenance script first
				_remove( P_MAINT );
				if( file_exists( $dest.'/'.F_MAINT ) ) {
					rename( $dest.'/'.F_MAINT, P_MAINT );
				}
				$maintainer = getMaintainer( key($version) );
				if( $maintainer ) { $maintainer->beforeUpdateTo(); }
				
				// now all the other files
				$didCopy = copyDir( $dest, PATH );
			}
			else {
				$didCopy = false;
			}
			
			$zip->close();
			// clean up only once everything has gone right
			if( $didCopy ) {
				if( $maintainer ) { $maintainer->afterUpdateTo(); }
				_remove( $dest );
				_remove( $update['file'] );
				$version[key($version)]['status'] = 'applied';
			}
			else {
				echo 'Problem applying update'."\r\n";
				$ok = false;
				$version[key($version)]['status'] = 'not applied';
			}
		}
		else{
			echo 'Problem opening update'."\r\n";
			$ok = false;
			$version[key($version)]['status'] = 'not applied';
		}
		
		saveVersion( $version );
	}
	
	if( $ok ) {
		end( $version );
		$version = array( 'current'=>key($version) );
		saveVersion( $version );
	}
	
	echo $updateCount.' updates applied'."\r\n";
	$r = ( $ok ? 'OK' : 'There was a problem applying one or more updates' );
	releaseLock( 'setup' );
	releaseLock( 'poll' );
	if( $_break ) { break; }
	
case( '-freelock' ):
	releaseLock( 'update' );
	echo 'Lock freed. I hope you know what you\'re doing'."\r\n";
	$r = 'OK';
	break;

case( '-undefined' ):
default:
	echo 'valid options are: -get, -apply, -freelock'.PHP_EOL;
	$r = 'OK';
	break;
}

releaseLock( 'update' );

exit( $r );
//exit( ( $r == 'OK' ) ? 1 : 0 );

function copyDir( $src, $dest )
{
	$src .= '/';
	$dest .= '/';
	$handle = opendir( $src );
	$ok = true;
	while( false !== ( $entry = readdir( $handle ) ) ) {
		$r = true;
		if( $entry == '.' || $entry == '..' ) {
			continue;
		}
		
		// remove old version
		if( file_exists( $dest.$entry ) ) {
			$r = _remove( $dest.$entry );
		}
		if( !$r ) {
			break;
		}
		
		// copy the new version in
		if( is_file( $src.$entry ) ) {
			$r = copy( $src.$entry, $dest.$entry );
		}
		elseif( is_dir( $src.$entry ) ) {
			$r = mkdir( $src.$entry );
			if( $r ) {
				$r = copyDir( $src.$entry, $dest.$entry );
			}
		}
		
		$ok = $ok && $r;
	}
	
	return $ok;
}

function _remove( $obj )
{
	$success = true;
	// do not act on dot directories
	if( ( basename( $obj ) == '.' ) || ( basename( $obj ) == '..' ) || !file_exists( $obj ) ) {
		return $success;
	}
	
	// links
	if( is_link( $obj ) ) {
		$success = ( @unlink( $obj ) || @rmdir( $obj ) );
	}
	// files
	elseif( is_file( $obj ) ) {
		$success = unlink( $obj );
	}
	// directories
	elseif( is_dir( $obj ) ) {
		$handle = opendir( $obj );
		while( false !== ( $entry = readdir( $handle ) ) ) {
			$success = _remove( $obj.'/'.$entry ) && $success;
		}
		closedir( $handle );
		$success = rmdir( $obj ) && $success;
	}
	
	return $success;
}

function getMaintainer( $v )
{
	static $cache = array();
	
	if( !isset($cache[$v]) ) {
		if( file_exists( P_MAINT ) ) {
			include( P_MAINT );
		}
		$maintClass = 'Maint_'.preg_replace( '~[^a-zA-Z0-9]~', '_', $v );
		if( class_exists( $maintClass ) ) {
			$cache[$v] = new $maintClass();
		}
		else {
			$cache[$v] = false;
		}
	}
	return $cache[$v];
}


/**
 * Parent class to all maint classes
 * Defines stubs for the update functions so calling them won't cause an error
 */
class Maint
{
	function beforeUpdateFrom() { return true; }
	function beforeUpdateTo()   { return true; }
	function afterUpdateTo()    { return true; }
}
?>