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

jimport('joomla.application.component.view');

/**
 * Reports Output View
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Reports
 * @since 0.1
 */
class ReportsViewAdmin extends JView 
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_varMap = array('state'=>'State', 'group'=>'Group', 'groupName'=>'GroupName', 'enabled'=>'Enabled');
	}
	
	/**
	 * Displays an xml page of the data from jhtml groups' child-loading function
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function display( $tpl = NULL )
	{
		$this->_varMap['year']  = 'Year';
		$this->_varMap['start'] = 'CycleStart';
		$this->_varMap['end']   = 'CycleEnd';
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$this->node = JRequest::getVar('node', false);
		$this->nodeLink = $this->link.'&task=loadnode&format=xml';
		
		$db = &JFactory::getDBO();
		$this->whereStr = '(c.year = '.$db->quote($this->year).' OR c.year IS NULL)'
			."\n".' AND '.ApotheosisLibDb::dateCheckSql('c.start_date', 'c.end_date', $this->start, $this->end);		
		
		$this->setLayout( 'ajax' );
		parent::display( 'groups' );
	}

}
?>
