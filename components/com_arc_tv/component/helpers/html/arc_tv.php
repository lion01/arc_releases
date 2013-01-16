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
	 * @param string $regex  Optional regex to apply to input
	 * @return string $retVal  The HTML to display the required input
	 */
	function tags( $name, $default = null, $params = array(), $regex = '' )
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
		
		$retVal  = JHTML::_( 'arc.combo', $name, $params, $tagList, 'id', 'word', $oldVal, true, $regex );
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
	 * @param string $regex  Optional regex to apply to input
	 * @return string $retVal  The HTML to display the required input
	 */
	function roles( $name, $default = null, $params = array(), $regex = '' )
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
		
		$retVal  = JHTML::_( 'arc.combo', $name, $params, $roleList, 'id', 'role', $oldVal, true, $regex );
		$retVal .= JHTML::_( 'arc.hidden', 'search_default_'.$name, $default, 'class="search_default"' );
		
		return $retVal;
	}
	
	/**
	 * Generate HTML to display a 5 star rating display / input box
	 * Includes hidden inputs for AJAX submission of user rating
	 * 
	 * @param string $name  Root of div IDs and input names
	 * @param float $global  Current global rating (2 decimal places)
	 * @param int $user  Current user rating (1 -5, integer)
	 * @param string action  Submit action
	 * @return string $retVal  The HTML to display a 5 star rating display / input box
	 */
	function ratings( $name, $global, $user, $action )
	{
		$ratingsImgPath = 'components'.DS.'com_arc_tv'.DS.'helpers'.DS.'images'.DS;
		$ratingsJSPath = 'components'.DS.'com_arc_tv'.DS.'helpers'.DS.'html'.DS;
		JHTML::script( 'ratings.js', $ratingsJSPath, true );
		
		// determine how many stars to show for global average ( 0 - 5 in 0.5 increments)
		if( $global >= ($half = ($ceil = ceil($global))- 0.5) + 0.25 ) {
			$numOfStars = $ceil;
		}
		elseif( $global < $half - 0.25 ) {
			$numOfStars = floor( $global );
		}
		else {
			$numOfStars = $half;
		}
		$pixelsPerStar = 48;
		$bkgOffset = ( $numOfStars * $pixelsPerStar ) * -1;
		
		$retVal =
		'<div id="'.$name.'_div" style="background-image:url(\''.$ratingsImgPath.'rating_stars.png\'); background-position: 0 '.$bkgOffset.'px">'
			.JHTML::_( 'arc.hidden', $name.'_global', $global, 'id="'.$name.'_global"' )
			.JHTML::_( 'arc.hidden', $name.'_user', $user, 'id="'.$name.'_user"' )
			.JHTML::_( 'arc.hidden', $name.'_action', $action, 'id="'.$name.'_action"' )
		.'</div>';
		
		return $retVal;
	}
}
?>