<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_COMPONENT.DS.'models'.DS.'object_field.php');

/**
 * Include all the field handling files
 */
$dirName = JPATH_COMPONENT.DS.'fields';
if( $dir = opendir($dirName) ) {
	while( ($file = readdir($dir)) !== false ) {
		if( is_file($dirName.DS.$file) && (substr($file, -4) == '.php') ) {
			include( $dirName.DS.$file );
		}
	}
	closedir($dir);
}

require_once( JPATH_COMPONENT.DS.'models'.DS.'object_report.php');
require_once( JPATH_COMPONENT.DS.'models'.DS.'object_statement_bank.php');

?>