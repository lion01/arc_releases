<?php
/**
 * @package     Arc
 * @subpackage  Planner
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'installation.php' );

function com_install()
{
	$com = 'com_arc_planner';
	apoth_cleanup_install( $com );
}
?>