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

/**
 * Utility class for generating arc specific HTML entities
 *
 * @static
 * @package    Arc
 * @subpackage HTML
 * @since      1.5
 */
class JHTMLAdmin_Arc
{
	/**
	 * Shows a list of all the roles
	 * 
	 * @param string $name  The name to use for the input
	 * @param mixed $default  The default value to have selected on form display
	 * @param boolean $multiple  Allow multiple selections?
	 * @return string  The HTML to display the required input
	 */
	function roleList( $name, $default = null, $multiple = false )
	{
		$default  = ( is_null($default)  ? ''   : $default );
		$oldVal   = JRequest::getVar($name, $default);
		
		$db = &JFactory::getDBO();
		$query = 'SELECT r1.id, CONCAT_WS("-", r4.role, r3.role, r2.role, r1.role) AS name'
			."\n".'FROM `jos_apoth_sys_roles` AS r1'
			."\n".'LEFT JOIN `jos_apoth_sys_roles` AS r2'
			."\n".'  ON r2.id = r1.parent'
			."\n".' AND r2.id != r2.parent'
			."\n".'LEFT JOIN `jos_apoth_sys_roles` AS r3'
			."\n".'  ON r3.id = r2.parent'
			."\n".' AND r3.id != r3.parent'
			."\n".'LEFT JOIN `jos_apoth_sys_roles` AS r4'
			."\n".'  ON r4.id = r3.parent'
			."\n".' AND r4.id != r4.parent'
			."\n".'ORDER BY `name`';
		$db->setQuery( $query );
		$options = $db->loadObjectList();
		
		$attribs = ($multiple ? 'multiple="multiple" style="height:15em"' : '');
		$name = ($multiple ? $name.'[]' : $name);
		$retVal = JHTML::_('select.genericList', $options, $name, $attribs , 'id', 'name', $oldVal);
		return $retVal;
	}
}
?>
