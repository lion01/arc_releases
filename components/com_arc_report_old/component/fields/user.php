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

/**
 * A class for handling user list fields
 *
 * @param string $extraWhere  A string to add to the WHERE clause of the query which gets all the users
 */
class ApothFieldUser extends ApothField
{
	function __construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default, $cycle, $extraWhere = false )
	{
		parent::__construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default );
		
		// *** Should probably become a list of only valid users at their level or below
		// *** Can't make getUserList any faster though, so this will be a bottleneck :-(
		$this->_where = 'WHERE gm.is_teacher = 1' // *** titikaka
			."\n".'  AND '.ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $cycle->valid_from, $cycle->valid_to )
			.( $extraWhere === false ? '' : "\n".$extraWhere );
		$this->_options = &ApotheosisLib::getUserList( $this->_where, false, 'teacher' );
	}
	
	/**
	 * Displays a select box for the user to select their name from.
	 *
	 * @param $enabled boolean  Optional parameter to determine if the input should be enabled
	 *                          If omitted, the field's pre-set htmlEnabled attribute is used
	 */
	function dataHtml( $enabled = NULL )
	{
		$e = (is_null($enabled) ? $this->htmlEnabled : $enabled);
		return( $e
			? htmlspecialchars($this->prefix).JHTML::_('select.genericList', $this->_options,     $this->_name, 'id="' .$this->_name.'" style="width: %1$s; height: %2$s;"', 'id', 'displayname', (($this->_value != '') ? $this->_value : $this->_default)).htmlspecialchars($this->suffix)
			: htmlspecialchars($this->prefix).JHTML::_('select.genericList', $this->_options, '_'.$this->_name, 'id="_'.$this->_name.'" style="width: %1$s; height: %2$s;" disabled="disabled"', 'id', 'displayname', (($this->_value != '') ? $this->_value : $this->_default)).htmlspecialchars($this->suffix)
				."\n".'<input type="hidden" name="'.$this->_name.'" id="'.$this->_name.'" value="'.htmlspecialchars($this->_value).'" />');
	}
	
	/**
	 * Displays a compact html input for this element
	 * @param $enabled boolean  As for dataHtml
	 * @return string  The html code for the desired input
	 */
	function dataHtmlSmall( $enabled = NULL )
	{
		return sprintf( $this->dataHtml( $enabled ), '15em', '1.5em');
	}
	
	/**
	 * Displays the text of the currently selected user
	 */
	function dataPdf()
	{
		$name = ( isset($this->_options[$this->_value]) ? $this->_options[$this->_value]->displayname : '' );
		return $this->prefix.$name.$this->suffix;
	}
	
	/**
	 * Accessor method to set the value of this field.
	 * If a value other than one of those available is supplied, NULL is set as the value
	 */
	function setValue( $val )
	{
		$this->_value = ( is_null($val) ? '' : $val );
	}
	
	/**
	 * Checks for validity of this field's value
	 * @return mixed  True if is valid, error message if not
	 */
	function validate()
	{
		return ( array_key_exists($this->_value, $this->_options) ? true : $this->_name.' must be user id (the option value, not the displayed text) from the list, not "'.$this->_value.'"' );
	}
	
	// memory saving with sleep and wakeup
	function __sleep()
	{
		unset($this->_options);
		parent::__sleep();
		return( array_keys(get_object_vars($this)) );
	}
	function __wakeup()
	{
		parent::__wakeup();
		$this->_options = &ApotheosisLib::getUserList( $this->_where, false, 'teacher' );
	}
}
?>