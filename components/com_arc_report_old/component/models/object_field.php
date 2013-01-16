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
 * Apoth Field abstract class
 * Defines the common features of fields that get used on reports
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ApothField extends JObject
{
	/** @var string  The unique name of the field to be used in html forms
	 * The index of this field in the _fields array of its containing object must be the same as its _name */
	var $_name;
	
	/** @var string  The column in the reports table that this field maps to.
	 *  If the field is not stored in the db, then false of an empty string should be set here */
	var $_column;
	
	/** @var string  Any recorded value to be used and displayed in html form */
	var $_value;
	
	/** @var string  Any default value to be used and displayed in html form */
	var $_default;
	
	/** @var number  The x coordinate of the left of the box when displaying for print */
	var $_left;
	/** @var number  The y coordinate of the top of the box when displaying for print */
	var $_top;
	/** @var number  The x coordinate of the right of the box when displaying for print */
	var $_right;
	/** @var number  The y coordinate of the bottom of the box when displaying for print */
	var $_bottom;
	
	/** @var boolean  Is this field required when filling in the input form? */
	var $_required = 0;
	
	/**
	 * Set of variables for determining how the field should be displayed in the html form
	 */
	var $_htmlWidth;
	var $_htmlHeight;
	var $_leftPadding;
	var $_rightPadding;
	var $_topPadding;
	var $_bottomPadding;
	var $htmlEnabled = true;
	var $htmlSmallEnabled = true;
	
	/** @var string  Title text to be displayed in html forms and for print */
	var $_title;
	
	/** @var object  Reference to the statement bank that is used for this field (if any) */
	var $_statementBank;
	var $_hasStatementBank = false;
	
	/** @var boolean  Should the field be shown on the generated pdf? */
	var $_showInPdf = true;
	
	// publicly tweakable variables (mainly for pdf generation)
	/** @var */
	var $prefix = '';
	var $suffix = '';
	var $titleAlign = 'L';
	var $dataAlign = 'L';
	var $hasBorder = true;
	/** @var indicates the required clearance after the title. 0=same line, -1=new line, other number=mm from current Y */
	var $titleClearance = -1;
	var $titleMarginTop = 0;
	var $showTitle = true;
	var $ownBox = true;
	var $valueAsTitle = false;

	function __construct( $rpt, $name, $column, $l, $t, $r, $b, $lp, $rp, $tp, $bp, $hw, $hh, $title, $value, $default )
	{
		parent::__construct();
		
		if( is_object($rpt) ) {
			$style = $rpt->getStyle('fields');
			$cycle = $rpt->getCycle();
			$this->_style->cycle = $cycle;
		}
		
		if( isset($style[$name]) ) {
			$ps = $rpt->getStyle( 'page_style' );
			
			$this->_style = clone( $style[$name] );
			$this->_style->group = $rpt->getGroup();
			$this->_style->template = $ps;
			$this->_style->field = $name;
		}
		else {
			$this->_style = new stdClass();
			$this->_style->lookup_id = null;
			$this->_style->lookup_type = null;
		}
			
		$this->_name            = $name;
		$this->_column          = $column;
		$this->_left            = ( ( !isset($this->_style->left          ) || is_null($this->_style->left          ) ) ? $l        : $this->_style->left );
		$this->_top             = ( ( !isset($this->_style->top           ) || is_null($this->_style->top           ) ) ? $t        : $this->_style->top );
		$this->_right           = ( ( !isset($this->_style->right         ) || is_null($this->_style->right         ) ) ? $r        : $this->_style->right );
		$this->_bottom          = ( ( !isset($this->_style->bottom        ) || is_null($this->_style->bottom        ) ) ? $b        : $this->_style->bottom );
		$this->_leftPadding     = ( ( !isset($this->_style->leftPadding   ) || is_null($this->_style->leftPadding   ) ) ? $lp       : $this->_style->leftPadding );
		$this->_rightPadding    = ( ( !isset($this->_style->rightPadding  ) || is_null($this->_style->rightPadding  ) ) ? $rp       : $this->_style->rightPadding );
		$this->_topPadding      = ( ( !isset($this->_style->topPadding    ) || is_null($this->_style->topPadding    ) ) ? $tp       : $this->_style->topPadding );
		$this->_bottomPadding   = ( ( !isset($this->_style->bottomPadding ) || is_null($this->_style->bottomPadding ) ) ? $bp       : $this->_style->bottomPadding );
		$this->_htmlWidth       = ( ( !isset($this->_style->htmlwidth     ) || is_null($this->_style->htmlwidth     ) ) ? $hw       : $this->_style->htmlwidth );
		$this->_htmlHeight      = ( ( !isset($this->_style->htmlheight    ) || is_null($this->_style->htmlheight    ) ) ? $hh       : $this->_style->htmlheight );
		$this->_title           = ( ( !isset($this->_style->title         ) || is_null($this->_style->title         ) ) ? $title    : $this->_style->title );
		$this->_default         = ( ( !isset($this->_style->default       ) || is_null($this->_style->default       ) ) ? $default  : $this->_style->default );
		
		$this->_lookupParams = array();
		
		$this->setValue( $value );
	}
	
	function setStyle( $data, $template )
	{
		if( is_null($this->_style) ) {
			$errors[] = 'Field '.$this->_name.' not associated with a legitimate report. Could not update.';
		}
		else {
			$db = &JFactory::getDBO();
			$changes = array();
			foreach( $data as $k=>$v ) {
				if( $v == '' ) {
					$v = null;
				}
				$dbV = (is_null($v) ? 'NULL' : $db->Quote($v) );
				switch( $k ) {
				case( 'title' ):
					if( $v != ((!isset($this->_style->title) || is_null($this->_style->title)) ? $this->_title : $this->_style->title) ) {
						$changes[] = $db->nameQuote( 'title' ).' = '.$dbV;
						$this->_style->title = $this->_title = $v;
					}
					break;
				
				case( 'lookup_id' ):
					$v = explode( ';', $v );
					if( $v != $this->_style->lookup_id ) {
						$changes[] = $db->nameQuote( 'lookup_id' ).' = '.$dbV;
						$this->_style->lookup_id = $v;
					}
					break;
				
				default:
					if( $v != $this->_style->$k ) {
						$changes[] = $db->nameQuote( $k ).' = '.$dbV;
						$this->_style->$k = $v;
					}
					break;
				}
			}
			
			// write any changes back to the db
			if( !empty($changes) ) {
				$sql = 'UPDATE #__apoth_rpt_style_fields'
					."\n".' SET '.implode( "\n".'   , ', $changes )
					."\n".' WHERE '.$db->nameQuote('cycle')      .' = '.$db->Quote($this->_style->cycle)
					."\n".'   AND '.$db->nameQuote('group')      .' = '.$db->Quote($this->_style->group)
					."\n".'   AND '.$db->nameQuote('template')   .' = '.$db->Quote($template)
					."\n".'   AND '.$db->nameQuote('field')      .' = '.$db->Quote($this->_style->field);
				$db->setQuery( $sql );
				$r = $db->Query();
				
				if( $db->getAffectedRows() == 0 ) {
					$sql = 'INSERT IGNORE INTO #__apoth_rpt_style_fields'
						."\n".' SET '.$db->nameQuote('cycle')      .' = '.$db->Quote($this->_style->cycle)
						."\n".'   , '.$db->nameQuote('group')      .' = '.$db->Quote($this->_style->group)
						."\n".'   , '.$db->nameQuote('template')   .' = '.$db->Quote($template)
						."\n".'   , '.$db->nameQuote('field')      .' = '.$db->Quote($this->_style->field)
						."\n".'   , '.implode( "\n".'   , ', $changes );
					$db->setQuery( $sql );
					$r = $db->Query();
				}
			}
		}
		return( empty($errors) ? true : $errors );
	}
	
	/**
	 * Accessor method to retrieve the name of this field
	 */
	function getName()
	{
		return $this->_name;
	}
	
	/**
	 * Accessor method to retrieve the column name where this field's data is stored
	 * For fields not stored in the db, this will return false
	 */
	function getColumn()
	{
		return ( (($this->_column === false) || ($this->_column === '') || is_null($this->_column)) ? false : $this->_column );
	}
	
	/**
	 * Accessor method to retrieve the value of this field.
	 * Child classes should impliment any validation which may be appropriate
	 */
	function getValue()
	{
		return $this->_value;
	}
	
	/**
	 * Accessor method to set the value of this field.
	 * Child classes should impliment any validation which may be appropriate
	 */
	function setValue( $val )
	{
		$this->_value = $val;
	}
	
	/**
	 * Determines if the current value of the field is within the given range (inlcuding boundaries)
	 *
	 * @param $lBound mixed  The lower boundary
	 * @param $uBound mixed  The upper boundary
	 * @return boolean  True if the value is within the boundaries, false otherwise
	 */
	function valueInRange( $lBound, $uBound )
	{
		return ( ($this->_value >= $lBound) && ($this->_value <= $uBound) );
	}
	
	/**
	 * Sets the required flag to the given value if that value is boolean
	 * @param $val boolean  The desired value of the "required" flag
	 */
	function setRequired( $val )
	{
		if( is_bool($val) ) {
			$this->_required = $val;
		}
	}
	/**
	 * Retrieves the value of the "required" flag
	 */
	function getRequired()
	{
		return $this->_required;
	}
	
	function validate()
	{
		return true;
	}
	
	/**
	 * Assigns a statement bank object to this field by reference
	 * ... actually just sets the cycle and group ids so we can create a statement bank if / when we need it
	 */
	function setStatementBank( $cycle, $groupId  )
	{
		$this->_cycle = $cycle;
		$this->_group = $groupId;
		$this->_hasStatementBank = true;
	}
	
	function &getStatementBank()
	{
		if( $this->_hasStatementBank && !is_object($this->_statementBank) ) {
			$this->_statementBank = &ApothStatementBank::getBank( $this->_cycle, $this->_group, $this->_name );
		}
		return $this->_statementBank;
	}
	
	function hasStatementBank()
	{
		return $this->_hasStatementBank;
	}
	
	function getTitle()
	{
		return $this->_title;
	}
	function titleHtml()
	{
		return '<h4>'.$this->_title.'</h4>';
	}
	function dataHtml( $enabled = true )
	{
		return htmlspecialchars($this->prefix.$this->_value.$this->suffix);
	}
	function dataHtmlSmall( $enabled = true )
	{
		return $this->dataHtml( $enabled );
	}
	
	function showInPdf()
	{
		return $this->_showInPdf && ($this->ownBox || !empty($this->_value) );
	}
	function titlePdf()
	{
		return $this->_title;
	}
	function dataPdf()
	{
		return $this->prefix.$this->_value.$this->suffix;
	}
	
	function getX()
	{
		return $this->_left;
	}
	function getY()
	{
		return $this->_top;
	}

	function getLeftPadding()
	{
		return $this->_leftPadding;
	}
	
	function getRightPadding()
	{
		return $this->_rightPadding;
	}

	function getTopPadding()
	{
		return $this->_topPadding;
	}

	function getBottomPadding()
	{
		return $this->_bottomPadding;
	}
	
	function getWidth()
	{
		return $this->_right - $this->_left;
	}
	function getHeight()
	{
		return $this->_bottom - $this->_top;
	}
	function getHtmlWidth()
	{
		return $this->_htmlWidth;
	}
	function hasSuffix()
	{
		return (empty($this->prefix) && empty($this->suffix));
	}
	function getHtmlHeight()
	{
		return $this->_htmlHeight;
	}
	
	function getTitleFontSize()
	{
		return 11;
	}
	function getDataFontSize()
	{
		return 11;
	}
	
	function __sleep()
	{
		if( $this->_hasStatementBank && is_object($this->_statementBank) ) {
			$this->_cycle = $this->_statementBank->getCycle();
			$this->_group = $this->_statementBank->getGroup();
			unset( $this->_statementBank );
		}
		return( array_keys(get_object_vars($this)) );
	}
	
	function __wakeup()
	{
	}
}
?>