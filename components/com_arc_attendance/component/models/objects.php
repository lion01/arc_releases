<?php
/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * All this file is for is to include all the required object files
 */
require_once( JPATH_COMPONENT.DS.'models'.DS.'object_course.php' );
require_once( JPATH_COMPONENT.DS.'models'.DS.'object_register.php' );
require_once( JPATH_COMPONENT.DS.'models'.DS.'object_mark.php' );
require_once( JPATH_COMPONENT.DS.'models'.DS.'object_mark_factory.php' );
require_once( JPATH_COMPONENT.DS.'models'.DS.'object_mark_sheet.php' );
require_once( JPATH_COMPONENT.DS.'models'.DS.'object_mark_sheet_summary.php' );
require_once( JPATH_COMPONENT.DS.'models'.DS.'object_mark_sheet_full.php' );
?>