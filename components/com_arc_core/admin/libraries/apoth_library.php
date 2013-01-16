<?php
/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Give us access to the core admin libraries and core admin controller
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'apoth_library_shared.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'arc_admin_controller.php' );
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'libraries'.DS.'arc_admin_model.php' );

// Path the helpers
JHTML::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_core'.DS.'helpers'.DS.'html' );
JHTML::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_arc_course'.DS.'helpers'.DS.'html' );

class ApotheosisLib extends ApotheosisLibParent
{
	function getComId($comName = 'com_arc_core')
	{
		$db = &JFactory::getDBO();
		$db->setQuery( "SELECT `id` FROM #__components"
			. "\n".' WHERE `option`="'.$comName.'"'
			. "\n".' AND `link` != ""' );
		$result = $db->loadObject();
		return $result->id;
	}
	
	function getMenuModId()
	{
		if (!isset($_menuModId)) {
			$db = &JFactory::getDBO();
			$db->setQuery( 'SELECT `id` FROM #__modules'
				."\n".'WHERE `module` LIKE "mod_arc_menu%"');
			$result = $db->loadObject();
			static $_menuModId = 0;
			$_menuModId = $result->id;
		}
		return $_menuModId;
	}
	
	/*
	 * A function to create the serial number (section before luhn number) 
	 * for the person id. Passed date must be in the format YYYY-mm-dd
	 */
	function serialNum( $dob = '' )
	{
		$db = &JFactory::getDBO();
		$db->BeginTrans();
		$db->setQuery('SELECT * FROM #__apoth_ppl_date_series WHERE `date` = "'.$dob.'"');
		$number = $db->loadObject('date');
		
		$serialLen = (($dob == '0000-00-00') ? 8 : 4);
		$num = (is_null($number) ? '1' : $number->number + 1);
		$serialNum = str_pad( $num, $serialLen, '0', STR_PAD_LEFT );
		
		if(is_null($number)) {
			$db->setQuery('INSERT INTO #__apoth_ppl_date_series (`date`, `number`) VALUES ("'.$dob.'", "'.$num.'")');
			$db->query();	
		}
		else {
			$db->setQuery('UPDATE #__apoth_ppl_date_series SET `number` = "'.$num.'" WHERE `date` = "'.$dob.'"');
			$db->query();
		}
		
		$db->CommitTrans();
		return $serialNum;
	}
}
?>
