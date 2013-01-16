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

// Get constants used by pdf object
require_once( JPATH_SITE.DS.'libraries'.DS.'joomla'.DS.'document'.DS.'apothpdf'.DS.'tcpdf_config.php' );

/**
 * A class for handling short text fields (single line)
 */
class ApothFieldWord extends ApothField
{
	function __construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default )
	{
		parent::__construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default );
		
		// == Set up and pull settings from pdf object ==
		// Pdf object is used for calculating text length and height for the line counter
		$this->_setPdf();
		$this->_lineLen = $r - $l;
		$this->_scaleFactor = $this->_pdf->getScaleFactor();
		$rowPointHeight = K_CELL_HEIGHT_RATIO * 0.3528; // see http://en.wikipedia.org/wiki/Point_(typography) for explanation of this magic number
		$this->_lMax = ( (($this->_bottom - $this->_top) - ($this->getTitleFontSize() * $rowPointHeight )) / ($this->getDataFontSize() * $rowPointHeight ) );
		$this->_lMax = floor($this->_lMax);
		// == End of pdf bit ==
		$this->_dropPdf();
	}
	
	/**
	 * Displays a box for the user to type a short text into.
	 * and if disabled, a hidden input to carry the value through on form submit
	 *
	 * @param $enabled boolean  Optional parameter to determine if the input should be enabled
	 *                          If omitted, the field's pre-set htmlEnabled attribute is used
	 */
	function dataHtml( $enabled = NULL )
	{
		$e = (is_null($enabled) ? $this->htmlEnabled : $enabled);
		return( $e
			? htmlspecialchars($this->prefix).'<input type="text" name="' .$this->_name.'" id="' .$this->_name.'"'.( (empty($this->_value) && !is_numeric($this->_value)) ? '' : ' value="'.htmlspecialchars($this->_value).'"' ).' style="width: %1$s; height: %2$s;" />'.htmlspecialchars($this->suffix)
			: htmlspecialchars($this->prefix).'<input type="text" name="_'.$this->_name.'" id="_'.$this->_name.'"'.( (empty($this->_value) && !is_numeric($this->_value)) ? '' : ' value="'.htmlspecialchars($this->_value).'"' ).' style="width: %1$s; height: %2$s;" disabled="disabled" />'.htmlspecialchars($this->suffix)
				."\n".'<input type="hidden" name="'.$this->_name.'" id="'.$this->_name.'" value="'.htmlspecialchars($this->_value).'" />');
	}
	
	/**
	 * Displays a compact html input for this element
	 * @param $enabled boolean  As for dataHtml
	 * @return string  The html code for the desired input
	 */
	function dataHtmlSmall( $enabled = NULL )
	{
		return sprintf( $this->dataHtml( $enabled ), '10em', '1.5em');
	}
	
	/**
	 * Checks for validity of this field's value
	 * @return mixed  True if is valid, error message if not
	 */
	function validate()
	{
		$this->_setPdf();
		$s = $this->_pdf->getStringWidth($this->_value);
		$retVal = ( ($s <= $this->_lineLen) ? true : $this->_name.' has too much text (width: '.$s.'mm / '.$this->_lineLen.'mm)' );
		$this->_dropPdf();
		return $retVal;
	}
	
	function _dropPdf()
	{
		unset($this->_pdf);
		unset($this->font);
	}
	function _setPdf()
	{
		$this->_pdf = &ApothReportLib::getPDF();
		
		if( !isset($this->fontName) ) {
			$lang = &JFactory::getLanguage();
			$font = $lang->getPdfFontName();
			$this->fontName = ($font) ? $font : 'vera';
		}
		
		$this->_pdf->setFont($this->fontName, '', $this->getDataFontSize());
		$this->font = $this->_pdf->CurrentFont;
	}
}
?>