<?php
/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
 /*
 * Extension Manager Summary Model
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class ApotheosisModelLuhn extends JModel
{
	var $_lastLuhn;
	var $_source;
	
	function generateLuhn( $inVal, $stripChars = false )
	{
		$this->_lastLuhn = ApotheosisLib::generateLuhn( $inVal, $stripChars );
		$this->_source = $inVal;
	}
	
	function checkLuhn( $inVal, $stripChars = false )
	{
		$this->_source = ApotheosisLib::checkLuhn( $inVal, $stripChars );
	}
	
	function getLuhn()
	{
		return $this->_lastLuhn;
	}
	
	function getSource()
	{
		return $this->_source;
	}
}
?>