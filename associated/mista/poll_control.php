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

//ini_set( 'display_errors', true );
//error_reporting( E_ALL );
//var_dump( $argv );
//var_dump( $config );

if( !isset($argv[1]) ) {
	$argv[1] = '-undefined';
}

if( !getLock( 'poll' ) && ($argv[1] != '-freelock') ) {
	$r = 'Unable to secure process lock. Perhaps another report is currently running?';
	exit( $r."\n" );
}

$_break = !( isset($argv[2]) && ($argv[2] == '-runon') );

switch( $argv[1] ) {
case( '-run' ):
	$_break = false;
case( '-getjobs' ):
	// Contacts the API to find out what jobs are queued up
	
	// check the API is accessible
	$f = urlExists( $config['url_read'], 'r' );
	if( $f === false ) {
		$r = 'Unable to access API';
		break;
	}
	
	// retrieve the job list from the API
	$oauth = getOauth();
	$oauth->setToken( $config['oauth_access_token'] );
	$oauth->setTokenSecret( $config['oauth_access_secret'] );
	$url_read = str_replace( array( '~api.call~', '~api.format~' ), array( urlencode( 'core.importqueue' ), 'json' ), $config['url_read'] );
	
	try{
		$response = $oauth->sendRequest( $url_read, array(), 'GET' );
	}
	catch( HTTP_OAuth_Consumer_Exception_InvalidResponse $E ) {
		$rExtra = ( isset( $response ) ? $response->getStatus().': '.$response->getReasonPhrase() : '' );
		echo 'fail:'.PHP_EOL; var_dump( $E );
		$r = 'Getting job list failed: '.$rExtra;
		break;
	}
	
	$inList = json_decode( $response->getBody(), true );
	
	// get the list of jobs already being dealt with
	$curList = loadJobs();
	
	// compare, add where appropriate
	foreach( $inList as $job ) {
		if( !isset($curList[$job['id']]) ) {
			$job['taken'] = 1;
			$curList[$job['id']] = $job;
		}
	}
	
	if( saveJobs( $curList ) ) {
		$r = 'OK';
	}
	else {
		$r = 'Failed to save job list';
	}
	
	if( $_break ) { break; }

case( '-generate' ):
	// if the export script isn't currently running and there are jobs to do, start it
	$curList = loadJobs();
	if( empty($curList) ) {
		echo 'No jobs to run'."\r\n";
		$r = 'OK';
		break;
	}
	
	// If we've got this far then there's at least 1 report to run
	$ok = true;
	$jobCount = 0;
	foreach( $curList as $jId=>$job ) {
		echo 'Running report: '.$job['call']."\r\n";
		if( $job['ready'] || !empty( $job['datafiles'] ) || ( $job['src_name'] != 'MIStA - SIMS' ) )  {
			continue;
		}
		$jobCount++;
		$ok = runReport( $job, true ) && $ok;
		$curList[$jId] = $job;
	}
	saveJobs( $curList );
	
	echo $jobCount.' jobs processed'."\r\n";
	$r = ( $ok ? 'OK' : 'There was a problem generating one or more data sets' );
	
	if( $_break ) { break; }

case( '-upload' ):
	// attempt to upload all previously generated data files
	$curList = loadJobs();
	if( empty($curList) ) {
		echo 'No jobs to process'."\r\n";
		$r = 'OK';
		break;
	}
	
	// If we've got this far then there's at least 1 report to see about uploading
	$ok = true;
	$jobCount = 0;
	foreach( $curList as $jId=>$job ) {
		echo 'Uploading report: '.$job['call'].'... ';
		if( empty( $job['datafiles'] ) ) {
			echo 'nothing to do'."\r\n";
		}
		else {
			echo 'uploading: '."\r\n";
			$jobCount++;
			$ok = uploadJob( $job ) && $ok; // takes it by reference and shuffles files from "datafilse" to "uploadedfiles"
			$curList[$jId] = $job;
		}
	}
	saveJobs( $curList );
	
	echo $jobCount.' jobs processed'."\r\n";
	$r = ( $ok ? 'OK' : 'There was a problem uploading one or more data sets' );
	
	if( $_break ) { break; }

case( '-cleanup' ):
	// attempt to remove all previously uploaded data files
	$curList = loadJobs();
	if( empty($curList) ) {
		echo 'No jobs to process'."\r\n";
		$r = 'OK';
		break;
	}
	
	// If we've got this far then there's at least 1 report to see about clearing up after
	$ok = true;
	$jobCount = 0;
	foreach( $curList as $jId=>$job ) {
		echo 'Cleaning up report: '.$job['call'].'... ';
		if( $job['ready'] != 1 ) {
			echo 'job not marked as complete: '."\r\n";
		}
		elseif( !empty( $job['datafiles'] ) ) {
			echo 'some files not yet uploaded'."\r\n";
		}
		else {
			echo 'removing'."\r\n";
			$jobCount++;
			$okTmp = deleteJob( $job );
			$ok = $okTmp && $ok;
			if( $okTmp ) {
				unset( $curList[$jId] );
			}
			else {
				$curList[$jId] = $job;
			}
		}
	}
	saveJobs( $curList );
	
	echo $jobCount.' jobs processed'."\r\n";
	$r = ( $ok ? 'OK' : 'There was a problem removing one or more data sets' );
	break;

case( '-reset' ):
	$curList = loadJobs();
	if( empty($curList) ) {
		echo 'No jobs to process'."\r\n";
		$r = 'OK';
		break;
	}
	
	$ok = true;
	if( !isset( $argv[2] ) || $isset( $curList[$argv[2]] ) ) {
		foreach( $curList as $jobId=>$job ) {
			$ok = resetJob( $job ) && $ok;
			$ok = deleteJob( $job ) && $ok;
			$curList[$jobId] = $job;
		}
	}
	else {
		$jobId = $argv[2];
		$job = $curList[$jobId];
		$ok = resetJob( $job ) && $ok;
		$ok = deleteJob( $job ) && $ok;
		$curList[$jobId] = $job;
	}
	
	$r = ( $ok ? 'OK' : 'There was a problem resetting one or more jobs' );
	
	break;

case( '-freelock' ):
	releaseLock( 'poll' );
	echo 'Lock freed. I hope you know what you\'re doing'."\r\n";
	$r = 'OK';
	break;

case( '-undefined' ):
default:
	echo 'valid options are: -run, -getjobs, -generate, -upload, -cleanup, -reset, -freelock'.PHP_EOL;
	$r = 'OK';
	break;
}

releaseLock( 'poll' );

exit( $r );
//exit( ( $r == 'OK' ) ? 1 : 0 );


// #####  These functions take care of actually generating the report  #####

/**
 * Runs a report and gets the results. Doesn't return anything, but echos out useful info
 *
 * @param array $job  The report job to run, including all relevant settings
 * @param boolean $debug  Should we generate a file of debugging information?
 */
function runReport( &$job, $debug = false )
{
	$config = loadConf();
	$rpt = $job['call'];
	$params = $job['params'];
	
	ob_start();
	$job['dir'] = $config['rpt_outdir'].DS.$job['id'].DS;
	mkdir( $job['dir'] );
	$outFile = $job['dir'].$rpt.'.xml';
	$paramFile = $job['dir'].$rpt.'_params.xml';
	
	if( $debug ) {
		echo 'runReport args: '; var_dump( func_get_args() );
		date_default_timezone_set('Europe/London');
		$job['logfile'] = $job['dir'].'log_'.$rpt.'_'.date('Ymd_His').'.log';
	}
	
	// Get and modify parameters if any were provided
	$hasParams = false;
	if( !empty( $params ) ) {
		$rptCmd = '"'.$config['rpt_clr'].'"'
			.' /USER:'.$config['rpt_user']
			.' /PASSWORD:'.$config['rpt_pwd']
			.' /REPORT:'.$rpt
			.' /OUTPUT:"'.$paramFile.'"'
			.' /PARAMDEF';
		shell_exec( $rptCmd );
		
		$txt = file_get_contents( $paramFile );
		// Put the values we were given into the params file
		foreach( $params as $name=>$val ) {
			$val = htmlspecialchars( $val );
			
			switch( strtolower($name) ) {
			case( 'start' ):
			case( 'end' ):
				$txt = preg_replace( '~(?<=<PromptText>'.preg_quote($name).'</PromptText><Values><Date>)([^<]*?)(?=</Date></Values>)~', $val, $txt, 1 );
				break;
			
			case( 'effective' ):
				$txt = preg_replace( '~(?<=<Name>EffectiveDate</Name><Type>Date</Type><Values><Date>)(.*?)(?=</Date></Values>)~', $val, $txt, 1 );
				$txt = preg_replace( '~(?<=<Parameter id="EffectiveDate" subreportfilter="FALSE" bypass=")(.*?)(?=">)~', 'FALSE', $txt, 1 );
				break;
			
			case( 'active_start' ):
				if( !isset($params['active_end']) ) {
					break;
				}
				$txt = preg_replace( '~(?<=<PromptText>End date is after</PromptText><Values><Date>)(.*?)(?=</Date></Values>)~', $val, $txt, 1 );
				break;
			
			case( 'active_end' ):
				if( !isset($params['active_start']) ) {
					break;
				}
				
				$txt = preg_replace( '~(?<=<PromptText>Start date is before</PromptText><Values><Date>)(.*?)(?=</Date></Values>)~', $val, $txt, 1 );
				break;
			}
		}
		
		file_put_contents( $paramFile, $txt );
		$hasParams = true;
	}
	
	$preExecTime = ini_get('max_execution_time'); // get the max_execution_time before we mess with it
	set_time_limit( $config['rpt_tMax'] );
	
	// Run the report, saving the output in a file
	$rptCmd = '"'.$config['rpt_clr'].'"'
		.' /USER:'.$config['rpt_user']
		.' /PASSWORD:'.$config['rpt_pwd']
		.' /REPORT:'.$rpt
		.' /OUTPUT:"'.$outFile.'"'
		.( $hasParams ? ' /PARAMFILE:"'.$paramFile.'"' : '' );
	shell_exec( $rptCmd );
	set_time_limit($preExecTime); // reset the max_execution_time to its original value
	
	$files = parseXml( $outFile, $debug );
	
	if( $debug ) {
		echo 'outFile: '; var_dump($outFile);echo "\r\n";
		echo 'paramFile: '; var_dump($paramFile);echo "\r\n";
		echo 'dataFiles: '; var_dump($files);echo "\r\n";
		echo 'returned: '; var_dump(!empty( $files ));echo "\r\n";
		$handle = fopen($job['logfile'], 'w+');
		fwrite( $handle, '##### get_report output'."\r\n"
			.ob_get_clean()
			.'##### end get_report output' );
		fclose( $handle );
	}
	else {
		ob_end_clean();
	}
	
	$job['datafiles'] = $files;
	return !empty( $files );
}


/**
 * Compresses an xml file into .gz files of a defined maximum size.
 * The genereated files must be decompressed then stitched back together
 * into a single file to be read as valid xml
 * 
 * @param $inFile string  The name of the file to split
 */
function parseXml( $inFile, $debug )
{
	$outFiles = array();
	$config = loadConf();
	
	if( $debug ) {
		echo 'splitting xml func args: '; var_dump( func_get_args() );
		echo 'file size: '; var_dump( filesize($inFile) );
		echo 'max size: '; var_dump( $config['rpt_sizeMax'] );
	}
	
	$f = fopen( $inFile, 'r' );
	$numLines = 0;
	$numFiles = 0;
	if( $f !== false ) {
		while( !feof( $f ) ) {
			if( !isset( $outFiles[$numFiles] ) ) {
				$outFiles[$numFiles] = str_replace( '.xml', '_'.$numFiles.'.gz', $inFile );
				$gz = gzopen( $outFiles[$numFiles], 'wb9' );
			}
			
			gzwrite( $gz, fgets( $f ) );
			
			if( $numLines++ > $config['rpt_sizeAccuracy'] ) {
				$numLines = 0;
				gzclose( $gz ); // if the file is not closed, even with clearstatcache the size doesn't get updated
				clearstatcache();
				$s = stat( $outFiles[$numFiles] );
				echo 'size: '.$s['size']."\r\n";
				if( $s['size'] > $config['rpt_sizeMax'] ) {
					$numFiles++;
				}
				else {
					$gz = gzopen( $outFiles[$numFiles], 'ab9' );
				}
			}
		}
	}
	gzclose( $gz );
	fclose( $f );
	unlink( $inFile );
	
	return $outFiles;
}

/**
 * Uploads each of the job's data files
 * and marks the job as ready if everything works out
 * 
 * @param $job array  The job to upload. This array contains the list of data files amongst other things
 */
function uploadJob( &$job )
{
	if( !is_array( $job['datafiles'] ) || empty( $job['datafiles'] ) ) {
		return null;
	}
	$config = loadConf();
	$retVal = true; // assume this will all work until shown otherwise
	
	static $oauth = false;
	static $url_write = false;
	static $url_ready = false;
	if( $oauth === false ) {
		$oauth = getOauth();
		$oauth->setToken( $config['oauth_access_token'] );
		$oauth->setTokenSecret( $config['oauth_access_secret'] );
		$url_write = str_replace( array( '~api.call~', '~api.format~' ), array( urlencode( 'core.dataFile'   ), 'json' ), $config['url_write'] );
		$url_ready = str_replace( array( '~api.call~', '~api.format~' ), array( urlencode( 'core.dataSource' ), 'json' ), $config['url_write'] );
	}
	
	// upload all the files derived from this job's base data filename
	foreach( $job['datafiles'] as $fKey=>$fName ) {
		if( !file_exists( $fName ) ) {
			unset( $job['datafiles'][$fKey] );
			$job['errorfiles'][$fKey] = $fName;
		}
		echo ' - '.$fName."\r\n";
		
		try{
			// the zipped binary data is base64 encoded before being urlencoded as without this
			// the urlencoding roughly triples the length by %-encoding almost every byte. 
			$response = $oauth->sendRequest( $url_write, array( 'fileName'=>basename($fName), 'queueId'=>$job['id'], 'raw'=>base64_encode( file_get_contents($fName) ) ), 'POST' );
			$result = json_decode( $response->getbody(), true );
			
			if( ($response->getStatus() == 200) && is_array($result) && ($result['status'] == 0) ) {
				$job['uploadedfiles'][$fKey] = $fName;
				unset( $job['datafiles'][$fKey] );
				$retVal = $retVal && true;
			}
			else {
				echo 'problem uploading data'.PHP_EOL;
				echo $response->getStatus().PHP_EOL;
				echo $response->getReasonPhrase().PHP_EOL;
				echo $response->getBody().PHP_EOL;
				
				$retVal = false;
				break; // **** should this just give up like this ?
			}
		}
		catch( HTTP_OAuth_Consumer_Exception_InvalidResponse $E ) {
			$retVal = false;
			$rExtra = ( isset( $response ) ? $response->getStatus().': '.$response->getReasonPhrase() : '' );
			echo ' *** Upload failed with code: '.$rExtra."\r\n"
				.' *** '.$E."\r\n";
			break;
		}
	}

	if( $retVal ) {
		try {
			$response = $oauth->sendRequest( $url_ready, array( 'queueId'=>$job['id'], 'src'=>'MIStA - SIMS' ), 'POST' );
			$result = json_decode( $response->getBody(), true );
			if( ($response->getStatus() == 200) && is_array($result) && ($result['status'] == 0) ) {
				$job['ready'] = 1;
				$retVal = $retVal && true;
			}
			else {
				$retVal = false;
			}
		}
		catch( HTTP_OAuth_Consumer_Exception_InvalidResponse $E ) {
			$retVal = false;
			$rExtra = ( isset( $response ) ? $response->getStatus().': '.$response->getReasonPhrase() : '' );
			echo 'Completion marking failed with code: '.$rExtra."\r\n"
				.' *** '.$E."\r\n";
		}
	}
	
	return $retVal;
}

function resetJob( &$job )
{
	$job['ready'] = 0;
	unset( $job['dir'] );
	unset( $job['logfile'] );
	unset( $job['datafiles'] );
	unset( $job['errorfiles'] );
	unset( $job['uploadedfiles'] );
	
	return true;
}

/**
 * Removes each of the job's associated files
 * 
 * @param $job array  The job to delete. This array contains the list of data files amongst other things
 */
function deleteJob( &$job )
{
	// remove the data files
	if( !empty( $job['datafiles'] ) ) {
		foreach( $job['datafiles'] as $file ) {
			unlink( $file );
		}
	}
	
	// remove the uploaded data files
	if( !empty( $job['uploadedfiles'] ) ) {
		foreach( $job['uploadedfiles'] as $file ) {
			unlink( $file );
		}
	}
	
	// remove the log file if there is one
	if( !empty( $job['logfile'] ) ) {
		unlink( $job['logfile'] );
	}
	
	if( isset( $job['dir'] ) && is_dir($job['dir']) ) {
		// remove params file if there is one
		$paramFile = $job['dir'].$job['call'].'_params.xml';
		if( file_exists( $paramFile ) ) {
			unlink( $paramFile );
		}
		
		// check for any stray files that may have slipped through the net
		$dir = opendir( $job['dir'] );
		if( $dir !== false ) {
			while( false !== ( $file = readdir($dir) ) ) {
				if( is_file( $job['dir'].$file) ) {
					unlink( $job['dir'].$file );
					echo 'found and removed extra file: '.$file."\r\n";
				}
			}
			closedir( $dir );
			rmdir( $job['dir'] );
		}
	}
	
	return true;
}

?>