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
class ReportsViewOutput extends JView 
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_varMap = array('state'=>'State');
	}
	
	/**
	 * Displays a generic page
	 * (for when there are no actions or selected registers)
	 *
	 * @param string $template  Optional name of the template to use
	 */
	function display( $tpl = NULL )
	{
		ob_start();
		ApotheosisLib::setViewVars($this, $this->_varMap);
		
		$this->setLayout( 'ajax' );
		$lister = &$this->getModel( 'lists' );
		
		$lName = JRequest::getVar( 'fieldname' );
		$lVals = unserialize(JRequest::getVar( $lName ));
//		echo 'lName: ';var_dump_pre($lName);
//		echo 'lVals: ';var_dump_pre($lVals);
		$this->dataSet = array();
		switch($lName) {
		case('ocourse'):
			$lister->setGroup($lVals);
			$tmp = $lister->getChildren();
			foreach( $tmp as $k=>$v ) {
				$tmp = new stdClass();
				$tmp->value = $v->id;
				$tmp->text = $v->fullname;
				$this->dataSet[] = $tmp;
			}
			break;
		
		case('ogroup'):
			$lister->setGroup($lVals);
			$tmp = $lister->getStudents();
			foreach( $tmp as $k=>$v ) {
				$tmp = new stdClass();
				$tmp->value = $v->pupilid;
				$tmp->text = $v->displayname;
				$this->dataSet[] = $tmp;
			}
			break;
		
		case('otutor'):
			$lister->setGroup($lVals);
			$tmp = $lister->getStudents();
			foreach( $tmp as $k=>$v ) {
				$tmp = new stdClass();
				$tmp->value = $v->pupilid;
				$tmp->text = $v->displayname;
				$this->dataSet[] = $tmp;
			}
			break;
		
		case('omember'):
			$lister->setGroup($lVals);
			$tmp = $lister->getStudentCourses();
			foreach( $tmp as $k=>$v ) {
				if($v->type == 'non' && $v->id != 0) {
					$tmp = new stdClass();
					$tmp->value = $v->id;
					$tmp->text = $v->fullname;
					$this->dataSet[] = $tmp;
				}
			}
			break;
		}
//		$tmp = ob_get_clean();
//		echo $tmp;
		ob_end_clean();
		
		parent::display( 'output_selections' );
	}

}
?>
