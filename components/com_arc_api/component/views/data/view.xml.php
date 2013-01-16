<?php
/**
 * @package     Arc
 * @subpackage  API
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * API Data View
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage API
 * @since      1.6.1
 */
class ApiViewData extends JView
{
	function display()
	{
		$this->data = $this->get( 'Data' );
		
		$xml = new SimpleXMLElement('<root/>');
		$this->array_to_xml( $xml, $this->data );
		
		echo $xml->asXML();
	}
	
	function array_to_xml($xml, $data) {
		foreach($data as $key => $value) {
			if(is_array($value)) {
				if(!is_numeric($key)){
					$subnode = $xml->addChild( $key );
					$this->array_to_xml( $subnode, $value );
				}
				else{
					$this->array_to_xml( $xml, $value );
				}
			}
			else {
				$xml->addChild( $key, $value );
			}
		}
	}
}

?>
