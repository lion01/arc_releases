<?php
/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_SITE.DS.'components'.DS.'com_arc_people'.DS.'models'.DS.'objects.php' );

/**
 * Data Access Helper
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage Attendance
 * @since 0.1
 */
class ArcApiRead_people extends ArcApiRead
{
	function person()
	{
		$u = ApotheosisLib::getUser();
		
		$retVal = array();
		$retVal['person_id'] = $u->person_id;
		$retVal['test'] = array('foo'=>'bar', 'quagly'=>'baz');
		
		return $retVal;
	}
}
?>
