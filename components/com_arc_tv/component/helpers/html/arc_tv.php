<?php
/**
 * @package     Arc
 * @subpackage  Tv
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
class JHTMLArc_Tv
{
	/**
	 * Generate HTML to display a combo-box video tag selector with the given name
	 * Will also add new tags by invoking combo_add extension
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value (used for form reset)
	 * @param array $params  Optional input properties
	 * @return string $retVal  The HTML to display the required input
	 */
	function tags( $name, $default = null, $params = array() )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		$params['multiple'] = 'multiple';
		
		$fVideo = &ApothFactory::_( 'tv.video' );
		$tags = $fVideo->getAllTags();
		$tagList = array();
		foreach( $tags as $tag ) {
			$tagList[] = (object)$tag;
		}
		
		$retVal  = JHTML::_( 'arc.combo', $name, $params, $tagList, 'id', 'word', $oldVal, true );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a combo-box video role selector with the given name
	 * Will also add new roles by invoking combo_add extension
	 *
	 * @param string $name  The name to use for the input
	 * @param string $default  Default input value (used for form reset)
	 * @param array $params  Optional input properties
	 * @return string $retVal  The HTML to display the required input
	 */
	function roles( $name, $default = null, $params = array() )
	{
		$default = ( !is_null($default) ? $default : '' );
		$oldVal = JRequest::getVar( $name, $default );
		$params['multiple'] = 'multiple';
		
		$fVideo = &ApothFactory::_( 'tv.video' );
		$roles = $fVideo->getAllRoles();
		$roleList = array();
		foreach( $roles as $role ) {
			$roleList[] = (object)$role;
		}
		
		$retVal  = JHTML::_( 'arc.combo', $name, $params, $roleList, 'id', 'role', $oldVal, true );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
}
?>