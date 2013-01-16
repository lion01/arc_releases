<?php
/**
 * @package     Arc
 * @subpackage  Plugin_Log
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport('joomla.event.plugin');

class plgSystemArc_log extends JPlugin
{
	static $_times = array();
	static $_logId;
	static $_debug = 0; // 0 = no debug, 1 = brief debug, 2 = detailed debug
	
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for
	 * plugins because func_get_args ( void ) returns a copy of all passed arguments
	 * NOT references.  This causes problems with cross-referencing necessary for the
	 * observer design pattern.
	 */
	function plgSystemArc_log( &$subject )
	{
		parent::__construct( $subject, false );
		
		// load plugin parameters
		$this->_plugin = JPluginHelper::getPlugin( 'system', 'Arc_log' );
	}
	
	/**
	 * Plugin methods with the same name as the event will be called automatically.
	 */
	
	/**
	 * Includes the code to operate Arc System Log
	 */
	function onAfterInitialise()
	{
		$db = &JFactory::getDBO();
		$user = &JFactory::getUser();
		ob_start();
		var_dump($_GET);
		$gdata = ob_get_clean();
		ob_start();
		var_dump($_POST);
		$pdata = ob_get_clean();
		$ip = $_SERVER['REMOTE_ADDR'];
		$query = 'INSERT INTO `#__apoth_sys_log` ( `j_userid`, `ip_add`, `action_time`, `url`, `get_data`, `post_data`)'
			."\n".'VALUES'
			."\n".'('.$db->Quote($user->id).', '.$db->Quote($ip).', '.$db->Quote(date('Y-m-d H:i:s')).', '.$db->Quote($_SERVER['REQUEST_URI']).', '.$db->Quote($gdata).', '.$db->Quote($pdata).' )';
		$db->setQuery( $query );
		$db->query();
		self::$_logId = $db->insertId();
		self::startTimer( 'whole page' );
	}
	
	function onAfterRender()
	{
		self::stopTimer( 'whole page' );
		if( empty( self::$_times ) ) {
			return true;
		}
		
		$db = &JFactory::getDBO();
		$dbRows = array();
		
		if( self::$_debug ) {
			$str = '<style>.debug td { border: 1px solid black; padding: 2px; }</style>'
				.'<div class="debug" style="position: absolute; top: 0px; left: 0px; z-index: 1000; text-align: left; background: pink; border: 0px solid red; border-width: 2px 0px; padding: 0.5em;">'
				.'<h3>Timings (log id: '.self::$_logId.')</h3>'
				.'<table>';
		}
		else {
			$str = '';
		}
		// aggregate the data and prepare it for display / logging
		foreach( self::$_times as $func=>$times ) {
			$interrupted = !is_null( $times['_cur'] );
			if( array_key_exists( '_cur', $times ) ) { unset( $times['_cur'] ); }
			
			ob_start();var_dump_pre( $times );$str .= ob_get_clean();
			
			if( empty( $times ) ) {
				$count = null;
				$total = null;
				$avg = null;
			}
			else {
				$total = array_sum( $times );
				$count = count( $times );
				$avg = $total / $count;
			}
			
			if( self::$_debug ) {
				$str .= '<tr><td>'.$func.'</td><td>'.( $interrupted ? 'bad' : 'OK' ).'</td><td>'.$total.'</td><td>'.$count.'</td></tr>';
			}
			
			$dbRows[] = '('
				.$db->Quote( self::$_logId ).', '
				.$db->Quote( $func ).', '
				.$db->Quote( (int)$interrupted ).', '
				.$db->Quote( $total ).', '
				.$db->Quote( $count ).', '
				.$db->Quote( $avg )
				.')';
		}
		if( self::$_debug ) {
			$str .= '</table>';
		}
		
		if( !empty( $dbRows ) ) {
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_sys_log_times' ).' VALUES '
				.implode( "\n, ", $dbRows );
			$db->setQuery( $query );
			$db->query();
			if( self::$_debug >= 2 ) {
				ob_start();
				debugQuery( $db );
				$str .= ob_get_clean();
			}
		}
		
		if( self::$_debug >= 2 ) {
			ob_start();
			var_dump_pre( self::$_times, 'raw times' );
			$str .= ob_get_clean();
		}
		if( self::$_debug ) {
			$str .= '</div>';
			echo $str;
		}
	}
	
	function startTimer( $func )
	{
		if( self::$_debug ) { $tm = microtime( true ); }
		if( isset(self::$_times[$func]['_cur']) && !is_null( self::$_times[$func]['_cur'] ) ) {
			self::stopTimer( $func );
		}
		self::$_times[$func]['_cur'] = microtime( true );
		if( self::$_debug ) { self::$_times['_meta'][] = microtime( true ) - $tm; }
	}
	
	function stopTimer( $func )
	{
		if( self::$_debug ) { $tm = microtime( true ); }
		if( !is_null( self::$_times[$func]['_cur'] ) ) {
			self::$_times[$func][] = microtime( true ) - self::$_times[$func]['_cur'];
			self::$_times[$func]['_cur'] = null;
		}
		if( self::$_debug ) { self::$_times['_meta'][] = microtime( true ) - $tm; }
	}
	
}
?>