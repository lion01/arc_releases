<?php
/**
 * @package     Arc
 * @subpackage  Staff
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Contact
*/
class HTML_Staffmanager
{
	function showComponents()
	{
		global $mainframe;

		// Initialize variables
		$db		= &JFactory::getDBO();
		$user	= &ApotheosisLib::getUser();

		//Ordering allowed ?
		$ordering = ($lists['order'] == 'section_name' && $lists['order_Dir'] == 'ASC');
		
		?>
		<div style="background: #99ff99; width: 50%; border: solid 1px blue;">Here's all the components currently installed that work with the staff manager.</div>
		<?php
	}
}
?>
