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
// **** TODO - make the avatar load the user's actual photo
//		$src = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_eportfolio_file', array( 'people.arc_people'=>$this->_rptData[$this->_config['field']], 'people.files'=>'avatar' ) );
		$src = JURI::base().'components'.DS.'com_arc_people'.DS.'images'.DS.'avatar_default.png';
		$html = '<img class="profile" src="'.$src.'" /> ('.$src.')';
		return parent::renderHTML( $html );
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
}
?>