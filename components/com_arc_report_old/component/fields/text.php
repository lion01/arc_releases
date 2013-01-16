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
 * A class for handling text fields
 */
class ApothFieldText extends ApothField
{
	function __construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default )
	{
		parent::__construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default );
		
		// == Set up and pull settings from pdf object ==
		// Pdf object is used for calculating text length and height for the line counter
		$this->_setPdf();
		$this->_lineLen = ($r - $l) - ($lp + $rp); // **** to match the padding added on output
		$this->_scaleFactor = $this->_pdf->getScaleFactor();
		$rowPointHeight = K_CELL_HEIGHT_RATIO * 0.3528; // see http://en.wikipedia.org/wiki/Point_(typography) for explanation of this magic number
		$tmpTop = $this->_top + $tp;
		$tmpBottom = $this->_bottom - $bp;
		$this->_lMax = ( (($tmpBottom - $tmpTop) - ($this->getTitleFontSize() * $rowPointHeight )) / ($this->getDataFontSize() * $rowPointHeight ) );
		$this->_lMax = floor($this->_lMax);
		// == End of pdf bit ==
		$this->_dropPdf();
	}
	
	/**
	 * Displays the text area for the text input with the current value (if any) displayed
	 * and if disabled, a hidden input to carry the value through on form submit
	 *
	 * @param $enabled boolean  Optional parameter to determine if the input should be enabled
	 *                          If omitted, the field's pre-set htmlEnabled attribute is used
	 */
	function dataHtml( $enabled = NULL )
	{
		$e = (is_null($enabled) ? $this->htmlEnabled : $enabled);
		$b = &$this->getStatementBank();
		return( $e
			? htmlspecialchars($this->prefix).'<textarea name="' .$this->_name.'" id="' .$this->_name.'" style="width: %1$s; height: %2$s;">'.htmlspecialchars($this->_value).'</textarea>'.htmlspecialchars($this->suffix)
			: htmlspecialchars($this->prefix).'<textarea name="_'.$this->_name.'" id="_'.$this->_name.'" style="width: %1$s; height: %2$s;" disabled="disabled">'.htmlspecialchars($this->_value).'</textarea>'.htmlspecialchars($this->suffix)
				."\n".'<input type="hidden" name="'.$this->_name.'" id="'.$this->_name.'" value="'.htmlspecialchars($this->_value).'" />');
	}
	
	/**
	 * Displays a compact html input for this element
	 * @param $enabled boolean  As for dataHtml
	 * @return string  The html code for the desired input
	 */
	function dataHtmlSmall( $enabled = NULL, &$report )
	{
		// auto-pick only if no value already entered
		if( $this->_value == '' ) {
			$b = &$this->getStatementBank();
			$options = $b->getStatements( true );
			foreach($options as $k=>$v) {
				if( $v->keyword != '' ) {
					$options[$k]->text = $v->keyword;
				}
				if( (!empty($v->range_min) || ($v->range_min === 0) || ($v->range_min === '0'))
				 && !empty($v->range_max)
				 && !empty($v->range_of) ) {
					$f = &$report->getField( $v->range_of );
					if( is_object($f)
					 && $f->valueInRange( $v->range_min, $v->range_max )
					 && (($f->getValue() < $v->range_max) || ($f->getValue == 100)) ) {
						$selected[$k] = $k;
						$options[$k]->selected = true;
					}
				}
			}
		}
		else {
			$opt = new stdClass();
			$opt->id = 0;
			$opt->text = 'Already written';
			$options = array($opt);
			$selected = 0;
			$enabled = false;
		}
		if( (is_null($enabled) ? $this->htmlEnabled : $enabled) ) {
			$retVal = '<select name="'.$this->_name.'[]" id="'.$this->_name.'[]" style="width: 10em; height: 8em;" multiple="multiple" style="width: 720px; height: 15em" onclick="'.$onClick.'">';
			if( is_array($options) ) {
				foreach( $options as $k=>$v ) {
					$retVal .= '<option value="'.$v->id.'" style="background: '.htmlspecialchars($v->color).';"'.($v->selected ? ' selected="selected"' : '').'>'.$v->text.'</option>';
				}
			}
			$retVal .= '</select><br />';
		}
		else {
			$retVal = JHTML::_('select.genericList', $options, '_'.$this->_name,      'id="_'.$this->_name.'"   style="width: 10em; height: 5em;" multiple="multiple" disabled="disabled"', 'id', 'text', $selected)
				."\n".'<input type="hidden" name="'.$this->_name.'" id="'.$this->_name.'" value="'.htmlspecialchars($this->_value).'" />';
		}
		return $retVal;
	}
	
	/**
	 * Generates the span which will be used to indicate how many lines have been used
	 */
	function lineCountHtml( $enabled = NULL )
	{
		$e = (is_null($enabled) ? $this->htmlEnabled : $enabled);
		return( $e
			? '<span id="linecount_'.$this->_name.'">0/'.$this->_lMax.'</span>'
			: '' );
	}
	
	function statementPickerHtml( $linkUrl, $imgUrl, $enabled = NULL )
	{
		$e = (is_null($enabled) ? $this->htmlEnabled : $enabled);
		$b = &$this->getStatementBank();
		return( ($e && is_object($b))
			? '<a class="modal" href="'.$linkUrl.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><img src="'.$imgUrl.'" alt="Statement Picker" /></a>'
			: '' );
	}
	
	/**
	 * Returns the maximum number of lines allowed in this text box.
	 * The number is worked out based on the box height,
	 * the title font size and the data font size
	 *
	 * *** WARNING
	 * *** This value assumes that the title is on its own line with no additional clearance
	 */
	function getLineMax()
	{
		return $this->_lMax;
	}
	function getLineLength()
	{
		return $this->_lineLen;
	}
	function getFontScale()
	{
		return $this->_scaleFactor;
	}
	function getFontName()
	{
		return $this->fontName;
	}
	function getFontWidths()
	{
		$this->_setPdf();
		$retVal = $this->font['cw'];
		$this->_dropPdf();
		return $retVal;
	}
	
	/**
	 * Checks for validity of this field's value
	 * Achieves this by performing most of the same operations as the pdf output template
	 * then checking to see if the y position is below the bottom of the box.
	 *
	 * *** I'd like to come back and tidy this up, but not sure if I'll get time
	 * @return mixed  True if is valid, error message if not
	 */
	function validate()
	{
		$this->_setPdf();
		$valueSize = $this->getDataFontSize();
		if( !$this->valueAsTitle ) {
			$this->_pdf->setFont($this->fontName, '', $valueSize);
		}
		$lCount = $this->_countLines();
		$retVal = ( ($lCount > $this->_lMax) ? $this->_name.' is too long (at '.$lCount.' lines out of '.$this->_lMax.')' : true );
		
		$this->_dropPdf();
		return $retVal;
	}
	
	function _countLines()
	{
		$bullet = ApothReportLib::getBulletText();
		$lineLen = $this->_lineLen;
		
		$l = 0;
		$w = $lineLen;
		$word = 0;
		$str = str_replace( array("\r\n", "\r"), "\n", $this->dataPdf() );
		$chars = $this->_pdf->UTF8StringToArray( $str );
		$pChar = 0;
		
		foreach( $chars as $i=>$myChar ) {
			$cw = $this->_pdf->GetCharWidth($myChar);
//			$myCharTxt = html_entity_decode('&#'.$myChar.';',ENT_NOQUOTES,'UTF-8');
			$myCharTxt = chr($myChar);
			//var_dump_pre($myCharTxt, 'char');
			
			if( $myCharTxt == "\n" ) {
				//echo $this->_name.' newline<br />';
				// only add a new line if we're not going to leave one empty
				// (remember we use PREG_SPLIT_NO_EMPTY in pdf_field.php which does the final pdf output)
				$w = $w + $word;
				$word = 0;
				if( $w > 0 ) {
					$l = $l + 1;
					$w = 0;
				}
			}
			else if( preg_match( '/\\W/', $myCharTxt ) ) {
				//echo 'word boundary ('.var_dump_pre($myChar, true).'). adding word to line<br />';
				$word = $word + $cw;
				if( (($w + $word) > $lineLen) ) {
					$l = $l + 1;
					$w = 0;
				}
				else {
					$w = $w + $word;
				}
				$word = 0;
			}
			else {
				$word = $word + $cw;
				//echo 'character('.var_dump_pre($myChar, true).'). adding '.$cw.' to word length ('.$w.').<br />';
				if( ($w + $word) > $lineLen ) {
					$l = $l + 1;
					$w = 0;
				}
			}
		}
		
		return $l;
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