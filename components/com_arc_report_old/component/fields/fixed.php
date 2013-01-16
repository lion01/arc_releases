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
 * A class for handling fixed text fields
 */
class ApothFieldFixed extends ApothField
{
	function __construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default )
	{
		parent::__construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default );
		$this->htmlEnabled = false;
		$this->htmlSmallEnabled = false;
	}
	
	/**
	 * Displays the value of the input (in a text box if enabled)
	 * or as text in a div with a hidden input to carry the value through on form submit if not enabled
	 *
	 * @param $enabled boolean  Optional parameter to determine if the input should be enabled
	 *                          If omitted, the field's pre-set htmlEnabled attribute is used
	 */
	function dataHtml( $enabled = NULL )
	{
		$e = (is_null($enabled) ? $this->htmlEnabled : $enabled);
		return( $e
			? htmlspecialchars($this->prefix).'<input type="text" name="' .$this->_name.'" id="' .$this->_name.'" style="width: %1$s; height: %2$s;" value="'.htmlspecialchars($this->_value).'" />'.htmlspecialchars($this->suffix)
			: '<div style="width: %1$s; height: %2$s;">'.htmlspecialchars($this->prefix.$this->_value.$this->suffix).'</div>'
			."\n".'<input type="hidden" name="'.$this->_name.'" id="'.$this->_name.'" value="'.htmlspecialchars($this->_value).'">');
	}
}
?>