<?php
/**
 * @package     Arc
 * @subpackage  Staff
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
require_once( JPATH_COMPONENT.DS.'staffmanager.html.php' );

//getting the connection to the db
$db = &JFactory::getDBO();
//preparing the query
$db->setQuery( 'SELECT ppl.id, COALESCE( ppl.preferred_firstname, ppl.firstname ) AS firstname, COALESCE( ppl.preferred_surname, ppl.surname ) AS surname, s.job_title, s.type'
	.' FROM #__apoth_sm_staff AS s'
	.' INNER JOIN #__apoth_ppl_people AS ppl'
	.'    ON s.person_id = ppl.id' );
//getting the results
$result = $db->loadObjectList();

//de-bug line
//echo'<pre>';var_dump($result);echo'</pre>';
staffmanager_html::show_users_html( $result );
?>