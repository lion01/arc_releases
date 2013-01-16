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
 * Merge words handler
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportMergeWords_Report extends ApothReportMergeWords
{
	function substitute( $d, $o )
	{
		return ( is_null( $o ) ? $d : reset( $o ) );
	}
	
	function options( $d, $o )
	{
		foreach( $o as $optId=>$opt ) {
			$obj = new stdClass();
			$obj->val = $opt;
			$obj->text = $opt;
			
			$optList[] = $obj;
		}
		
		return JHTML::_( 'select.genericlist', $optList, 'opt_list', '', 'val', 'text' );
	}
	
	function gender( $d, $o )
	{
		$gender = ApotheosisData::_( 'people.gender', $d );
		
		switch( strtolower( $gender ) ) {
		case( 'm' ):
		default:
			$retVal = $o[0];
			break;
		
		case( 'f' ):
			$retVal = $o[1];
			break;
		}
		
		return $retVal;
	}
	
}


// #####  Field subclasses  #####

/**
 * Report Text Undefined
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Report__Undefined extends ApothReportField
{
	function renderHTML( $value )
	{
		$style = isset( $this->_config['style'] ) ? $this->_config['style'].' ' : '';
		$txt = '<span style="'.$style.'background: red;">Undefined field';
		ob_start();
		var_dump_pre( $this );
		$txt .= ob_get_clean().'</span>';
		return parent::renderHTML( $txt );
	}
}

/**
 * Report Text Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Report_Text extends ApothReportField
{
	function renderHTML( $value )
	{
		if( isset( $this->_config['style'] ) ) {
			$txt = '<span style="'.$this->_config['style'].'">'.$this->_config['text'].'</span>';
		}
		else {
			$txt = $this->_config['text'];
		}
		return parent::renderHTML( $txt );
	}
}

/**
 * Report More Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Report_More extends ApothReportField
{
	function renderHTML( $value )
	{
		$html = '<a href="#" class="btn toggler" '
			.'otherText="'.( isset( $this->_config['text_alt'] ) ? $this->_config['text_alt'] : $this->_config['text'] ).'">'
			.$this->_config['text']
			.'</a>';
		return parent::renderHTML( $html );
	}
	
	function getScripts()
	{
		return array(
			JURI::base().'components'.DS.'com_arc_report'.DS.'helpers'.DS.'report_more.js'
		);
	}
}

/**
 * Report Statement Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Report_Statements extends ApothReportField
{
	function renderHTML( $value )
	{
		$html = '<textarea name="f_'.$this->_id.'" class="report_statement_text" style="top 0px; left: 0px; width: '.($this->_core['web_width'] - 45).'px; height: '.($this->_core['web_height'] - 10).'px"'.(empty( $value ) ? ' title="'.$this->_core['web_default'].'"' : '').'>'.$value.'</textarea>';
		if( $link = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_ajax_statement', array( 'report.subreport'=>$this->_rptData['id'], 'report.field'=>$this->getId() ) ) ) {
			$html .= '<a href="'.$link.'" class="report_statement_bank modal btn" target="blank" rel="{handler:\'iframe\', size:{x:640,y:480}}"><span class="inline_icon"></span><span class="bank_text">bank</span></a>';
		}
		return parent::renderHTML( $html, true );
	}
	
	function getScripts()
	{
		return array(
			JURI::base().'components'.DS.'com_arc_report'.DS.'helpers'.DS.'report_statements.js',
		);
	}
	
	function getStatements( $report, $doMerge = false )
	{
		$fField = ApothFactory::_( 'report.field' );
		$statements = $fField->loadStatements( $this->_context['cycleId'], $this->_context['layoutId'], $this->_context['sectionId'], $this->_id, $this->_context['groupId'] );
		
		if( $doMerge ) {
			$mergeWords = $fField->loadMergeWords();
			$replaceWords = array();
			 
			foreach( $statements as $sId=>$s ) {
				$mergables = array();
				preg_match_all( '~\\[\\[.+?\\]\\]~', $statements[$sId]['text'], $mergables );
				foreach( $mergables[0] as $mergeable ) {
					$replace = null;
					$word = substr( $mergeable, 2, -2 );
					
					if( isset( $mergeWords[$word] ) && !array_key_exists( $mergeable, $replaceWords ) ) {
						// resolve aliases
						if( ( substr( $mergeWords[$word]['handler'], 0, 1 )  == '~' )
						 && ( substr( $mergeWords[$word]['handler'], -1, 1 ) == '~' ) ) {
							$word = substr( $mergeWords[$word]['handler'], 1, -1 );
						}
						
						// find the handler and other vital data for the merge word
						$h = $mergeWords[$word]['handler'];
						$d = ( is_null( $mergeWords[$word]['datum'] ) ? null : $report->getDatum( $mergeWords[$word]['datum'] ) );
						$o = ( isset( $mergeWords[$word]['options'] ) ? $mergeWords[$word]['options'] : null );
						
						// get the replace value
						$replaceWords[$mergeable] = self::_mergeWord( $h, $d, $o );
					}
				}
			}
			
			// don't replace merge fields with nulls // **** not sure if maybe we should?
			foreach( $replaceWords as $w=>$r ) {
				if( is_null( $r ) ) {
					unset( $replaceWords[$w] );
				}
			}
			
			// substitute in replace values
			$search = array_keys( $replaceWords );
			foreach( $statements as $sId=>$s ) {
				$statements[$sId]['text'] = str_replace( $search, $replaceWords, $statements[$sId]['text'] );
			}
		}
		
		return $statements;
	}
}

/**
 * Report Prior-Statement Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Report_priorStatements extends ApothReportField_Report_Statements
{
	function getStatements( $report, $doMerge = false )
	{
		$db = &JFactory::getDBO();
		
		$allCycles = ( is_array( $this->_config['cycle'] ) ? $this->_config['cycle'] : array( $this->_config['cycle'] ) );
		$oldCycles = array();
		$cycles = array();
		$texts = array();
		$statements = array();
		
		// work out which cycles to look in for prior statements
		foreach( $allCycles as $c ) {
			if( substr( $c, 0, 4 ) == 'old_' ) {
				$oldCycles[] = $db->Quote( (int)substr( $c, 4 ) );
			}
			else {
				$cycles[] = $db->Quote( $c );
			}
		}
		
		// get any text from the old report system (only ever used at Wildern until 2012)
		if( !empty( $oldCycles ) ) {
			$query = 'SELECT '.$db->nameQuote( $this->_config['field'] )
				."\n".'FROM '.$db->nameQuote( '#__apoth_rpt__old_reports' ).' AS r'
				."\n".'WHERE '.$db->nameQuote( 'cycle' ).' IN ( '.implode( ', ', $oldCycles ).' )'
				."\n".'  AND '.$db->nameQuote( 'student' ).' = '.$db->Quote( $this->_rptData[$this->_core['lookup_source']] )
				;
			$db->setQuery( $query );
			$texts = array_merge( $texts, $db->loadResultArray() );
		}
		// get any text from prior report cycles
		// *** not yet implimented
		if( !empty( $cycles ) ) {
			$texts = array_merge( $texts, array( 'No prior statements found' ) );
		}
		
		foreach( $texts as $k=>$v ) {
			$lines = preg_split( '~(\\r|\\n|\\r\\n)~', $v );
			foreach( $lines as $line ) {
				$line = trim( $line );
				if( !empty( $line ) ) {
					$statements[] = array( 'color'=>( ($odd = !$odd) ? 'lightgrey' : '' ), 'text'=>$line );
				} 
			}
		}
		
		return $statements;
	}
	
}

/**
 * Report Textarea Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Report_Textarea extends ApothReportField
{
	function renderHTML( $value )
	{
		$html = '<textarea name="f_'.$this->_id.'" class="report_textarea" style="top 0px; left: 0px; width: '.$this->_core['web_width'].'px; height: '.$this->_core['web_height'].'px"'.(empty( $value ) ? ' title="'.$this->_core['web_default'].'"' : '').'>'.$value.'</textarea>';
		return parent::renderHTML( $html );
	}
	
	function getScripts()
	{
		return array(
			JURI::base().'components'.DS.'com_arc_report'.DS.'helpers'.DS.'report_textarea.js',
		);
	}
}

/**
 * Report Options Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Report_Radiolist extends ApothReportField
{
	function renderHTML( $value )
	{
		if( !isset( $this->_config['options'] ) || empty( $this->_config['options'] ) ) {
			$html = '<div class="radio">';
		}
		else {
			switch( isset( $this->_config['layout'] ) ? $this->_config['layout'] : 'vertical' ) {
			case( 'horizontal' ):
				$html = '<div class="radio_horizontal">';
				$w = floor( $this->_core['web_width'] / count( $this->_config['options'] ) );
				$rStyle = 'width: '.$w.'px; float: left; text-align: center';
				break;
			
			case( 'vertical' ):
			default:
				$html = '<div class="radio_vertical">';
				$w = $this->_core['web_width'];
				$rStyle = 'width: '.$w.'px;';
				break;
			}
			
			foreach( $this->_config['options'] as $k=>$v ) {
				$html .= '<div style="'.$rStyle.'">';
				$name = 'f_'.$this->_core['id'];
				$id = $name.'_'.$k;
				if( $this->_config['controls'] !== false ) {
					$html .= "\n\t".'<input type="radio" name="'.$name.'" id="'.$id.'" value="'.$k.'" '.( $k == $value ? 'checked="checked" ' : '' ).'/>';
				}
				if( $this->_config['labels'] !== false ) {
					$html .= "\n\t".'<label for="'.$id.'">'.$v.'</label>';
				}
				$html .= '</div>';
			}
			$html .= '</div>';
		}
		return parent::renderHTML( $html );
	}
}

/**
 * Report Value Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Report_Value extends ApothReportField
{
	function renderHTML( $value )
	{
		$html = 'A lookup of past data';
		return parent::renderHTML( $html );
	}
}
?>