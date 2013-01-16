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
 * A class for handling list fields
 * with pre-defined contents
 */
class ApothFieldList extends ApothField
{
	/**
	 * Much the same as the other field constructors, but with the addition of one parameter.
	 * The $style param can be either a string (bands, levels, grades) indicating the pre-defined
	 * list to use, or it can be an array of options represented as objects with value and text properties
	 *
	 * @param $style mixed  The style of pre-defined list to use, or an array of options
	 */
	function __construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default, $style )
	{
		parent::__construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default );
		$this->_style = $style;
		
		switch($style) {
		case('bands'):
			$this->_options['']->value  = '';  $this->_options['']->text  = '';
			$this->_options['D']->value = 'D'; $this->_options['D']->text = 'Distinction';
			$this->_options['M']->value = 'M'; $this->_options['M']->text = 'Merit';
			$this->_options['C']->value = 'C'; $this->_options['C']->text = 'Credit';
			$this->_options['P']->value = 'P'; $this->_options['P']->text = 'Pass';
			$this->_options['U']->value = 'U'; $this->_options['U']->text = 'Ungraded';
			break;
		
		case('levels'):
			$this->_options['']->value    = '';    $this->_options['']->text    = '';
			$this->_options['8.8']->value = '8.8'; $this->_options['8.8']->text = '8.8';
			$this->_options['8.5']->value = '8.5'; $this->_options['8.5']->text = '8.5';
			$this->_options['8.2']->value = '8.2'; $this->_options['8.2']->text = '8.2';
			$this->_options['7.8']->value = '7.8'; $this->_options['7.8']->text = '7.8';
			$this->_options['7.5']->value = '7.5'; $this->_options['7.5']->text = '7.5';
			$this->_options['7.2']->value = '7.2'; $this->_options['7.2']->text = '7.2';
			$this->_options['6.8']->value = '6.8'; $this->_options['6.8']->text = '6.8';
			$this->_options['6.5']->value = '6.5'; $this->_options['6.5']->text = '6.5';
			$this->_options['6.2']->value = '6.2'; $this->_options['6.2']->text = '6.2';
			$this->_options['5.8']->value = '5.8'; $this->_options['5.8']->text = '5.8';
			$this->_options['5.5']->value = '5.5'; $this->_options['5.5']->text = '5.5';
			$this->_options['5.2']->value = '5.2'; $this->_options['5.2']->text = '5.2';
			$this->_options['4.8']->value = '4.8'; $this->_options['4.8']->text = '4.8';
			$this->_options['4.5']->value = '4.5'; $this->_options['4.5']->text = '4.5';
			$this->_options['4.2']->value = '4.2'; $this->_options['4.2']->text = '4.2';
			$this->_options['3.8']->value = '3.8'; $this->_options['3.8']->text = '3.8';
			$this->_options['3.5']->value = '3.5'; $this->_options['3.5']->text = '3.5';
			$this->_options['3.2']->value = '3.2'; $this->_options['3.2']->text = '3.2';
			$this->_options['2.8']->value = '2.8'; $this->_options['2.8']->text = '2.8';
			$this->_options['2.5']->value = '2.5'; $this->_options['2.5']->text = '2.5';
			$this->_options['2.2']->value = '2.2'; $this->_options['2.2']->text = '2.2';
			$this->_options['1.8']->value = '1.8'; $this->_options['1.8']->text = '1.8';
			$this->_options['1.5']->value = '1.5'; $this->_options['1.5']->text = '1.5';
			$this->_options['1.2']->value = '1.2'; $this->_options['1.2']->text = '1.2';
			$this->_options['0.8']->value = '0.8'; $this->_options['0.8']->text = '0.8';
			$this->_options['0.5']->value = '0.5'; $this->_options['0.5']->text = '0.5';
			$this->_options['0.2']->value = '0.2'; $this->_options['0.2']->text = '0.2';
			break;
		
		case('grades'):
			$this->_options['']->value   = '';   $this->_options['']->text   = '';
			$this->_options['A*']->value = 'A*'; $this->_options['A*']->text = 'A*';
			$this->_options['A']->value  = 'A';  $this->_options['A']->text  = 'A';
			$this->_options['B']->value  = 'B';  $this->_options['B']->text  = 'B';
			$this->_options['C']->value  = 'C';  $this->_options['C']->text  = 'C';
			$this->_options['D']->value  = 'D';  $this->_options['D']->text  = 'D';
			$this->_options['E']->value  = 'E';  $this->_options['E']->text  = 'E';
			$this->_options['F']->value  = 'F';  $this->_options['F']->text  = 'F';
			$this->_options['G']->value  = 'G';  $this->_options['G']->text  = 'G';
			$this->_options['U']->value  = 'U';  $this->_options['U']->text  = 'U';
			break;
		
		default:
			if( is_array($style) ) {
				foreach( $style as $opt ) {
					$this->_options[$opt->value] = $opt;
				}
			}
			else {
				$this->_options['']->value = '';
				$this->_options['']->text  = '';
			}
		}
		
	}

	
	/**
	 * Displays the select list of options for the input with the current value (if any) selected
	 *
	 * @param $enabled boolean  Optional parameter to determine if the input should be enabled
	 *                          If omitted, the field's pre-set htmlEnabled attribute is used
	 */
	function dataHtml( $enabled = NULL )
	{
		$e = (is_null($enabled) ? $this->htmlEnabled : $enabled);
		return( $e
			? htmlspecialchars($this->prefix).JHTML::_('select.genericList', $this->_options,     $this->_name, 'id="' .$this->_name.'" style="width: %1$s; height: %2$s;"', 'value', 'text', $this->_value).htmlspecialchars($this->suffix)
			: htmlspecialchars($this->prefix).JHTML::_('select.genericList', $this->_options, '_'.$this->_name, 'id="_'.$this->_name.'" style="width: %1$s; height: %2$s;" disabled="disabled"', 'value', 'text', $this->_value).htmlspecialchars($this->suffix)
				."\n".'<input type="hidden" name="'.$this->_name.'" id="'.$this->_name.'" value="'.htmlspecialchars($this->_value).'" />');
	}
	
	/**
	 * Displays a compact html input for this element
	 * @param $enabled boolean  As for dataHtml
	 * @return string  The html code for the desired input
	 */
	function dataHtmlSmall( $enabled = NULL )
	{
		return sprintf( $this->dataHtml( $enabled ), '4em', '1.5em');
	}
	
	/**
	 * Displays the text of the currently selected value
	 */
	function dataPdf()
	{
		return $this->prefix.$this->_options[$this->_value]->text.$this->suffix;
	}
	
	/**
	 * Checks for validity of this field's value
	 * @return mixed  True if is valid, error message if not
	 */
	function validate()
	{
		return ( array_key_exists($this->_value, $this->_options) ? true : $this->_name.' must be a value from the list, not "'.$this->_value.'"' );
	}
	
	/**
	 * Determines if the current value of the field is within the given range (inlcuding boundaries)
	 * In the context of a list, being within the boundaries means being between those elements in
	 * the list. Earlier in the list is "higher", later is "lower" (eg A* appears first, and is top grade)
	 *
	 * @param $lBound mixed  The lower boundary
	 * @param $uBound mixed  The upper boundary
	 * @return boolean  True if the value is within the boundaries, false otherwise
	 */
	function valueInRange( $lBound, $uBound )
	{
		$found = false;
		$pastUpper = false;
		$pastLower = false;
		$opt = reset($this->_options);
		do {
			if( $opt->value == $uBound ) {
				$pastUpper = true;
			}
			if( $opt->value = $this->_value ) {
				$found = true;
				break;
			}
			if( $opt->value = $lBound ) {
				$pastLower = true;
			}
			$opt = next($this->_options);
		} while( ($opt !== false) && ($pastLower === false) );
		
		$foundActual = $found && $pastUpper && !$pastLower;
		
		// we have found a numeric match if we have all numbers and our value is between bounds
		$foundNumeric = ( ( is_numeric($this->_value) && is_numeric($lBound) && is_numeric($uBound) )
		               && ( (($uBound >= $this->_value) && ($this->_value >= $lBound))
		                 || (($uBound <= $this->_value) && ($this->_value <= $lBound)) ) );
		
		return ( $foundActual || $foundNumeric );
	}
	
	function getStyle()
	{
		return $this->_style;
	}
}
?>