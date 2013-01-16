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
 * A class for handling text fields
 */
class ApothFieldBool extends ApothField
{
	function __construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default )
	{
		parent::__construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, (bool)$value, $default );
	}
	
	/**
	 * Sets the value of the field to true / false instead of "on" / "off"
	 */
	function setValue( $val )
	{
		$this->_value = ($val == 'on');
	}
	
	/**
	 * Displays a checkbox whose state is determined by the value of the field
	 * and if disabled, a hidden input to carry the value through on form submit
	 *
	 * @param $enabled boolean  Optional parameter to determine if the input should be enabled
	 *                          If omitted, the field's pre-set htmlEnabled attribute is used
	 */
	function dataHtml( $enabled = NULL )
	{
		$e = (is_null($enabled) ? $this->htmlEnabled : $enabled);
		return( $e
			? htmlspecialchars($this->prefix).'<input type="checkbox" name="' .$this->_name.'" id="' .$this->_name.'"'.( ($this->_value == true) ? ' checked="checked"' : '').' />'.htmlspecialchars($this->suffix)
			: htmlspecialchars($this->prefix).'<input type="checkbox" name="_'.$this->_name.'" id="_'.$this->_name.'"'.( ($this->_value == true) ? ' checked="checked"' : '').' disabled="disabled" />'.htmlspecialchars($this->suffix)
				."\n".'<input type="hidden" name="'.$this->_name.'" id="'.$this->_name.'" value="'.(($this->_value == true) ? 'on' : 'off').'" />');
	}
	
	/**
	 * Displays a textual representation of the current value
	 */
	function dataPdf()
	{
		return $this->prefix.($this->_value ? 'Yes' : 'No').$this->suffix;
	}
	
	/**
	 * Checks for validity of this field's value
	 * @return mixed  True if is valid, error message if not
	 */
	function validate()
	{
		return ( is_bool($this->_value) ? true : $this->_name.' must be boolean, not "'.$this->_value.'"' );
	}
	
}
?>