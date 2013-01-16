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

jimport('joomla.application.component.view');

/**
 * Extension Manager Install View
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class ApotheosisViewLuhn extends JView
{
	function display($tpl = NULL)
	{
		$document = &JFactory::getDocument();
		$document->setTitle(JText::_( 'Apotheosis Luhn Number Generator' ));
		
		echo 'Luhn checknumbers are used to ensure validity of data<br />';
		
		parent::display($tpl);
	}

	function generate($tpl = NULL)
	{
		$lc = $this->get('Luhn');
		$inVal = $this->get('Source');
		echo '<p>Required check digit is: "'.$lc.'", so total character sequence is:<br />'.$inVal.'-'.$lc.'</p>';
		parent::display($tpl);
	}

	function check($tpl = NULL)
	{
		echo 'Checked the checknumber<br />';
		$inVal = $this->get('Source');
		if ($inVal) {
			echo '<span style="color: green;">Valid</span>';
		}
		else {
			echo '<span style="color: red; font-weight: bold">Not Valid</span>';
		}
		parent::display($tpl);
	}
}
?>
