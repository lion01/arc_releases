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

jimport('joomla.application.component.controller');
jimport('joomla.application.helper');

/**
 * Content Component Controller
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ApotheosisControllerFavourites extends ApotheosisController
{
	function display()
	{
		$view = JRequest::getVar('view');
		
		if( method_exists( $this, $view ) ) {
			$this->$view();
		}
		else {
			$this->favouritesList();
		}
	}
	
	function favouritesList()
	{
		$model = &$this->getModel( 'favourites' );
		$view  = &$this->getView ( 'favourites' );
		
		$view->setModel ( $model, true );
		$view->display();
	}
	
	function systemDefault()
	{
		echo '<H3>Sorry</H3>';
		echo '<p>...altering system settings is not yet implemented</p>';
//		$this->favouritesList();
	}
}
?>