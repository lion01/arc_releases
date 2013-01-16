<?php
/**
 * @package     Arc
 * @subpackage  Pupil
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

echo JText::_( 'COMPONENT' ).'<br />';
echo JText::_( 'PURPOSE' ).'<br />';

global $mainframe;
jimport( 'joomla.application.helper' );
require_once( JPATH_COMPONENT.DS.'pupilmanager.html.php' );

// get pupil data from the db
$db = &JFactory::getDBO();
$db->setQuery( 'SELECT COALESCE( ppl.preferred_surname, ppl.surname ) AS surname, COALESCE( ppl.preferred_firstname, ppl.firstname ) AS firstname, p.upn, ppl.id, p.school_lea_number, p.school_dfes_number'
	."\n".' FROM #__apoth_pup_pupils AS p'
	."\n".' INNER JOIN #__apoth_ppl_people AS ppl'
	."\n".'    ON p.person_id = ppl.id'
	."\n".' ORDER BY surname ASC' );
$result = $db->loadObjectList();

pupilmanager_html::show_users_html( $result );
?>
