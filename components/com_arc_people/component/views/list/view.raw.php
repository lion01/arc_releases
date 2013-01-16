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

jimport('joomla.application.component.view');

/**
 * People Manager list View
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	People
 * @since 0.1
 */
class PeopleViewList extends JView 
{
	/**
	 * Default method to offer a downloadable csv of the current people
	 */
	function display()
	{
		$this->model = &$this->getModel();
		$this->people = &$this->get( 'People' );
		$this->rels = $this->get( 'RelSearch' );
		
		JResponse::setHeader('Content-Type', 'application/octet-stream');
		JResponse::setHeader('Content-Disposition', 'attachment; filename="people.csv"');
		
		$this->setLayout( 'raw' );
		parent::display( 'csv_list' );
	}
}
?>