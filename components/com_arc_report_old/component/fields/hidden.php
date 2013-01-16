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
 * A class for handling hidden fields
 */
class ApothFieldHidden extends ApothField
{
	function __construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default )
	{
		parent::__construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default );
		$this->_showInPdf = false;
		$this->htmlEnabled = false;
		$this->htmlSmallEnabled = false;
	}
	
	function titleHtml()
	{
		return '';
	}
	/**
	 * Generates a hidden input to carry the value through on form submit
	 *
	 * @param $enabled boolean  Optional parameter to determine if the input should be enabled
	 *                          Has no effect on hidden inputs, but included for consistency
	 */
	function dataHtml( $enabled = NULL )
	{
		return '<input type="hidden" name="'.$this->_name.'" id="'.$this->_name.'" value="'.htmlspecialchars($this->_value).'" />';
	}
}
?>