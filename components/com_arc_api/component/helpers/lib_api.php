<?php
/**
 * @package     Arc
 * @subpackage  API
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class ArcApi extends jObject
{
	function _( $ident, $params )
	{
		static $cache = array();
		
		$parts = explode( '.', $ident, 3 );
		$method = ( empty($parts) ? false : strtolower( array_shift($parts) ) );
		$cName  = ( empty($parts) ? false : strtolower( array_shift($parts) ) );
		$fName  = ( empty($parts) ? false : array_shift($parts) );
		
//		var_dump_pre($method, 'method');
//		var_dump_pre($cName, 'cName');
//		var_dump_pre($fName, 'fName');
//		var_dump_pre($params, 'params');
		
		if( empty($method) || empty($cName) || empty($fName) ) {
			return null;
		}
		
		$key = $method.'.'.$cName;
		
		$retVal = false;
		if( !isset($cache[$key]) ) {
			$fileName = JPATH_SITE.DS.'components'.DS.'com_arc_'.$cName.DS.'helpers'.DS.'api.php';
			if( file_exists($fileName) ) {
				require_once($fileName);
			}
			$cNameFull = 'ArcApi'.ucfirst($method).'_'.ucfirst($cName);
			if( class_exists($cNameFull) ) {
				$cache[$key] = new $cNameFull();
			}
			
			if( !isset($cache[$key]) ) {
				$cache[$key] = false;
			}
		}
		
		if( is_a($cache[$key], 'ArcApi'.ucfirst($method)) && method_exists($cache[$key], $fName) ) {
			$retVal = call_user_func_array( array($cache[$key], $fName), $params );
		}
		else {
			$retVal = null;
		}
		
		return $retVal;
	}
	
}

class ArcApiRead extends ArcApi
{
	
}

class ArcApiWrite extends ArcApi
{
	
}
?>