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

// Give us access to the joomla model class
jimport( 'joomla.application.component.model' );

/**
 * Core Admin Settings Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Core
 * @since      1.6
 */
class CoreAdminModelSettings extends ArcAdminModel
{
	/**
	 * Constructor sets the component so we can retrieve params using ArcAdminModel
	 */
	function __construct( $config = array() )
	{
		$config['component'] = 'com_arc_core';
		parent::__construct( $config );
	}
}
?>