<?php
/**
 * @package     Arc
 * @subpackage  People
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
class ApothReportMergeWords_People extends ApothReportMergeWords
{
	function name( $d, $o )
	{
		$p = ApotheosisData::_( 'people.person', $d );
		return htmlspecialchars( $p->firstname );
	}
}


// #####  Field subclasses  #####

/**
 * Report Photo Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_People_Photo extends ApothReportField
{
	function renderHTML( $value )
	{
		$fileInfo = $this->_getPhotoFile();
		$html = '<img class="profile" src="'.$fileInfo['src'].'" />';
		return parent::renderHTML( $html, false, false );
	}
	
	function renderPDF( $pdf, $value )
	{
		$l = $this->_core['print_l'] + $this->_boundBox['l'];
		$t = $this->_core['print_t'] + $this->_boundBox['t'];
		$w = $this->_core['print_width'];
		$h = $this->_core['print_height'];
		$r = $this->_boundBox['r'] + ( $this->_boundBox['w'] - ( $this->_core['print_l'] + $this->_core['print_width'] ) );
		
		$l += $this->_core['print_pad_l'];
		$t += $this->_core['print_pad_t'];
		$r += $this->_core['print_pad_r'];
		
		$fileInfo = $this->_getPhotoFile();
		$pdf->image( $fileInfo['src'], $l, $t, $w, $h, $fileInfo['type'], '', '', true );
		
		if( $this->_core['print_border'] ) {
			$pdf->rect( $l, $t, $w, $h);
		}
	}
	
	function _getPhotoFile()
	{
		if( isset( $this->_config['person_id'] ) ) {
			$pId = $this->_config['person_id'];
		}
		else {
			$pId = $this->_rptData[$this->_config['field']];
		}
		
		if( empty( $pId ) ) {
			$src = false;
		}
		else {
			$src = ApotheosisData::_( 'people.photo', $pId );
			$type = 'jpeg';
		}
		
		if( $src == false ) {
			$src = JURI::base().'components'.DS.'com_arc_people'.DS.'images'.DS.'avatar_default.png';
			$type = 'png';
		}
		
		return array( 'src'=>$src, 'type'=>$type );;
	}
}

/**
 * Report Name Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_People_Name extends ApothReportField
{
	function renderHTML( $value )
	{
		if( is_null( $value ) ) {
			$html = htmlspecialchars( ApotheosisData::_( 'people.displayName', $this->_rptData[$this->_config['field']], $this->_config['format'] ) );
		}
		else {
			$html = '<i>name</i>';
		}
		return parent::renderHTML( $html );
	}
	
	function renderPDF( $pdf, $value )
	{
		if( is_null( $value ) ) {
			$txt = ApotheosisData::_( 'people.displayName', $this->_rptData[$this->_config['field']], $this->_config['format'] );
		}
		else {
			$txt = '[[name]]';
		}
		parent::renderPDF( $pdf, $txt );
	}
}
?>