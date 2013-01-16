<?php
/**
 * @package     Arc
 * @subpackage  Module_Context
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class modArcContextHelper
{
	public function getHelp( $action )
	{
		$names = ApotheosisLib::getActions();
		$name = $names[$action]['name'];
		$helpUrl = 'https://arcwiki.pun.net/index.php/Arc_guide:'.$name;
		return $helpUrl;
	}
	
	
	public function getLinks( $action )
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT *'
			."\n".'FROM '.$db->nameQuote( '#__apoth_sys_action_context' )
			."\n".'WHERE '.$db->nameQuote( 'from_id' ).' = '.$db->Quote( $action )
			."\n".'   OR '.$db->nameQuote( 'from_id' ).' IS NULL'
			."\n".'ORDER BY '.$db->nameQuote( 'order' );
		$db->setQuery( $query );
		$data = $db->loadAssocList();
		
		if( !is_array( $data ) ) {
			$data = array();
		}
		$links = array();
		
		$user = &ApotheosisLib::getUser();
		$uId = $user->id;
		
		foreach( $data as $d ) {
			if( !empty( $d['to_id'] ) ){
			
				$actionId = $d['to_id'];
				$allowed = ApotheosisLibAcl::getUserPermitted( $uId, $actionId );
				if( $allowed && ApotheosisLibAcl::getUserRestricted($uId, $actionId) ) {
					$allowed = ApotheosisLibAcl::checkDependancies( $uId, $actionId );
				}
			
				if( $allowed ) {
					$link = ApotheosisLib::getActionLink( $actionId );
				}
				else {
					continue;
				}
			}
			else {
				$link = $d['url'];
			}
			
			$links[$d['category']][] = array( 
				  'url'=>$link
				, 'text'=>$d['text']
				, 'target'=>( empty( $d['target'] ) ? null : $d['target'] )
			);
		}
		
		return $links;
	}
}
?>
