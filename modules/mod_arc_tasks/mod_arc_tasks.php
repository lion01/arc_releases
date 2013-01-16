<?php
/**
 * @package     Arc
 * @subpackage  Module_Tasks
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Include the tasks helper file
require_once( JPATH_SITE.DS.'modules'.DS.'mod_arc_tasks'.DS.'helper.php' );

// Statically define "new" list options.
// *** Should really be loaded from the DB in some way, but there's no time right now
// *** until there are multiple components it doesn't matter anyway
$tasks = array();

$data = array( 'new'=>true );
$eref = ApotheosisLibAcl::getUserLinkAllowed( 'apoth_msg_hub_inter', array( 'message.scopes'=>'form', 'message.forms'=>'behaviour.start.edit', 'message.data'=>json_encode($data) ) );
if( $eref != false ) ( 
	  $tasks['newRef'] = array('text'=>'eRef', 'url'=>$eref, 'target'=>'popup')
);
//	/* eg of another type */ $tasks['google'] = array('text'=>'Google', 'url'=>'http;//google.com', 'target'=>'blank')
if( !empty( $tasks ) ) {
	require( JModuleHelper::getLayoutPath('mod_arc_tasks') );
}

?>